<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeductionWageController extends Controller
{
    public function index(Request $request)
    {
        // Filter Bulan & Tahun
        $month = $request->input('month', date('n'));
        $year  = $request->input('year', date('Y'));

        // Buat format periode, misal: "2026-05" untuk disimpan di kolom 'month'
        $period = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

        // Ambil Data Karyawan (Sama seperti filter absen)
        $employees = DB::table('users')
            ->select('nik', 'name', 'jabatan', 'status')
            ->where('active', '!=', 'no')
            ->where('dept', 'Factory')
            ->orderBy('status', 'desc')
            ->orderBy('name')
            ->get();

        // Ambil Data Potongan Bulan Tersebut
        $deductionsRaw = DB::table('deduction_wages')
            ->where('month', $period)
            ->get();

        // Mapping Data Potongan berdasarkan NIK
        $deductions = [];
        foreach ($deductionsRaw as $d) {
            $deductions[$d->nik] = $d;
        }

        return view('deduction-wages.index', compact('employees', 'deductions', 'month', 'year', 'period'));
    }

    public function update(Request $request)
    {
        // Terima data dari AJAX Auto-Save
        $nik    = $request->nik;
        $period = $request->month; // Format "2026-05"
        $field  = $request->field; // Nama kolom (misal: spsi, astek, other)
        $value  = (float) $request->value;

        // Validasi kolom (Pastikan 'other' juga masuk karena Anda baru menambahkannya)
        $allowedFields = ['spsi', 'astek', 'listrik', 'kantin', 'spd_motor', 'bank', 'other'];
        if (! in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Kolom tidak valid']);
        }

        // =================================================================
        // LOGIKA KEY: Gunakan 'nik' dan 'month' sebagai kunci pencarian
        // =================================================================
        $record = DB::table('deduction_wages')
            ->where('nik', $nik)
            ->where('month', $period)
            ->first();

        if ($record) {
            // Jika Key (NIK + Month) SUDAH ADA -> Lakukan UPDATE
            DB::table('deduction_wages')
                ->where('id', $record->id)
                ->update([
                    $field       => $value,
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
        } else {
            // Jika Key BELUM ADA -> Lakukan INSERT
            // (Nilai kolom lain di-set 0 agar tidak terjadi error SQL "No Default Value")
            DB::table('deduction_wages')->insert([
                'nik'        => $nik,
                'month'      => $period,
                'spsi'       => $field == 'spsi' ? $value : 0,
                'astek'      => $field == 'astek' ? $value : 0,
                'listrik'    => $field == 'listrik' ? $value : 0,
                'kantin'     => $field == 'kantin' ? $value : 0,
                'spd_motor'  => $field == 'spd_motor' ? $value : 0,
                'bank'       => $field == 'bank' ? $value : 0,
                'other'      => $field == 'other' ? $value : 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
