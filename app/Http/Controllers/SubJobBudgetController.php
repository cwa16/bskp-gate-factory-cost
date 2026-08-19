<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubJobBudgetController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year  = $request->input('year', date('Y'));

        // 1. Tangkap filter tipe (default ke gabungan)
        $type        = $request->input('type', 'gabungan');
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

        // Structure Sub Jobs (Master)
        $subJobsRaw = DB::table('sub_jobs')
            ->select('id', 'area', 'name', 'color')
            ->orderBy('id', 'asc')
            ->get();

        $structure = [];
        foreach ($subJobsRaw as $job) {
            $structure[$job->area][] = (array) $job;
        }

        // CUSTOM SORTING AREA (Sesuai Alur Produksi Pabrik)
        uksort($structure, function ($a, $b) {
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

                return 5;
            };

            $weightA = $getWeight($a);
            $weightB = $getWeight($b);
            if ($weightA == $weightB) {
                return strcmp($a, $b);
            }

            return $weightA <=> $weightB;
        });

        // ==========================================================
        // 2. LOGIKA FILTER (PASTIKAN HANYA 1 TABEL YANG DIBUAT)
        // ==========================================================
        $configs = [
            'gabungan'    => ['title' => 'Regular + Contract FL', 'statuses' => ['Regular', 'Contract FL']],
            'regular'     => ['title' => 'Regular', 'statuses' => ['Regular']],
            'contract_fl' => ['title' => 'Contract FL', 'statuses' => ['Contract FL']],
        ];

        $selectedConfig = $configs[$type] ?? $configs['gabungan'];
        $tableTitle     = $selectedConfig['title'];
        $statuses       = $selectedConfig['statuses'];

        // 3. Ambil Data Budget (TIDAK PERLU Group By Status lagi!)
        $budgets = DB::table('user_sub_job_budgets')
            ->select('sub_job', 'work_date', DB::raw('SUM(qty) as total_qty'))
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->whereIn('status', $statuses)
            ->groupBy('sub_job', 'work_date') // <-- Status dihapus agar langsung digabung
            ->get();

        // 4. Siapkan Penampung (Hanya ada 1 Array Data)
        $reports = [
            $tableTitle => [
                'map'    => [],
                'totals' => array_fill(1, $daysInMonth, 0),
            ],
        ];

        // 5. Masukkan ke dalam Mapping
        foreach ($budgets as $b) {
            $val     = (float) $b->total_qty;
            $dateKey = date('j', strtotime($b->work_date));

            $reports[$tableTitle]['map'][$b->sub_job][$b->work_date]  = $val;
            $reports[$tableTitle]['totals'][$dateKey]                += $val;
        }

        return view('sub-job-budget.index', compact(
            'structure',
            'reports',
            'daysInMonth',
            'month',
            'year',
            'type'
        ));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nik'        => 'required',
            'work_date'  => 'required|date',
            'sub_job_id' => 'required|exists:sub_jobs,id',
            'qty'        => 'required|numeric|min:0',
            'status'     => 'required', // Regular / Contract FL / dll
        ]);

        DB::beginTransaction(); // Pakai transaksi agar data aman (konsisten)

        try {
            // A. SIMPAN KE TABEL BUDGET (PLAN)
            // Ini data rencana qty (misal: target 100)
            DB::table('user_sub_job_budgets')->updateOrInsert(
                [
                    'nik'       => $request->nik,
                    'work_date' => $request->work_date,
                    'sub_job'   => $request->sub_job_id,
                ],
                [
                    'qty'        => $request->qty,
                    'status'     => $request->status,
                    'updated_at' => now(),
                    // Jika baru, set created_at
                    'created_at' => DB::raw('IFNULL(created_at, NOW())'),
                ]
            );

            // B. OTOMATIS SIMPAN KE TABEL USER_SUB_JOBS (ACTUAL)
            // Ini untuk operasional harian. Kita set Sub Job-nya saja.
            // Nilai HK/OT biarkan default (atau 0) sampai nanti ada sinkronisasi absen.

            DB::table('user_sub_jobs')->updateOrInsert(
                [
                    'nik'       => $request->nik,
                    'work_date' => $request->work_date,
                ],
                [
                    'sub_job'    => $request->sub_job_id, // <--- INI KUNCINYA (Sub Job terisi otomatis)
                    'updated_at' => now(),

                    // Field Default Wajib (Agar tidak error)
                    'start_work' => null,
                    'end_work'   => null,
                    // Jangan timpa HK/OT jika sudah ada isinya (gunakan DB::raw)
                    'hk'         => DB::raw('hk'),
                    'ot'         => DB::raw('ot'),
                    'ot_rev'     => DB::raw('ot_rev'),
                ]
            );

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Budget & Sub Job Karyawan berhasil disimpan!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    public function copyFromPrevious(Request $request)
    {
        $month = (int) $request->month;
        $year  = (int) $request->year;

        $currentStart = Carbon::create($year, $month, 1)->startOfMonth();
        $currentEnd   = Carbon::create($year, $month, 1)->endOfMonth();

        $prevStart = $currentStart->copy()->subMonth()->startOfMonth();
        $prevEnd   = $currentStart->copy()->subMonth()->endOfMonth();

        $prevBudgets = DB::table('user_sub_job_budgets')
            ->whereBetween('work_date', [
                $prevStart->toDateString(),
                $prevEnd->toDateString(),
            ])
            ->get();

        if ($prevBudgets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data bulan sebelumnya',
            ], 404);
        }

        foreach ($prevBudgets as $row) {

            $targetDate = Carbon::parse($row->work_date)->addMonth();

            // skip tanggal tidak valid (31 -> Feb)
            if (! $targetDate->isValid()) {
                continue;
            }

            $exists = DB::table('user_sub_job_budgets')
                ->where('sub_job', $row->sub_job)
                ->where('work_date', $targetDate->toDateString())
                ->exists();

            // ❗ HANYA INSERT JIKA BELUM ADA
            if (! $exists) {
                DB::table('user_sub_job_budgets')->updateOrInsert([
                    'sub_job'   => $row->sub_job,
                    'work_date' => $targetDate->toDateString(),
                ], [
                    'qty'        => $row->qty, // 🔥 NILAI ASLI
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Budget berhasil disalin dari bulan sebelumnya',
        ]);
    }

}
