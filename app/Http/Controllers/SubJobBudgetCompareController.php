<?php
namespace App\Http\Controllers;

use App\Exports\SubJobBudgetCompareExport as BudgetExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SubJobBudgetCompareController extends Controller
{
    public function index(Request $request)
    {
        $month       = $request->input('month', date('n'));
        $year        = $request->input('year', date('Y'));
        $type        = $request->input('type', 'gabungan'); // default ke gabungan
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

        // ==========================================================
        // LOGIKA TTD (Today To-Date)
        // Jika bulan ini, batasnya = hari ini. Jika bulan lalu = full sebulan.
        // ==========================================================
        if ($month == date('n') && $year == date('Y')) {
            $ttdDay = (int) date('j');
        } else {
            $ttdDay = $daysInMonth;
        }

        // ==========================================================
        // 1. Ambil Structure Sub Jobs
        // ==========================================================
        $subJobsRaw = DB::table('sub_jobs')
            ->select('id', 'area', 'name', 'color')
        // ->orderBy('area') // Hapus bagian ini, karena kita akan menggunakan Custom Sort
            ->orderBy('id')
            ->get();

        // Ambil Rate untuk DW (Borongan) di tahun tersebut
        $dwRate = DB::table('master_costs')
            ->where('status', 'DW')
            ->where('year', $year)
            ->value('cost_per_day');

        // Susun ke dalam Array
        $structure = [];
        foreach ($subJobsRaw as $job) {
            $structure[$job->area][] = (array) $job;
        }

        // ==========================================================
        // CUSTOM SORTING AREA (Sesuai Alur Pabrik)
        // ==========================================================
        uksort($structure, function ($a, $b) {
            // Berikan "Bobot Antrean" berdasarkan kata kunci Area
            $getWeight = function ($areaName) {
                if (stripos($areaName, 'Process') !== false || stripos($areaName, 'Proses') !== false) {
                    return 1;
                }

                if (stripos($areaName, 'Milling') !== false) {
                    return 2;
                }

                if (stripos($areaName, 'SH') !== false || stripos($areaName, 'Unloading') !== false) {
                    return 3;
                }

                if (stripos($areaName, 'Grading') !== false) {
                    return 4;
                }

                return 5; // Lain-lain / area sisanya ditaruh paling bawah
            };

            $weightA = $getWeight($a);
            $weightB = $getWeight($b);

            // Jika bobotnya sama (misal ada dua area dengan nama yang mirip), urutkan abjad
            if ($weightA == $weightB) {
                return strcmp($a, $b);
            }
            // Urutkan dari bobot terkecil (1) ke terbesar (5)
            return $weightA <=> $weightB;
        });

        // ==========================================================
        // 2. LOGIKA FILTER (PASTI HANYA 1 TABEL YANG DI-RENDER)
        // ==========================================================
        $allConfigs = [
            'gabungan'    => [
                'title'    => 'Cost Factory Regular + Contract FL (unit Rp 000)',
                'statuses' => ['Regular', 'Contract FL'],
            ],
            'regular'     => [
                'title'    => 'Cost Factory Regular (unit Rp 000)',
                'statuses' => ['Regular'],
            ],
            'contract_fl' => [
                'title'    => 'Cost Factory Contract FL (unit Rp 000)',
                'statuses' => ['Contract FL'],
            ],
        ];

        // Langsung pilih 1 konfigurasi berdasarkan input $type.
        // Jika URL dikutak-katik user, kembalikan ke 'gabungan'
        $reportsConfig = [$allConfigs[$type] ?? $allConfigs['gabungan']];

        // 3. Loop Data (Sekarang ini HANYA akan berputar 1 kali saja!)
        $reports = [];
        foreach ($reportsConfig as $config) {
            $data = $this->getReportData($month, $year, $config['statuses'], $dwRate);

            $reports[] = [
                'title'  => $config['title'],
                'plan'   => $data['plan'],
                'actual' => $data['actual'],
            ];
        }

        // Pastikan $ttdDay dikirim ke compact
        return view('sub-job-budget.compare', compact(
            'structure',
            'reports',
            'daysInMonth',
            'month',
            'year',
            'dwRate',
            'ttdDay',
            'type'
        ));
    }

    // --- FUNGSI REUSABLE UNTUK QUERY DATA ---
    private function getReportData($month, $year, $statuses = [], $dwRate)
    {
        $planQuery = DB::table('user_sub_job_budgets')
            ->join('sub_jobs', 'user_sub_job_budgets.sub_job', '=', 'sub_jobs.id')
            ->leftJoin('master_costs', function ($join) use ($year) {
                $join->on(
                    DB::raw('user_sub_job_budgets.status COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('master_costs.status COLLATE utf8mb4_unicode_ci')
                )->where('master_costs.year', '=', $year);
            })
        // --- TAMBAHAN JOIN HOLIDAYS UNTUK PLAN ---
            ->leftJoin('holidays', function ($join) {
                $join->on('user_sub_job_budgets.work_date', '=', 'holidays.date');
            })
            ->select(
                'sub_jobs.name as sub_job_name',
                'sub_jobs.payment_system',
                'user_sub_job_budgets.sub_job',
                'user_sub_job_budgets.work_date',

                // --- LOGIKA PLAN QTY (HK + OT Konversi) ---
                DB::raw('SUM(
                    CASE
                        WHEN sub_jobs.payment_system = "borongan" THEN 1394
                        ELSE COALESCE(user_sub_job_budgets.qty, 0) + (
                            COALESCE(user_sub_job_budgets.ot, 0) /
                            CASE
                                WHEN holidays.date IS NOT NULL THEN 7 -- Prioritas 1: Jika Libur
                                WHEN DAYOFWEEK(user_sub_job_budgets.work_date) = 6 THEN 5 -- Prioritas 2: Jika Jumat
                                ELSE 7 -- Prioritas 3: Selain Jumat & Libur
                            END
                        )
                    END
                ) as plan_qty'),

                // --- LOGIKA PLAN COST (Rupiah Budget) ---
                DB::raw('SUM(
                    CASE
                        WHEN sub_jobs.payment_system = "borongan" THEN (1394 * ' . $dwRate . ')
                        ELSE (
                            COALESCE(user_sub_job_budgets.qty, 0) + (
                                COALESCE(user_sub_job_budgets.ot, 0) /
                                CASE
                                    WHEN holidays.date IS NOT NULL THEN 7
                                    WHEN DAYOFWEEK(user_sub_job_budgets.work_date) = 6 THEN 5
                                    ELSE 7
                                END
                            )
                        ) * COALESCE(master_costs.cost_per_day, 0)
                    END
                ) as plan_cost')
            )
            ->whereMonth('user_sub_job_budgets.work_date', $month)
            ->whereYear('user_sub_job_budgets.work_date', $year);

        if (! empty($statuses)) {
            $planQuery->whereIn('user_sub_job_budgets.status', $statuses);
        }

        $plans = $planQuery->groupBy(
            'user_sub_job_budgets.sub_job',
            'user_sub_job_budgets.work_date',
            'sub_jobs.name',
            'sub_jobs.payment_system'
        )->get();

        $planMap = [];
        foreach ($plans as $p) {
            // Gunakan alias plan_qty dan plan_cost untuk data borongan
            $planMap[$p->sub_job][$p->work_date] = [
                'qty' => (float) $p->plan_qty,
                'rp'  => (float) $p->plan_cost,
            ];
        }

        // ==============================================================
        // B. QUERY ACTUAL (user_sub_jobs JOIN users)
        // ==============================================================
        $actualQuery = DB::table('user_sub_jobs')
            ->join('sub_jobs', 'user_sub_jobs.sub_job', '=', 'sub_jobs.id')
            ->join('users', function ($join) {
                $join->on(
                    DB::raw('users.nik COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('user_sub_jobs.nik COLLATE utf8mb4_unicode_ci')
                );
            })
            ->leftJoin('master_costs', function ($join) use ($year) {
                $join->on(
                    DB::raw('users.status COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('master_costs.status COLLATE utf8mb4_unicode_ci')
                )->where('master_costs.year', '=', $year);
            })
            ->leftJoin('holidays', function ($join) {
                $join->on('user_sub_jobs.work_date', '=', 'holidays.date');
            })
            ->select(
                'user_sub_jobs.sub_job',
                'user_sub_jobs.work_date',
                'sub_jobs.name as sub_job_name',
                'sub_jobs.payment_system',

                // --- LOGIKA ACTUAL QTY ---
                DB::raw('SUM(
                    CASE
                        WHEN sub_jobs.payment_system = "borongan" THEN COALESCE(user_sub_jobs.qty, 0)
                        ELSE COALESCE(user_sub_jobs.hk, 0) + (
                            COALESCE(user_sub_jobs.ot_rev, user_sub_jobs.ot, 0) /
                            CASE
                                WHEN holidays.date IS NOT NULL THEN 7
                                WHEN DAYOFWEEK(user_sub_jobs.work_date) = 6 THEN 5
                                ELSE 7
                            END
                        )
                    END
                ) as actual_qty'),

                // --- LOGIKA ACTUAL COST ---
                DB::raw('SUM(
                    CASE
                        WHEN sub_jobs.payment_system = "borongan" THEN (COALESCE(user_sub_jobs.qty, 0) * ' . $dwRate . ')
                        ELSE (
                            COALESCE(user_sub_jobs.hk, 0) + (
                                COALESCE(user_sub_jobs.ot_rev, user_sub_jobs.ot, 0) /
                                CASE
                                    WHEN holidays.date IS NOT NULL THEN 7
                                    WHEN DAYOFWEEK(user_sub_jobs.work_date) = 6 THEN 5
                                    ELSE 7
                                END
                            )
                        ) * COALESCE(master_costs.cost_per_day, 0)
                    END
                ) as actual_cost')
            )
            ->whereMonth('user_sub_jobs.work_date', $month)
            ->whereYear('user_sub_jobs.work_date', $year);

        if (! empty($statuses)) {
            $actualQuery->whereIn('users.status', $statuses);
        }

        // FIX 2: Tambahkan kolom dari tabel sub_jobs ke groupBy
        $actuals = $actualQuery->groupBy(
            'user_sub_jobs.sub_job',
            'user_sub_jobs.work_date',
            'sub_jobs.name',
            'sub_jobs.payment_system'
        )->get();

        $actualMap = [];
        foreach ($actuals as $a) {
            $actualMap[$a->sub_job][$a->work_date] = [
                'qty' => (float) $a->actual_qty,
                'rp'  => (float) $a->actual_cost,
            ];
        }

        return ['plan' => $planMap, 'actual' => $actualMap];
    }

    public function export(Request $request)
    {
        $month       = $request->input('month', date('n'));
        $year        = $request->input('year', date('Y'));
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

        // 1. SIAPKAN DATA (Copy logic dari method index)
        // Agar tidak duplikasi kode, idealnya logic ini dipisah ke private function,
        // tapi untuk sekarang kita copy saja bagian penyiapan datanya.

        // Ambil Structure
        $subJobsRaw = DB::table('sub_jobs')
            ->select('id', 'area', 'name', 'color')
            ->orderBy('area')->orderBy('id')->get();
        $structure = [];
        foreach ($subJobsRaw as $job) {
            $structure[$job->area][] = (array) $job;
        }

        $reportsConfig = [
            // TABEL 1: Regular & Contract FL
            [
                'title'    => 'Regular + Contract FL',
                'statuses' => ['Regular', 'Contract FL'],
            ],
            // TABEL 2: Daily Worker (DW) - Sesuaikan nama status di DB Anda
            [
                'title'    => 'Regular',
                'statuses' => ['Regular'],
            ],
            // TABEL 3: Magang / Intern - Sesuaikan nama status di DB Anda
            [
                'title'    => 'Contract FL',
                'statuses' => ['Contract FL'],
            ],
        ];

        $reports = [];
        foreach ($reportsConfig as $config) {
            $data      = $this->getReportData($month, $year, $config['statuses']);
            $reports[] = [
                'title'  => $config['title'],
                'plan'   => $data['plan'],
                'actual' => $data['actual'],
            ];
        }

        // 2. BUNDLE DATA
        $dataExport = [
            'structure' => $structure,
            'reports'   => $reports,
        ];

        // 3. DOWNLOAD EXCEL
        $fileName = 'Budget_vs_Actual_' . date('F_Y', mktime(0, 0, 0, $month, 1, $year)) . '.xlsx';

        return Excel::download(
            new BudgetExport($dataExport, $month, $year, $daysInMonth),
            $fileName
        );
    }
}
