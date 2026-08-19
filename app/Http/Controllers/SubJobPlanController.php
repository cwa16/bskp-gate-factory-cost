<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class SubJobPlanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Filter Waktu
        $month       = $request->input('month', date('n'));
        $year        = $request->input('year', date('Y'));
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

        // Kita jadikan Collection keyBy 'id' agar mudah akses warnanya
        $subJobs = DB::table('sub_jobs')
            ->select('id', 'name', 'code', 'area', 'color')
            ->orderBy('id') // Urutkan by ID agar Legend urut 1, 2, 3...
            ->get()
            ->keyBy('id');

        // 3. Ambil Karyawan (DIPISAH 2 GRUP)
        // Grup 1: Regular
        $regularEmps = DB::table('users')
            ->select('id', 'nik', 'name', 'jabatan', 'status')
            ->where('active', 'yes')
            ->where('dept', 'Factory')
            ->whereNotIn('jabatan', ['Asst Mng', 'Adm B', 'Mdr', 'HM'])
            ->where('status', 'Regular')
            ->orderBy('jabatan', 'asc')
            ->get();

        // Grup 2: Contract FL
        $contractEmps = DB::table('users')
            ->select('id', 'nik', 'name', 'jabatan', 'status')
            ->where('active', 'yes')
            ->where('dept', 'Factory')
            ->whereNotIn('jabatan', ['Asst Mng', 'Adm B', 'Mdr', 'HM'])
            ->where('status', 'Contract FL')
            ->orderBy('jabatan', 'asc')
            ->get();

        // 3. Ambil Data Budget Existing (Value = ID Sub Job)
        $budgetRaw = DB::table('user_sub_job_budgets')
            ->join('users', function ($join) {
                $join->on(DB::raw('user_sub_job_budgets.nik COLLATE utf8mb4_unicode_ci'), '=', DB::raw('users.nik COLLATE utf8mb4_unicode_ci'));
            })
            ->select('users.nik', 'user_sub_job_budgets.work_date', 'user_sub_job_budgets.sub_job', 'user_sub_job_budgets.ot', 'user_sub_job_budgets.status')
            ->whereMonth('user_sub_job_budgets.work_date', $month)
            ->whereYear('user_sub_job_budgets.work_date', $year)
            ->whereIn('user_sub_job_budgets.status', ['Regular', 'Contract FL'])
            ->get();

        // Mapping: [nik][tanggal] => sub_job_id (int)
        $existingBudgets = [];
        foreach ($budgetRaw as $row) {
            $existingBudgets[$row->nik][$row->work_date] = [
                'sub_job' => $row->sub_job,
                'ot'      => $row->ot,
            ];
        }

        return view('sub-job-plan.index', compact(
            'regularEmps', 'contractEmps',
            'daysInMonth', 'month', 'year',
            'subJobs',
            'existingBudgets'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik'        => 'required',
            'work_date'  => 'required|date',
            'status'     => 'required',
            'sub_job_id' => 'nullable|numeric',
        ]);

        $user = DB::table('users')->where('nik', $request->nik)->first();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        // Mulai Transaksi Database agar aman (konsistensi 2 tabel)
        DB::beginTransaction();

        try {
            // Skenario 1: Input Kosong -> Hapus Budget
            if (empty($request->sub_job_id) || $request->sub_job_id == 0) {
                DB::table('user_sub_job_budgets')
                    ->where('nik', $user->nik)
                    ->where('work_date', $request->work_date)
                    ->where('status', $request->status)
                    ->delete();

                // Opsional: Jika Budget dihapus, apakah di Actual juga mau diset NULL?
                // Jika ya, uncomment baris di bawah ini:
                /*
            DB::table('user_sub_jobs')
                ->where('nik', $user->nik)
                ->where('work_date', $request->work_date)
                ->update(['sub_job' => null]);
            */

            } else {
                // Skenario 2: Input Ada -> Simpan Budget & Sync ke Actual

                // Cek Validitas Sub Job
                $exists = DB::table('sub_jobs')->where('id', $request->sub_job_id)->exists();
                if (! $exists) {
                    return response()->json(['success' => false, 'message' => 'Kode Job tidak valid']);
                }

                // A. SIMPAN KE TABEL PLAN (BUDGET)
                DB::table('user_sub_job_budgets')->updateOrInsert(
                    [
                        'nik'       => $user->nik,
                        'work_date' => $request->work_date,
                        // Status dijadikan kunci unik agar tidak duplikat di plan
                        'status'    => $request->status,
                    ],
                    [
                        'sub_job'    => $request->sub_job_id,
                        'qty'        => 1,
                        'updated_at' => now(),
                        // Jika baru insert, set created_at
                        'created_at' => DB::raw('IFNULL(created_at, NOW())'),
                    ]
                );

                // B. OTOMATIS SIMPAN KE TABEL ACTUAL (USER_SUB_JOBS)
                // Ini logika tambahannya:
                DB::table('user_sub_jobs')->updateOrInsert(
                    [
                        'nik'       => $user->nik,
                        'work_date' => $request->work_date,
                    ],
                    [
                        'sub_job'    => $request->sub_job_id, // Update Sub Job sesuai Plan
                        'updated_at' => now(),

                        // Gunakan IFNULL agar jika row sudah ada, jam kerja tidak tertimpa.
                        // Jika row baru, gunakan default value.
                        'start_work' => null,
                        'end_work'   => null,

                        // Kolom HK, OT, OT_REV tidak perlu ditulis disini.
                        // updateOrInsert hanya mengubah kolom yang didefinisikan di array ke-2.
                        // Jadi nilai HK/OT yang sudah ada aman (tidak hilang).
                    ]
                );
            }

            DB::commit(); // Simpan perubahan permanen
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan jika ada error
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function saveBatch(Request $request)
    {
        // Validasi input array
        $request->validate([
            'changes'             => 'required|array',
            'changes.*.nik'       => 'required',
            'changes.*.work_date' => 'required|date',
            'changes.*.status'    => 'required',
            'changes.*.field'     => 'required|in:sub_job,ot', // Memastikan field valid
            'changes.*.value'     => 'nullable',
        ]);

        $changes = $request->input('changes');

        DB::beginTransaction();
        try {
            foreach ($changes as $item) {
                $user = DB::table('users')->where('nik', $item['nik'])->first();
                if (! $user) {
                    continue;
                }
                // Skip jika user tidak ditemukan

                $field = $item['field'];
                // Konversi string kosong jadi null
                $value = ($item['value'] === '' || $item['value'] === null || (float) $item['value'] == 0) ? null : (float) $item['value'];

                // A. Simpan ke Tabel Plan (Budget)
                $planData = [
                    $field       => $value,
                    'updated_at' => now(),
                    'created_at' => DB::raw('IFNULL(created_at, NOW())'),
                ];

                // Otomatis isi qty jika yang diubah sub_job
                if ($field === 'sub_job') {
                    $planData['qty'] = $value ? 1 : 0;
                }

                DB::table('user_sub_job_budgets')->updateOrInsert(
                    [
                        'nik'       => $user->nik,
                        'work_date' => $item['work_date'],
                        'status'    => $item['status'],
                    ],
                    $planData
                );

                // B. Sync ke Tabel Actual
                $actualData = [
                    $field       => $value,
                    'updated_at' => now(),
                    'start_work' => null,
                    'end_work'   => null,
                ];

                DB::table('user_sub_jobs')->updateOrInsert(
                    [
                        'nik'       => $user->nik,
                        'work_date' => $item['work_date'],
                    ],
                    $actualData
                );

                // C. Cleanup (Hapus jika sub_job DAN ot sama-sama kosong)
                DB::table('user_sub_job_budgets')
                    ->where('nik', $user->nik)
                    ->where('work_date', $item['work_date'])
                    ->where(function ($q) {
                        $q->whereNull('sub_job')->orWhere('sub_job', 0);
                    })
                    ->where(function ($q) {
                        $q->whereNull('ot')->orWhere('ot', 0);
                    })
                    ->delete();
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function copyBudget(Request $request)
    {
        $request->validate([
            'from_month' => 'required|integer',
            'from_year'  => 'required|integer',
            'to_month'   => 'required|integer',
            'to_year'    => 'required|integer',
            'status'     => 'required|string', // Regular / Contract FL
        ]);

        $fromMonth = $request->from_month;
        $fromYear  = $request->from_year;
        $toMonth   = $request->to_month;
        $toYear    = $request->to_year;
        $status    = $request->status;

        // 1. Ambil Data Sumber (Source)
        $sourceBudgets = DB::table('user_sub_job_budgets')
            ->whereMonth('work_date', $fromMonth)
            ->whereYear('work_date', $fromYear)
            ->where('status', $status)
            ->get();

        if ($sourceBudgets->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Data sumber kosong/tidak ditemukan!']);
        }

        // 2. Mapping Data Sumber: [NIK][Hari] = Data
        // Kita simpan Hari-nya saja (1-31) sebagai key
        $sourceMap = [];
        foreach ($sourceBudgets as $row) {
            $day                        = (int) date('j', strtotime($row->work_date));
            $sourceMap[$row->nik][$day] = [
                'sub_job' => $row->sub_job,
                'qty'     => $row->qty,
                'ot'      => $row->ot, // <--- TAMBAHKAN BARIS INI DI SINI
            ];
        }

        // 3. Hitung Jumlah Hari
        $daysInSource = \Carbon\Carbon::create($fromYear, $fromMonth)->daysInMonth;
        $daysInTarget = \Carbon\Carbon::create($toYear, $toMonth)->daysInMonth;

        DB::beginTransaction();
        try {
            // Hapus data lama di bulan target (Agar bersih/replace)
            DB::table('user_sub_job_budgets')
                ->whereMonth('work_date', $toMonth)
                ->whereYear('work_date', $toYear)
                ->where('status', $status)
                ->delete();

            // 4. Proses Copy (Looping per NIK yang ada di source)
            foreach ($sourceMap as $nik => $daysData) {

                // Loop untuk mengisi Target (1 s/d 30/31)
                for ($d = 1; $d <= $daysInTarget; $d++) {

                    // LOGIKA PENGISI KEKURANGAN HARI (MODULO)
                    // Rumus: Jika d > source, ambil dari awal lagi.
                    // Pointer akan selalu bernilai 1 sampai daysInSource.
                    $pointerDay = (($d - 1) % $daysInSource) + 1;

                    if (isset($daysData[$pointerDay])) {
                        $data       = $daysData[$pointerDay];
                        $targetDate = sprintf('%s-%02d-%02d', $toYear, $toMonth, $d);

                        // A. Simpan ke Budget (Plan)
                        DB::table('user_sub_job_budgets')->insert([
                            'nik'        => $nik,
                            'work_date'  => $targetDate,
                            'status'     => $status,
                            'sub_job'    => $data['sub_job'],
                            'qty'        => $data['qty'],
                            'ot'         => $data['ot'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // B. Sync ke Actual (User Sub Jobs)
                        // Agar operasional harian langsung terupdate
                        DB::table('user_sub_jobs')->updateOrInsert(
                            [
                                'nik'       => $nik,
                                'work_date' => $targetDate,
                            ],
                            [
                                'sub_job'    => $data['sub_job'],
                                'updated_at' => now(),
                                'start_work' => null,
                                'end_work'   => null,
                            ]
                        );
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil dicopy dan disesuaikan!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
