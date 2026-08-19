<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CostPerKgController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year  = $request->input('year', date('Y'));
        $type  = $request->input('type', 'gabungan');

        // Panggil fungsi engine agar tidak ada duplikasi kode
        $data           = $this->getDashboardData($month, $year, $type);
        $data['ttdDay'] = ($month == date('n') && $year == date('Y')) ? (int) date('j') : $data['daysInMonth'];

        return view('cost-per-kg.index', $data);
    }

    public function export(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year  = $request->input('year', date('Y'));
        $type  = $request->input('type', 'gabungan');

        $data           = $this->getDashboardData($month, $year, $type);
        $data['ttdDay'] = ($month == date('n') && $year == date('Y')) ? (int) date('j') : $data['daysInMonth'];

        $fileName = "Cost_Per_Kg_{$year}_{$month}_{$type}.xls";

        return response(view('cost-per-kg.export', $data))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    // ====================================================================
    // FUNGSI UTAMA: MENYIAPKAN SELURUH DATA COST & PRODUKSI
    // ====================================================================
    private function getDashboardData($month, $year, $type)
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        // 1. TARIK DATA AKTUAL PRODUKSI
        $dailyLatex = array_fill(1, $daysInMonth, 0);
        $dailyLump  = array_fill(1, $daysInMonth, 0);
        $dailyRss   = array_fill(1, $daysInMonth, 0);

        $latexLumpData = DB::connection('mysql_factory')->table('table_latex as latex')
            ->leftJoin('table_lump as lump', 'latex.rcp_code', '=', 'lump.rcp_code')
            ->whereMonth('latex.date', $month)->whereYear('latex.date', $year)
            ->select(DB::raw('DAY(latex.date) as day'), DB::raw('SUM(latex.kering_kg) as total_latex'), DB::raw('SUM(lump.cup_lump) as total_lump'))
            ->groupBy(DB::raw('DAY(latex.date)'))->get();

        foreach ($latexLumpData as $row) {
            $dailyLatex[$row->day] = (float) $row->total_latex;
            $dailyLump[$row->day]  = (float) $row->total_lump;
        }

        $rssData = DB::connection('mysql_factory')->table('table_grading as grading')
            ->leftJoin('table_grd_details as detailss', 'grading.grd_code', '=', 'detailss.grd_code')
            ->whereMonth('grading.tgl_produksi', $month)->whereYear('grading.tgl_produksi', $year)
            ->select(DB::raw('DAY(grading.tgl_produksi) as day'), DB::raw('SUM(detailss.total_grd) as total_rss'))
            ->groupBy(DB::raw('DAY(grading.tgl_produksi)'))->get();

        foreach ($rssData as $row) {
            $dailyRss[$row->day] = (float) $row->total_rss;
        }

        // 2. AMBIL MASTER DATA KARYAWAN & RATE COST
        $statuses = [];
        if ($type == 'regular') {$statuses = ['Regular'];} elseif ($type == 'contract_fl') {$statuses = ['Contract FL'];} else { $statuses = ['Regular', 'Contract FL'];}

        $employeesQuery = DB::table('users')
            ->where('active', 'yes')
            ->where('dept', 'Factory')
            ->whereNotIn('jabatan', ['Adm A', 'Adm B', 'Asst Mng', 'HM', 'Mdr']);

        if (! empty($statuses)) {
            $employeesQuery->whereIn('status', $statuses);
        }
        $employeesMap = $employeesQuery->pluck('status', 'nik')->toArray();

        $costs  = DB::table('master_costs')->where('year', $year)->pluck('cost_per_day', 'status')->toArray();
        $dwRate = $costs['DW'] ?? 0;

        $umrTotal      = DB::table('master_umr')->where('year', $year)->value('total') ?? 0;
        $otRatePerHour = $umrTotal > 0 ? ($umrTotal / 173) : 0;

        // Ambil Data Hari Libur
        $holidaysRaw = DB::table('holidays')->whereMonth('date', $month)->whereYear('date', $year)->pluck('date')->toArray();

        // 3. HITUNG ACTUAL COST
        $rawActuals = DB::table('user_sub_jobs')
            ->join('sub_jobs', 'user_sub_jobs.sub_job', '=', 'sub_jobs.id')
            ->whereMonth('user_sub_jobs.work_date', $month)
            ->whereYear('user_sub_jobs.work_date', $year)
            ->get(['nik', 'work_date', 'hk', 'ot', 'ot_rev', 'qty', 'user_sub_jobs.desc', 'sub_jobs.area', 'sub_jobs.payment_system']);

        $dayMap            = [];
        $activeAreasActual = [];

        foreach ($rawActuals as $r) {
            $nik = $r->nik;
            if (! isset($employeesMap[$nik])) {continue;}

            $date = date('Y-m-d', strtotime($r->work_date));
            if (! isset($dayMap[$nik][$date])) {
                $dayMap[$nik][$date] = ['has_borongan' => false, 'borongan_qty' => 0, 'borongan_area' => '', 'hk' => 0, 'ot' => 0, 'ot_rev' => null, 'reg_area' => ''];
            }

            if ($r->payment_system == 'borongan' && (float) $r->qty > 0) {
                $dayMap[$nik][$date]['has_borongan']   = true;
                $dayMap[$nik][$date]['borongan_qty']  += (float) $r->qty;
                $dayMap[$nik][$date]['borongan_area']  = $r->area;
                $activeAreasActual[$r->area]           = true;
            } else {
                // SINKRONISASI 1: KODE KHUSUS JADI HK 1
                $specialCodes = ['CT', 'CB', 'CH', 'CLL', 'CL', 'S'];
                $cleanDesc    = strtoupper(trim($r->desc));

                if (in_array($cleanDesc, $specialCodes)) {
                    $hkVal = 1;
                } else {
                    $hkVal = is_numeric($r->hk) ? (float) $r->hk : 0;
                }

                $dayMap[$nik][$date]['hk'] = max($dayMap[$nik][$date]['hk'], $hkVal);
                $dayMap[$nik][$date]['ot'] = max($dayMap[$nik][$date]['ot'], (float) $r->ot);
                if (! is_null($r->ot_rev) && trim($r->ot_rev) !== '') {
                    $dayMap[$nik][$date]['ot_rev'] = max((float) $dayMap[$nik][$date]['ot_rev'], (float) $r->ot_rev);
                }
                $dayMap[$nik][$date]['reg_area'] = $r->area;
                $activeAreasActual[$r->area]     = true;
            }
        }

        $costsPerAreaDay = [];
        foreach ($dayMap as $nik => $dates) {
            $status  = $employeesMap[$nik];
            $empRate = $costs[$status] ?? 0;

            foreach ($dates as $date => $data) {
                $day = (int) date('j', strtotime($date));

                if ($data['has_borongan']) {
                    $cost = $data['borongan_qty'] * $dwRate;
                    $area = $data['borongan_area'];
                } else {
                    $finalOt = ($data['ot_rev'] !== '' && $data['ot_rev'] !== null) ? (float) $data['ot_rev'] : (float) $data['ot'];

                    // SINKRONISASI 2: LOGIKA MULTIPLIER OT (HARI KERJA NORMAL, LIBUR & MINGGU)
                    $dateObj   = Carbon::parse($date);
                    $isSunday  = $dateObj->isSunday();
                    $isFriday  = $dateObj->isFriday();
                    $isHoliday = $isSunday || in_array($date, $holidaysRaw);

                    $payableOt = 0;

                    if ($finalOt > 0) {
                        if ($isHoliday) {
                            // --- JIKA HARI LIBUR / MINGGU ---
                            if ($isFriday) {
                                // Libur Nasional di Hari Jumat (Jam Normal 5 Jam)
                                if ($finalOt <= 5) {
                                    $payableOt = $finalOt * 2;
                                } elseif ($finalOt <= 6) {
                                    $payableOt = (5 * 2) + (($finalOt - 5) * 3);
                                } else {
                                    $payableOt = (5 * 2) + (1 * 3) + (($finalOt - 6) * 4);
                                }
                            } else {
                                // Hari Minggu atau Libur Nasional Selain Jumat (Jam Normal 7 Jam)
                                if ($finalOt <= 7) {
                                    $payableOt = $finalOt * 2;
                                } elseif ($finalOt <= 8) {
                                    $payableOt = (7 * 2) + (($finalOt - 7) * 3);
                                } else {
                                    $payableOt = (7 * 2) + (1 * 3) + (($finalOt - 8) * 4);
                                }
                            }
                        } else {
                            // --- JIKA HARI KERJA NORMAL (Senin - Jumat Non-Libur) ---
                            if ($finalOt <= 1) {
                                $payableOt = $finalOt * 1.5;
                            } else {
                                $payableOt = (1 * 1.5) + (($finalOt - 1) * 2);
                            }
                        }
                    }

                    // COST ACTUAL DENGAN JAM LEMBUR YANG SUDAH DIKALI (payableOt)
                    $cost = ($data['hk'] * $empRate) + ($payableOt * $otRatePerHour);
                    $area = $data['reg_area'];
                }

                if (! isset($costsPerAreaDay[$area][$day])) {
                    $costsPerAreaDay[$area][$day] = 0;
                }
                $costsPerAreaDay[$area][$day] += $cost;
            }
        }

        // 4. QUERY BUDGET COST (PLAN)
        $planQuery = DB::table('user_sub_job_budgets')
            ->join('sub_jobs', 'user_sub_job_budgets.sub_job', '=', 'sub_jobs.id')
            ->leftJoin('master_costs', function ($join) use ($year) {
                $join->on(DB::raw('user_sub_job_budgets.status COLLATE utf8mb4_unicode_ci'), '=', DB::raw('master_costs.status COLLATE utf8mb4_unicode_ci'))->where('master_costs.year', '=', $year);
            })
            ->select(
                'sub_jobs.area', DB::raw('DAY(user_sub_job_budgets.work_date) as day'),
                DB::raw('SUM(CASE WHEN sub_jobs.payment_system = "borongan" THEN (1394 * ' . $dwRate . ') ELSE (COALESCE(user_sub_job_budgets.qty, 0) * COALESCE(master_costs.cost_per_day, 0)) + (COALESCE(user_sub_job_budgets.ot, 0) * ' . $otRatePerHour . ') END) as plan_cost')
            )->whereMonth('user_sub_job_budgets.work_date', $month)->whereYear('user_sub_job_budgets.work_date', $year);

        if (! empty($statuses)) {$planQuery->whereIn('user_sub_job_budgets.status', $statuses);}
        $rawPlans = $planQuery->groupBy('sub_jobs.area', DB::raw('DAY(user_sub_job_budgets.work_date)'))->get();

        // 5. SUSUN STRUKTUR AREA
        $activeAreas = collect(array_keys($activeAreasActual))->merge(collect($rawPlans)->pluck('area'))->unique()->toArray();
        usort($activeAreas, function ($a, $b) {
            $getWeight = function ($areaName) {
                if (stripos($areaName, 'Process') !== false) {return 1;}
                if (stripos($areaName, 'Milling') !== false) {return 2;}
                if (stripos($areaName, 'Unloading') !== false || stripos($areaName, 'SH') !== false) {return 3;}
                if (stripos($areaName, 'Grading') !== false) {return 4;}
                return 5;
            };
            $weightA = $getWeight($a); $weightB = $getWeight($b);
            if ($weightA == $weightB) {return strcmp($a, $b);}
            return $weightA <=> $weightB;
        });

        $areas = [];
        foreach ($activeAreas as $area) {
            if (stripos($area, 'Process') !== false) {$areas[$area] = '(Latex + Lump)';} elseif (stripos($area, 'Milling') !== false) {$areas[$area] = '(Latex)';} else { $areas[$area] = '(RSS)';}
        }
        if (empty($areas)) {$areas = ['Process' => '(Latex + Lump)', 'Milling' => '(Latex)', 'SH + Unloading' => '(RSS)'];}

        // 6. TARIK BUDGET PRODUKSI
        $budgetMaster = DB::connection('mysql_factory')->table('budget_master')
            ->whereMonth('month_year', $month)->whereYear('month_year', $year)->get();

        $bProdLatex = 0;
        $bProdLump  = 0;
        $bProdRss   = 0;
        foreach ($budgetMaster as $bm) {
            if ($bm->kind == 'Receiving Latex') {$bProdLatex += $bm->budget;} elseif ($bm->kind == 'Receiving Lump') {$bProdLump += $bm->budget;} elseif (in_array($bm->kind, ['Factory RSS#1', 'Factory RSS#4', 'Factory Local', 'Factory BC'])) {$bProdRss += $bm->budget;}
        }

        $budgetProd     = [];
        $budgetTotalRss = [];

        foreach (array_keys($areas) as $area) {
            $monthlyBudget = 0;
            if (stripos($area, 'Process') !== false) {$monthlyBudget = $bProdLatex + $bProdLump;} elseif (stripos($area, 'Milling') !== false) {$monthlyBudget = $bProdLatex;} else { $monthlyBudget = $bProdRss;}

            $dailyBudget = $daysInMonth > 0 ? $monthlyBudget / $daysInMonth : 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $budgetProd[$area][$d] = $dailyBudget;
                $budgetTotalRss[$d]    = $daysInMonth > 0 ? $bProdRss / $daysInMonth : 0;
            }
        }

        // 7. MAPPING DATA
        $budgetCostsPerAreaDay = [];
        $actualProd            = [];
        $actualTotalRss        = [];

        foreach (array_keys($areas) as $area) {
            for ($d = 1; $d <= $daysInMonth; $d++) {
                if (! isset($costsPerAreaDay[$area][$d])) {$costsPerAreaDay[$area][$d] = 0;}
                $budgetCostsPerAreaDay[$area][$d] = 0;

                if (stripos($area, 'Process') !== false) {$actualProd[$area][$d] = $dailyLatex[$d] + $dailyLump[$d];} elseif (stripos($area, 'Milling') !== false) {$actualProd[$area][$d] = $dailyLatex[$d];} else { $actualProd[$area][$d] = $dailyRss[$d];}
                $actualTotalRss[$d] = $dailyRss[$d];
            }
        }
        foreach ($rawPlans as $row) {$budgetCostsPerAreaDay[$row->area][(int) $row->day] = (float) $row->plan_cost;}

        // 8. BUDGET CPK HARIAN
        $rawBudgets    = DB::table('budget_cost_per_kgs')->whereMonth('work_date', $month)->whereYear('work_date', $year)->get();
        $budgetCpkData = [];
        foreach ($rawBudgets as $b) {
            $budgetCpkData[$b->area][(int) Carbon::parse($b->work_date)->format('j')] = (float) $b->budget_cpk;
        }

        return compact(
            'month', 'year', 'daysInMonth', 'areas', 'costsPerAreaDay', 'budgetCostsPerAreaDay',
            'actualProd', 'actualTotalRss', 'budgetProd', 'budgetTotalRss', 'budgetCpkData', 'type'
        );
    }
}
