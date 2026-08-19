<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoronganController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year  = $request->input('year', date('Y'));

        // 1. Ambil List Pekerjaan Borongan
        $boronganJobs = DB::table('sub_jobs')
            ->where('payment_system', 'borongan')
            ->get();

        $subJobId = $request->input('sub_job_id');
        if (! $subJobId && $boronganJobs->isNotEmpty()) {
            $subJobId = $boronganJobs->first()->id;
        }

        // =========================================================
        // 2. QUERY KARYAWAN DARI TABEL USER_SUB_JOBS (AKTUAL)
        // =========================================================
        $users = DB::table('users')
            ->join('user_sub_jobs', 'users.nik', '=', 'user_sub_jobs.nik')
            ->join('sub_jobs', 'user_sub_jobs.sub_job', '=', 'sub_jobs.id')
            ->select('users.nik', 'users.name')
            ->whereIn('users.status', ['Regular', 'Contract FL'])
            ->where('sub_jobs.payment_system', 'borongan')
            ->whereMonth('user_sub_jobs.work_date', $month)
            ->whereYear('user_sub_jobs.work_date', $year)
            ->where('user_sub_jobs.sub_job', $subJobId)
            ->distinct()
            ->orderBy('users.name')
            ->get();

        // 3. Ambil Data Aktual Qty Borongan
        $existingData = DB::table('user_sub_jobs')
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->where('sub_job', $subJobId)
            ->get();

        $mapData = [];
        foreach ($existingData as $row) {
            $day                      = (int) date('j', strtotime($row->work_date));
            $mapData[$row->nik][$day] = $row->qty;
        }

        $daysInMonth = \Carbon\Carbon::createFromDate($year, $month)->daysInMonth;

        // =========================================================
        // 4. AMBIL RATE DARI MASTER_COSTS (STATUS = DW)
        // =========================================================
        // (Asumsi nama kolom harganya adalah 'cost_per_day', jika beda silakan disesuaikan)
        $rateDW = DB::table('master_costs')
            ->where('year', $year)
            ->where('status', 'DW')
            ->value('cost_per_day') ?? 0;

        // =========================================================
        // 5. AMBIL DATA POTONGAN DARI DEDUCTION_WAGES
        // =========================================================
        $deductionsRaw = DB::table('deduction_wages')
            ->where('month', $month) // Menggunakan bulan Integer
            ->get();

        $deductionsMap = [];
        foreach ($deductionsRaw as $d) {
            $deductionsMap[$d->nik] = $d;
        }

        return view('borongan.index', compact(
            'month', 'year', 'boronganJobs', 'subJobId', 'users', 'mapData', 'daysInMonth', 'rateDW', 'deductionsMap'
        ));
    }
    public function store(Request $request)
    {
        $request->validate([
            'nik'       => 'required',
            'work_date' => 'required|date',
            'sub_job'   => 'required|integer',
        ]);

        $qty = ($request->qty === '' || $request->qty === null) ? null : (int) $request->qty;

        if ($qty > 0) {
            // Jika ada inputan, Update / Insert dengan HK = 0
            DB::table('user_sub_jobs')->updateOrInsert(
                [
                    'nik'       => $request->nik,
                    'work_date' => $request->work_date,
                ],
                [
                    'sub_job'    => $request->sub_job,
                    'qty'        => $qty,
                    'hk'         => 0, // KUNCI UTAMA: Borongan tidak dihitung HK
                    'ot'         => 0, // Borongan tidak dapat OT
                    'updated_at' => now(),
                    // Field dummy wajib
                    'start_work' => null,
                    'end_work'   => null,
                ]
            );
        } else {
            // Jika dikosongkan/nol, kembalikan HK menjadi default (opsional) atau set qty = 0
            DB::table('user_sub_jobs')
                ->where('nik', $request->nik)
                ->where('work_date', $request->work_date)
                ->where('sub_job', $request->sub_job)
                ->update(['qty' => 0, 'updated_at' => now()]);
        }

        return response()->json(['success' => true]);
    }
}
