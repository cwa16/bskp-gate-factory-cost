<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year  = $request->input('year', date('Y'));

        // 1. Tarik Rate Cost
        $costs = DB::table('master_costs')->where('year', $year)->pluck('cost_per_day', 'status')->toArray();

        // 2. Tarik Hari Libur & Pastikan Format Y-m-d
        $holidaysRaw = DB::table('holidays')->whereMonth('date', $month)->whereYear('date', $year)->pluck('date')->toArray();
        $holidays    = array_map(function ($d) {return date('Y-m-d', strtotime($d));}, $holidaysRaw);

        // ====================================================================
        // 3. MAPPING USER FACTORY (Menghindari Kebocoran Gaji Non-Operator)
        // ====================================================================
        $usersMap = DB::table('users')
            ->where('active', 'yes')
            ->where('dept', 'Factory')
            ->whereNotIn('jabatan', ['Adm A', 'Adm B', 'Asst Mng', 'HM', 'Mdr'])
            ->pluck('status', 'nik')
            ->toArray();

        // --- PROSES DATA DETAIL ---
        $dataRegular    = $this->getDataByStatus($month, $year, 'Regular', $costs, $holidays, $usersMap);
        $dataContractFL = $this->getDataByStatus($month, $year, 'Contract FL', $costs, $holidays, $usersMap);

        $mtdResult = $this->getMtdData($month, $year, $costs, $holidays, $usersMap);
        $mtdData   = $mtdResult['data'];
        $toDate    = $mtdResult['toDate'];

        $dailyTrend = $this->getDailyCostTrend($month, $year, $costs, $holidays, $usersMap);

        // *** TAMBAHAN BARU: DAILY COST TREND PER AREA ***
        $dailyTrendPerArea = $this->getDailyTrendPerArea($month, $year, $costs, $holidays, $usersMap);

        // ====================================================================
        // 4. MENGHITUNG KPI PRODUKSI (Kg/HK & Rp/Kg) DARI BUDGET RSS#1
        // ====================================================================
        $targetProduksi = DB::table('budget_cost_per_kgs')
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->selectRaw('SUM(budget_cpk) as budget_cpk')
            ->value('budget_cpk') ?? 0;

        $totalHk   = $dataRegular['actual']->total_hk + $dataContractFL['actual']->total_hk;
        $totalCost = $dataRegular['actual']->total_cost + $dataContractFL['actual']->total_cost;

        $totalPlanCost  = $dataRegular['plan']->total_cost + $dataContractFL['plan']->total_cost;
        $persentaseCost = $totalPlanCost > 0 ? ($totalCost / $totalPlanCost) * 100 : 0;

        $kpiProduksi = [
            'produksi_kg' => (float) $targetProduksi,
            'total_hk'    => (float) $totalHk,
            'kg_per_hk'   => $totalHk > 0 ? ($targetProduksi / $totalHk) : 0,
            'rp_per_kg'   => $targetProduksi > 0 ? ($totalCost / $targetProduksi) : 0,
            'persentase'  => $persentaseCost,
        ];

        // ====================================================================
        // 5. PERSIAPAN DATA GRAFIK COST PER KG (UNTUK DASHBOARD)
        // ====================================================================
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        // A. Tarik Produksi RSS Harian dari database Pabrik
        $rssData = DB::connection('mysql_factory')->table('table_grading as grading')
            ->leftJoin('table_grd_details as details', 'grading.grd_code', '=', 'details.grd_code')
            ->whereMonth('grading.tgl_produksi', $month)
            ->whereYear('grading.tgl_produksi', $year)
            ->select(DB::raw('DAY(grading.tgl_produksi) as day'), DB::raw('SUM(details.total_grd) as total_rss'))
            ->groupBy(DB::raw('DAY(grading.tgl_produksi)'))
            ->get();

        $dailyRss = array_fill(1, $daysInMonth, 0);
        foreach ($rssData as $row) {
            $dailyRss[$row->day] = (float) $row->total_rss;
        }

        // B. Tarik Target Budget CPK Harian
        $budgetCpks = DB::table('budget_cost_per_kgs')
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->select('work_date', DB::raw('SUM(budget_cpk) as budget_cpk'))
            ->groupBy('work_date')
            ->get();

        $dailyTargetCpk = array_fill(1, $daysInMonth, 0);
        foreach ($budgetCpks as $b) {
            $day                   = (int) date('j', strtotime($b->work_date));
            $dailyTargetCpk[$day] += $b->budget_cpk;
        }

        // C. Susun Array untuk Chart.js
        $chartCpk = ['labels' => [], 'actual' => [], 'target' => []];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $chartCpk['labels'][] = str_pad($d, 2, '0', STR_PAD_LEFT);
            $index                = $d - 1;

            // Total Rupiah Pabrik Hari Itu (Regular + Contract FL)
            $dailyActCost = ($dailyTrend['Regular']['actual'][$index] ?? 0) + ($dailyTrend['Contract FL']['actual'][$index] ?? 0);
            $rss          = $dailyRss[$d] ?? 0;

            // ACTUAL CPK
            $chartCpk['actual'][] = $rss > 0 ? round($dailyActCost / $rss, 2) : 0;
            // TARGET CPK
            $chartCpk['target'][] = round($dailyTargetCpk[$d], 2);
        }

        return view('dashboard.index', compact(
            'month', 'year',
            'dataRegular', 'dataContractFL',
            'mtdData', 'toDate', 'dailyTrend', 'kpiProduksi', 'chartCpk',
            'dailyTrendPerArea' // *** Variabel Baru dikirim ke View ***
        ));
    }

    // =========================================================================
    // FUNGSI BARU: TREND BUDGET VS ACTUAL DIBAGI PER AREA & STATUS
    // =========================================================================
    private function getDailyTrendPerArea($month, $year, $costs, $holidays, $usersMap)
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        // 1. TARIK DATA ACTUAL (Bebas Duplikat Manual)
        $qAct = DB::table('user_sub_jobs')
            ->join('sub_jobs', 'user_sub_jobs.sub_job', '=', 'sub_jobs.id')
            ->whereIn('user_sub_jobs.nik', array_keys($usersMap))
            ->whereMonth('user_sub_jobs.work_date', $month)
            ->whereYear('user_sub_jobs.work_date', $year)
            ->get(['user_sub_jobs.nik', 'user_sub_jobs.work_date', 'user_sub_jobs.hk', 'user_sub_jobs.ot', 'user_sub_jobs.ot_rev', 'sub_jobs.area']);

        $dedup = [];
        foreach ($qAct as $r) {
            $dateOnly = date('Y-m-d', strtotime($r->work_date));
            $key      = $r->nik . '_' . $dateOnly . '_' . $r->area;
            $ot       = (is_null($r->ot_rev) || trim($r->ot_rev) === '') ? $r->ot : $r->ot_rev;
            $hkVal    = is_numeric($r->hk) ? (float) $r->hk : 0;

            if (! isset($dedup[$key])) {
                $dedup[$key] = ['nik' => $r->nik, 'work_date' => $dateOnly, 'area' => $r->area, 'hk' => $hkVal, 'ot' => (float) $ot];
            } else {
                $dedup[$key]['hk'] = max($dedup[$key]['hk'], $hkVal);
                $dedup[$key]['ot'] = max($dedup[$key]['ot'], (float) $ot);
            }
        }

        // Pisahkan Array Actual berdasarkan Status
        $actualData  = ['Regular' => [], 'Contract FL' => []];
        $activeAreas = [];
        foreach ($dedup as $row) {
            $status = $usersMap[$row['nik']] ?? null;
            if (! in_array($status, ['Regular', 'Contract FL'])) {
                continue;
            }

            $day                = (int) Carbon::parse($row['work_date'])->format('j');
            $area               = $row['area'];
            $activeAreas[$area] = true;

            $divisor = in_array($row['work_date'], $holidays) ? 7 : (Carbon::parse($row['work_date'])->isFriday() ? 5 : 7);
            $hkEq    = $row['hk'] + ($row['ot'] / $divisor);
            $rate    = $costs[$status] ?? 0;

            if (! isset($actualData[$status][$area])) {
                $actualData[$status][$area] = array_fill(1, $daysInMonth, 0);
            }

            $actualData[$status][$area][$day] += ($hkEq * $rate);
        }

        // 2. TARIK DATA BUDGET/PLAN
        $qPlan = DB::table('user_sub_job_budgets')
            ->join('sub_jobs', 'user_sub_job_budgets.sub_job', '=', 'sub_jobs.id')
            ->whereIn('user_sub_job_budgets.status', ['Regular', 'Contract FL'])
            ->whereMonth('user_sub_job_budgets.work_date', $month)
            ->whereYear('user_sub_job_budgets.work_date', $year)
            ->get(['user_sub_job_budgets.status', 'user_sub_job_budgets.work_date', 'user_sub_job_budgets.qty', 'user_sub_job_budgets.ot', 'sub_jobs.area']);

        // Pisahkan Array Budget berdasarkan Status
        $budgetData = ['Regular' => [], 'Contract FL' => []];
        foreach ($qPlan as $row) {
            if (! in_array($row->status, ['Regular', 'Contract FL'])) {
                continue;
            }

            $dateOnly           = date('Y-m-d', strtotime($row->work_date));
            $day                = (int) Carbon::parse($dateOnly)->format('j');
            $area               = $row->area;
            $activeAreas[$area] = true;

            $divisor = in_array($dateOnly, $holidays) ? 7 : (Carbon::parse($dateOnly)->isFriday() ? 5 : 7);
            $hkEq    = $row->qty + ($row->ot / $divisor);
            $rate    = $costs[$row->status] ?? 0;

            if (! isset($budgetData[$row->status][$area])) {
                $budgetData[$row->status][$area] = array_fill(1, $daysInMonth, 0);
            }

            $budgetData[$row->status][$area][$day] += ($hkEq * $rate);
        }

        // 3. SUSUN DATA UNTUK CHART MULTIPLE AREA
        $charts = [];
        $labels = range(1, $daysInMonth);

        $sortedAreas = array_keys($activeAreas);
        usort($sortedAreas, function ($a, $b) {
            $getWeight = function ($areaName) {
                if (stripos($areaName, 'Process') !== false) {
                    return 1;
                }

                if (stripos($areaName, 'Milling') !== false) {
                    return 2;
                }

                if (stripos($areaName, 'Unloading') !== false || stripos($areaName, 'SH') !== false) {
                    return 3;
                }

                if (stripos($areaName, 'Grading') !== false) {
                    return 4;
                }

                return 5;
            };
            $weightA = $getWeight($a); $weightB = $getWeight($b);
            if ($weightA == $weightB) {
                return strcmp($a, $b);
            }

            return $weightA <=> $weightB;
        });

        foreach ($sortedAreas as $area) {
            $charts[$area] = [
                'labels'          => $labels,
                'regular_actual'  => array_values($actualData['Regular'][$area] ?? array_fill(1, $daysInMonth, 0)),
                'regular_budget'  => array_values($budgetData['Regular'][$area] ?? array_fill(1, $daysInMonth, 0)),
                'contract_actual' => array_values($actualData['Contract FL'][$area] ?? array_fill(1, $daysInMonth, 0)),
                'contract_budget' => array_values($budgetData['Contract FL'][$area] ?? array_fill(1, $daysInMonth, 0)),
            ];
        }

        return $charts;
    }

    // =========================================================================
    // FUNGSI PEMBERSIH DUPLIKAT (SHIELD ANTI-CARTESIAN & MULTI SUB-JOB)
    // =========================================================================
    private function getDedupActuals($month, $year, $toDate = null, $validNiks = null)
    {
        $q = DB::table('user_sub_jobs')
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year);

        if ($toDate) {
            $q->where('work_date', '<=', $toDate);
        }
        if ($validNiks) {
            $q->whereIn('nik', $validNiks);
        }

        $raw = $q->get(['nik', 'work_date', 'hk', 'ot', 'ot_rev']);

        $dedup = [];
        foreach ($raw as $r) {
            $dateOnly = date('Y-m-d', strtotime($r->work_date));
            $key      = $r->nik . '_' . $dateOnly;
            $ot       = (is_null($r->ot_rev) || trim($r->ot_rev) === '') ? $r->ot : $r->ot_rev;
            $hkVal    = is_numeric($r->hk) ? (float) $r->hk : 0;

            if (! isset($dedup[$key])) {
                $dedup[$key] = [
                    'nik'       => $r->nik,
                    'work_date' => $dateOnly,
                    'hk'        => $hkVal,
                    'ot'        => (float) $ot,
                ];
            } else {
                $dedup[$key]['hk'] = max($dedup[$key]['hk'], $hkVal);
                $dedup[$key]['ot'] = max($dedup[$key]['ot'], (float) $ot);
            }
        }
        return array_values($dedup);
    }

    // =========================================================================
    // FUNGSI MTD DATA
    // =========================================================================
    private function getMtdData($month, $year, $costs, $holidays, $usersMap)
    {
        if ($month == date('n') && $year == date('Y')) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = Carbon::create($year, $month)->endOfMonth()->format('Y-m-d');
        }

        $todateData = [
            'Regular'     => ['plan' => 0, 'actual' => 0],
            'Contract FL' => ['plan' => 0, 'actual' => 0],
            'Total'       => ['plan' => 0, 'actual' => 0],
        ];

        $actuals = $this->getDedupActuals($month, $year, $toDate, array_keys($usersMap));

        foreach ($actuals as $row) {
            $status = $usersMap[$row['nik']] ?? null;
            if (! in_array($status, ['Regular', 'Contract FL'])) {
                continue;
            }

            $isHoliday = in_array($row['work_date'], $holidays);
            $isFriday  = Carbon::parse($row['work_date'])->isFriday();
            $divisor   = $isHoliday ? 7 : ($isFriday ? 5 : 7);

            $hkEquivalent = $row['hk'] + ($row['ot'] / $divisor);
            $rate         = $costs[$status] ?? 0;
            $cost         = $hkEquivalent * $rate;

            $todateData[$status]['actual'] += $cost;
            $todateData['Total']['actual'] += $cost;
        }

        $plans = DB::table('user_sub_job_budgets')
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->whereIn('status', ['Regular', 'Contract FL'])
            ->get(['status', 'work_date', 'qty', 'ot']);

        foreach ($plans as $row) {
            $dateOnly  = date('Y-m-d', strtotime($row->work_date));
            $isHoliday = in_array($dateOnly, $holidays);
            $isFriday  = Carbon::parse($dateOnly)->isFriday();
            $divisor   = $isHoliday ? 7 : ($isFriday ? 5 : 7);

            $hkEquivalent = $row->qty + ($row->ot / $divisor);
            $rate         = $costs[$row->status] ?? 0;
            $cost         = $hkEquivalent * $rate;

            $todateData[$row->status]['plan'] += $cost;
            $todateData['Total']['plan']      += $cost;
        }

        return ['data' => $todateData, 'toDate' => $toDate];
    }

    // =========================================================================
    // FUNGSI DATA DETAIL TABS
    // =========================================================================
    private function getDataByStatus($month, $year, $statusLabel, $costs, $holidays, $usersMap)
    {
        $rate = $costs[$statusLabel] ?? 0;

        $validNiks = array_keys(array_filter($usersMap, function ($val) use ($statusLabel) {
            return $val === $statusLabel;
        }));

        if (empty($validNiks)) {
            $validNiks = ['EMPTY'];
        }

        $actuals = $this->getDedupActuals($month, $year, null, $validNiks);

        $act_hk           = 0;
        $act_ot_converted = 0;
        $act_cost         = 0;
        foreach ($actuals as $row) {
            $isHoliday = in_array($row['work_date'], $holidays);
            $divisor   = $isHoliday ? 7 : (Carbon::parse($row['work_date'])->isFriday() ? 5 : 7);

            $otEq              = $row['ot'] / $divisor;
            $act_hk           += $row['hk'];
            $act_ot_converted += $otEq;
            $act_cost         += ($row['hk'] + $otEq) * $rate;
        }

        $actualData = (object) [
            'total_hk'   => $act_hk,
            'total_ot'   => $act_ot_converted,
            'total_cost' => $act_cost,
        ];

        $plans = DB::table('user_sub_job_budgets')
            ->where('status', $statusLabel)
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->get(['work_date', 'qty', 'ot']);

        $plan_hk   = 0;
        $plan_cost = 0;
        foreach ($plans as $row) {
            $dateOnly  = date('Y-m-d', strtotime($row->work_date));
            $isHoliday = in_array($dateOnly, $holidays);
            $divisor   = $isHoliday ? 7 : (Carbon::parse($dateOnly)->isFriday() ? 5 : 7);

            $hkEq       = $row->qty + ($row->ot / $divisor);
            $plan_hk   += $hkEq;
            $plan_cost += $hkEq * $rate;
        }

        $planData  = (object) [
            'total_hk'   => $plan_hk,
            'total_cost' => $plan_cost,
        ];

        $budgetByArea = DB::table('user_sub_job_budgets')
            ->join('sub_jobs', 'user_sub_job_budgets.sub_job', '=', 'sub_jobs.id')
            ->where('user_sub_job_budgets.status', $statusLabel)
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->groupBy('sub_jobs.area')
            ->select('sub_jobs.area', DB::raw('SUM(qty) as val'))
            ->pluck('val', 'sub_jobs.area')->toArray();

        $actualByArea = DB::table('user_sub_jobs')
            ->join('sub_jobs', 'user_sub_jobs.sub_job', '=', 'sub_jobs.id')
            ->whereIn('user_sub_jobs.nik', $validNiks)
            ->whereMonth('user_sub_jobs.work_date', $month)
            ->whereYear('user_sub_jobs.work_date', $year)
            ->groupBy('sub_jobs.area')
            ->select('sub_jobs.area', DB::raw('SUM(user_sub_jobs.hk) as val'))
            ->pluck('val', 'sub_jobs.area')->toArray();

        $allAreas = array_unique(array_merge(array_keys($budgetByArea), array_keys($actualByArea)));
        sort($allAreas);

        $chartArea = ['labels' => [], 'plan' => [], 'act' => []];
        foreach ($allAreas as $area) {
            $chartArea['labels'][] = $area;
            $chartArea['plan'][]   = $budgetByArea[$area] ?? 0;
            $chartArea['act'][]    = $actualByArea[$area] ?? 0;
        }

        $rawDaily = DB::table('user_sub_jobs')
            ->whereIn('nik', $validNiks)
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->where('sub_job', '>', 0)
            ->select(DB::raw('DAY(work_date) as day'), 'sub_job', DB::raw('SUM(COALESCE(ot_rev, ot, 0)) as total_ot'))
            ->groupBy(DB::raw('DAY(work_date)'), 'sub_job')
            ->get();

        $activeSubJobs = collect($rawDaily)->groupBy('sub_job')
            ->map(function ($items) {return $items->sum('total_ot');})
            ->filter(function ($sum) {return $sum > 0;})
            ->sortDesc()->keys()->toArray();

        $subJobDetails = DB::table('sub_jobs')->whereIn('id', $activeSubJobs)->pluck('name', 'id')->toArray();
        $subJobColors  = DB::table('sub_jobs')->whereIn('id', $activeSubJobs)->pluck('color', 'id')->toArray();

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $chartDaily  = ['labels' => range(1, $daysInMonth), 'datasets' => []];

        foreach ($activeSubJobs as $jobId) {
            $dataPerDay = array_fill(0, $daysInMonth, 0);
            foreach ($rawDaily as $row) {
                if ($row->sub_job == $jobId) {
                    $dataPerDay[$row->day - 1] = (float) $row->total_ot;
                }
            }
            $chartDaily['datasets'][] = [
                'label'           => $subJobDetails[$jobId] ?? 'Job ' . $jobId,
                'data'            => $dataPerDay,
                'backgroundColor' => $subJobColors[$jobId] ?? '#ced4da',
                'stack'           => 'Stack 0',
            ];
        }

        $empStats = [];
        foreach ($actuals as $r) {
            if (! isset($empStats[$r['nik']])) {
                $empStats[$r['nik']] = ['hk' => 0, 'ot' => 0];
            }
            $empStats[$r['nik']]['hk'] += $r['hk'];
            $empStats[$r['nik']]['ot'] += $r['ot'];
        }

        $usersInfo = DB::table('users')->whereIn('nik', $validNiks)->get(['nik', 'name', 'jabatan']);
        $topList   = [];
        foreach ($usersInfo as $u) {
            $stats = $empStats[$u->nik] ?? ['hk' => 0, 'ot' => 0];
            if ($stats['ot'] > 0 || $stats['hk'] > 0) {
                $topList[] = (object) [
                    'nik'      => $u->nik,
                    'name'     => $u->name,
                    'jabatan'  => $u->jabatan,
                    'total_hk' => $stats['hk'],
                    'total_ot' => $stats['ot'],
                ];
            }
        }
        usort($topList, function ($a, $b) {return $b->total_ot <=> $a->total_ot;});
        $topEmployees = array_slice($topList, 0, 5);

        return [
            'actual'       => $actualData,
            'plan'         => $planData,
            'chartArea'    => $chartArea,
            'chartDaily'   => $chartDaily,
            'topEmployees' => $topEmployees,
        ];
    }

    // =========================================================================
    // FUNGSI TREND COST HARIAN (GRAFIK GARIS KIRI KANAN)
    // =========================================================================
    private function getDailyCostTrend($month, $year, $costs, $holidays, $usersMap)
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        $trendData = [
            'Regular'     => ['plan' => array_fill(1, $daysInMonth, 0), 'actual' => array_fill(1, $daysInMonth, 0)],
            'Contract FL' => ['plan' => array_fill(1, $daysInMonth, 0), 'actual' => array_fill(1, $daysInMonth, 0)],
        ];

        $plans = DB::table('user_sub_job_budgets')
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->whereIn('status', ['Regular', 'Contract FL'])
            ->get(['status', 'work_date', 'qty', 'ot']);

        foreach ($plans as $row) {
            $dateOnly = date('Y-m-d', strtotime($row->work_date));
            $day      = (int) Carbon::parse($dateOnly)->format('j');
            $divisor  = in_array($dateOnly, $holidays) ? 7 : (Carbon::parse($dateOnly)->isFriday() ? 5 : 7);

            $hkEq = $row->qty + ($row->ot / $divisor);
            $rate = $costs[$row->status] ?? 0;

            $trendData[$row->status]['plan'][$day] += ($hkEq * $rate);
        }

        $actuals  = $this->getDedupActuals($month, $year, null, array_keys($usersMap));

        foreach ($actuals as $row) {
            $status = $usersMap[$row['nik']] ?? null;
            if (! in_array($status, ['Regular', 'Contract FL'])) {
                continue;
            }

            $day     = (int) Carbon::parse($row['work_date'])->format('j');
            $divisor = in_array($row['work_date'], $holidays) ? 7 : (Carbon::parse($row['work_date'])->isFriday() ? 5 : 7);

            $hkEq = $row['hk'] + ($row['ot'] / $divisor);
            $rate = $costs[$status] ?? 0;

            $trendData[$status]['actual'][$day] += ($hkEq * $rate);
        }

        return [
            'labels'      => range(1, $daysInMonth),
            'Regular'     => [
                'plan'   => array_values($trendData['Regular']['plan']),
                'actual' => array_values($trendData['Regular']['actual']),
            ],
            'Contract FL' => [
                'plan'   => array_values($trendData['Contract FL']['plan']),
                'actual' => array_values($trendData['Contract FL']['actual']),
            ],
        ];
    }
}
