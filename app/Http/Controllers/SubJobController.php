<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class SubJobController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('btn_filter')) {
            if ($request->input('btn_filter') == '2') {
                $month = $request->input('month', date('n'));
                $year  = $request->input('year', date('Y'));

                if (method_exists($this, 'syncAttendanceToSubJobs')) {
                    $this->syncAttendanceToSubJobs($month, $year);
                }
                return Redirect::back();
            } else {
                $month = $request->input('month', date('n'));
                $year  = $request->input('year', date('Y'));
                return Redirect::route('subjob.index', ['month' => $month, 'year' => $year]);
            }
        }

        $month       = (int) $request->input('month', date('n'));
        $year        = (int) $request->input('year', date('Y'));
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

        $costs         = DB::table('master_costs')->where('year', $year)->pluck('cost_per_day', 'status')->toArray();
        $umrTotal      = DB::table('master_umr')->where('year', $year)->value('total') ?? 0;
        $otRatePerHour = $umrTotal > 0 ? ($umrTotal / 173) : 0;

        $employees = DB::table('users')
            ->select('id', 'nik', 'name', 'jabatan', 'status')
            ->where('active', 'yes')
            ->where('dept', 'Factory')
            ->whereNotIn('jabatan', ['Adm A', 'Adm B', 'Asst Mng', 'HM', 'Mdr'])
            ->whereIn('status', ['Regular', 'Contract FL'])
            ->orderBy('status', 'desc')
            ->orderBy('nik')
            ->get();

        $holidaysRaw = DB::table('holidays')->whereMonth('date', $month)->whereYear('date', $year)->get(['date', 'info']);
        $holidayMap  = [];
        foreach ($holidaysRaw as $h) {
            $holidayMap[$h->date] = $h->info;
        }

        $subJobsRaw = DB::table('user_sub_jobs')
            ->join('sub_jobs', 'user_sub_jobs.sub_job', '=', 'sub_jobs.id')
            ->select(
                'user_sub_jobs.nik', 'user_sub_jobs.work_date', 'user_sub_jobs.hk',
                'user_sub_jobs.ot', 'user_sub_jobs.ot_rev', 'user_sub_jobs.desc',
                'user_sub_jobs.qty', 'sub_jobs.payment_system'
            )
            ->whereMonth('user_sub_jobs.work_date', $month)
            ->whereYear('user_sub_jobs.work_date', $year)
            ->get();

        $dataMap = [];
        foreach ($subJobsRaw as $row) {
            $dateOnly = date('Y-m-d', strtotime($row->work_date));

            if (! isset($dataMap[$row->nik][$dateOnly])) {
                $dataMap[$row->nik][$dateOnly] = [
                    'has_borongan' => false, 'hk' => 0, 'ot' => 0, 'ot_rev' => null, 'desc' => '',
                ];
            }

            if ($row->payment_system == 'borongan' && (float) $row->qty > 0) {
                $dataMap[$row->nik][$dateOnly]['has_borongan'] = true;
            } else {
                $specialCodes = ['CT', 'CB', 'CH', 'CLL', 'CL', 'S'];
                $cleanDesc    = strtoupper(trim($row->desc));

                if (in_array($cleanDesc, $specialCodes)) {
                    $hkVal = 1;
                } else {
                    $hkVal = is_numeric($row->hk) ? (float) $row->hk : 0;
                }

                $otVal = (float) $row->ot;

                $dataMap[$row->nik][$dateOnly]['hk'] = max($dataMap[$row->nik][$dateOnly]['hk'], $hkVal);
                $dataMap[$row->nik][$dateOnly]['ot'] = max($dataMap[$row->nik][$dateOnly]['ot'], $otVal);

                if (! is_null($row->ot_rev) && trim($row->ot_rev) !== '') {
                    $dataMap[$row->nik][$dateOnly]['ot_rev'] = max((float) $dataMap[$row->nik][$dateOnly]['ot_rev'], (float) $row->ot_rev);
                }

                if ($row->desc && $dataMap[$row->nik][$dateOnly]['desc'] === '') {
                    $dataMap[$row->nik][$dateOnly]['desc'] = $row->desc;
                }
            }
        }

        $deductionsRaw = DB::table('deduction_wages')->where('month', $month)->get();
        $deductionsMap = [];
        foreach ($deductionsRaw as $d) {
            $deductionsMap[$d->nik] = $d;
        }

        $matrix         = [];
        $employeeTotals = [];

        foreach ($employees as $emp) {
            $empRate           = $costs[$emp->status] ?? 0;
            $empTotalHk        = 0;
            $empTotalOt        = 0;
            $empTotalPayableOt = 0;
            $empTotalRp        = 0;
            $empTotalHkRp      = 0;
            $empTotalOtRp      = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateObj = Carbon::createFromDate($year, $month, $d);
                $dateStr = $dateObj->format('Y-m-d');

                $isSunday    = $dateObj->isSunday();
                $isFriday    = $dateObj->isFriday();
                $holidayInfo = $holidayMap[$dateStr] ?? null;
                $isHoliday   = $isSunday || ! is_null($holidayInfo);
                $holidayName = $holidayInfo ?? ($isSunday ? 'Minggu' : '');

                $dayRecord = $dataMap[$emp->nik][$dateStr] ?? null;

                $hkVal = 0;
                $otVal = 0;
                $otRev = '';
                $desc  = '';
                $hkRaw = '';

                if ($dayRecord) {
                    if ($dayRecord['has_borongan'] === true) {
                        $hkVal = 0;
                        $otVal = 0;
                        $otRev = '';
                        $desc  = 'BORONGAN';
                        $hkRaw = '';
                    } else {
                        $hkVal = $dayRecord['hk'];
                        $otVal = $dayRecord['ot'];
                        $otRev = $dayRecord['ot_rev'];
                        $desc  = $dayRecord['desc'];
                        $hkRaw = $dayRecord['hk'];
                    }
                }

                $finalOt = ($otRev !== '' && ! is_null($otRev)) ? (float) $otRev : $otVal;

                // ====================================================================
                // LOGIKA MULTIPLIER OT (HARI KERJA NORMAL, LIBUR & MINGGU)
                // Berdasarkan Kepmenakertrans No. 102/MEN/VI/2004
                // ====================================================================
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
                        // Jam 1 Pertama = x 1.5
                        // Jam 2 dan Seterusnya = x 2
                        if ($finalOt <= 1) {
                            $payableOt = $finalOt * 1.5;
                        } else {
                            $payableOt = (1 * 1.5) + (($finalOt - 1) * 2);
                        }
                    }
                }

                $rupiahHk = $hkVal * $empRate;
                $rupiahOt = $payableOt * $otRatePerHour;

                $empTotalHk        += $hkVal;
                $empTotalOt        += $finalOt;
                $empTotalPayableOt += $payableOt;
                $empTotalHkRp      += $rupiahHk;
                $empTotalOtRp      += $rupiahOt;
                $empTotalRp        += ($rupiahHk + $rupiahOt);

                $matrix[$emp->nik][$d] = [
                    'hk_raw'     => $hkRaw,
                    'hk'         => $hkVal,
                    'ot'         => $otVal,
                    'ot_rev'     => $otRev,
                    'ot_calc'    => $payableOt,
                    'desc'       => $desc,
                    'date'       => $dateStr,
                    'is_holiday' => $isHoliday,
                    'holiday_nm' => $holidayName,
                ];
            }

            $ded       = $deductionsMap[$emp->nik] ?? null;
            $spsi      = $ded ? (float) $ded->spsi : 0;
            $astek     = $ded ? (float) $ded->astek : 0;
            $listrik   = $ded ? (float) $ded->listrik : 0;
            $kantin    = $ded ? (float) $ded->kantin : 0;
            $spd_motor = $ded ? (float) $ded->spd_motor : 0;
            $bank      = $ded ? (float) $ded->bank : 0;
            $other     = $ded ? (float) $ded->other : 0;

            $totalPotongan = $spsi + $astek + $listrik + $kantin + $spd_motor + $bank + $other;
            $wagesNetto    = $empTotalRp - $totalPotongan;

            $employeeTotals[$emp->nik] = [
                'total_hk'       => $empTotalHk,
                'total_ot'       => $empTotalOt,
                'total_ot_final' => $empTotalPayableOt,
                'total_rp'       => $empTotalRp,
                'total_hk_rp'    => $empTotalHkRp,
                'total_ot_rp'    => $empTotalOtRp,
                'spsi'           => $spsi, 'astek'           => $astek, 'listrik'  => $listrik,
                'kantin'         => $kantin, 'spd_motor'     => $spd_motor, 'bank' => $bank,
                'other'          => $other, 'total_potongan' => $totalPotongan,
                'wages_netto'    => $wagesNetto,
            ];
        }

        return view('sub-job.index', compact('employees', 'matrix', 'employeeTotals', 'daysInMonth', 'month', 'year'));
    }

    public function revise(Request $request)
    {
        $nik      = $request->nik;
        $date     = $request->date;
        $ot_value = $request->ot_value;

        if ($ot_value === '' || is_null($ot_value)) {$ot_value = null;}

        DB::table('user_sub_jobs')->where('nik', $nik)->where('work_date', $date)->update(['ot_rev' => $ot_value]);
        return response()->json(['success' => true]);
    }

    private function syncAttendanceToSubJobs($month, $year)
    {
        $holidays = DB::table('holidays')->whereMonth('date', $month)->whereYear('date', $year)->pluck('date')->toArray();

        $startDate = \Carbon\Carbon::create($year, $month, 1)->format('Y-m-d');
        $endDate   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->addDay()->format('Y-m-d');

        $validAttendance = DB::table('test_absen_regs')
            ->join('users', 'test_absen_regs.user_id', '=', 'users.nik')
            ->select('users.nik', 'test_absen_regs.date', 'test_absen_regs.overtime_hour', 'test_absen_regs.overtime_minute', 'test_absen_regs.desc', 'test_absen_regs.start_work', 'test_absen_regs.end_work')
            ->whereBetween('test_absen_regs.date', [$startDate, $endDate])
            ->get();

        foreach ($validAttendance as $row) {
            $workDate = $row->date;
            if (! empty($row->start_work)) {
                $startHour = (int) date('H', strtotime($row->start_work));
                if ($startHour >= 20) {
                    if (strlen(trim($row->start_work)) > 10) {
                        $workDate = \Carbon\Carbon::parse($row->start_work)->format('Y-m-d');
                    } else {
                        $workDate = \Carbon\Carbon::parse($row->date)->subDay()->format('Y-m-d');
                    }
                }
            }

            $carbonDate = \Carbon\Carbon::parse($workDate);
            if ($carbonDate->month != $month) {continue;}

            $isSunday  = $carbonDate->isSunday();
            $isFriday  = $carbonDate->isFriday();
            $isHoliday = in_array($workDate, $holidays);

            $jamUtama   = (int) ($row->overtime_hour ?? 0);
            $menit      = (int) ($row->overtime_minute ?? 0);
            $tambahan   = ($menit >= 30) ? 0.5 : 0;
            $roundedJam = $jamUtama + $tambahan;

            // ====================================================================
            // KOREKSI MESIN ABSEN: OT HARI JUMAT (Jam Normal = 5 Jam)
            // ====================================================================
            // Karena mesin memukul rata 7 jam/hari, maka OT di hari Jumat kurang 2 jam jika karyawan pulang sore.
            if ($isFriday && ! empty($row->start_work) && ! empty($row->end_work)) {
                $start = \Carbon\Carbon::parse($row->start_work);
                $end   = \Carbon\Carbon::parse($row->end_work);

                $totalMinutes = $start->diffInMinutes($end);

                // Normal Jumat = 5 Jam Kerja + 1 Jam Istirahat = 6 Jam (360 menit) di pabrik
                if ($totalMinutes > 360) {
                    $otMinutes    = $totalMinutes - 360;
                    $calcJam      = floor($otMinutes / 60);
                    $calcTambahan = (($otMinutes % 60) >= 30) ? 0.5 : 0;

                    // Gunakan nilai terbesar (Jika mesin salah hitung, pakai kalkulasi ini)
                    $roundedJam = max($roundedJam, $calcJam + $calcTambahan);
                }
            }

            if (strtoupper($row->desc) === 'MX' && ! $isFriday && $roundedJam > 0) {
                $roundedJam = max(0, $roundedJam - 1);
            }

            $isAbsent = ! in_array($row->desc, ['H', 'TA', 'L']);
            $hkValue  = ($isSunday || $isHoliday || $isAbsent) ? 0 : 1;

            DB::table('user_sub_jobs')->updateOrInsert(
                ['nik' => $row->nik, 'work_date' => $workDate],
                ['hk' => $hkValue, 'ot' => $roundedJam, 'desc' => $row->desc, 'updated_at' => now(), 'start_work' => $row->start_work ?? null, 'end_work' => $row->end_work ?? null]
            );
        }
    }

    public function wagesSummary(Request $request)
    {
        $month = (int) $request->input('month', date('n'));
        $year  = (int) $request->input('year', date('Y'));
        $data  = $this->getWagesData($month, $year);
        return view('sub-job.wages-summary', array_merge($data, ['month' => $month, 'year' => $year]));
    }

    private function getWagesData($month, $year)
    {
        $costs         = DB::table('master_costs')->where('year', $year)->pluck('cost_per_day', 'status')->toArray();
        $umrTotal      = DB::table('master_umr')->where('year', $year)->value('total') ?? 0;
        $otRatePerHour = $umrTotal > 0 ? ($umrTotal / 173) : 0;

        $holidaysRaw = DB::table('holidays')->whereMonth('date', $month)->whereYear('date', $year)->pluck('date')->toArray();

        $employees = DB::table('users')
            ->where('active', 'yes')->where('dept', 'Factory')
            ->whereNotIn('jabatan', ['Adm A', 'Adm B', 'Asst Mng', 'HM', 'Mdr'])
            ->whereIn('status', ['Regular', 'Contract FL'])
            ->get();

        $subJobsRaw = DB::table('user_sub_jobs')
            ->join('sub_jobs', 'user_sub_jobs.sub_job', '=', 'sub_jobs.id')
            ->whereMonth('user_sub_jobs.work_date', $month)
            ->whereYear('user_sub_jobs.work_date', $year)
            ->get();

        $employeeTotals = [];
        foreach ($employees as $emp) {
            $empRate           = $costs[$emp->status] ?? 0;
            $empTotalHk        = 0;
            $empTotalOt        = 0;
            $empTotalPayableOt = 0;
            $empTotalHkRp      = 0;
            $empTotalOtRp      = 0;

            $empRecords = $subJobsRaw->where('nik', $emp->nik);
            $grouped    = $empRecords->groupBy(function ($item) {return date('Y-m-d', strtotime($item->work_date));});

            foreach ($grouped as $date => $recs) {
                $maxHk    = $recs->max('hk');
                $maxOt    = $recs->max('ot');
                $maxOtRev = $recs->max('ot_rev');

                $descList  = $recs->pluck('desc')->toArray();
                $isSpecial = false;
                foreach ($descList as $dsc) {
                    if (in_array(strtoupper(trim($dsc)), ['CT', 'CB', 'CH', 'CLL', 'CL', 'S'])) {
                        $isSpecial = true;
                        break;
                    }
                }
                $finalHk = $isSpecial ? 1 : (float) $maxHk;
                $finalOt = (! is_null($maxOtRev) && trim($maxOtRev) !== '') ? (float) $maxOtRev : (float) $maxOt;

                $dateObj   = Carbon::parse($date);
                $isSunday  = $dateObj->isSunday();
                $isFriday  = $dateObj->isFriday();
                $isHoliday = $isSunday || in_array($date, $holidaysRaw);

                $payableOt = $finalOt;

                if ($isHoliday && $finalOt > 0) {
                    if ($isFriday) {
                        if ($finalOt <= 5) {
                            $payableOt = $finalOt * 2;
                        } elseif ($finalOt <= 6) {
                            $payableOt = (5 * 2) + (($finalOt - 5) * 3);
                        } else {
                            $payableOt = (5 * 2) + (1 * 3) + (($finalOt - 6) * 4);
                        }
                    } else {
                        if ($finalOt <= 7) {
                            $payableOt = $finalOt * 2;
                        } elseif ($finalOt <= 8) {
                            $payableOt = (7 * 2) + (($finalOt - 7) * 3);
                        } else {
                            $payableOt = (7 * 2) + (1 * 3) + (($finalOt - 8) * 4);
                        }
                    }
                }

                $empTotalHk        += $finalHk;
                $empTotalOt        += $finalOt;
                $empTotalPayableOt += $payableOt;
                $empTotalHkRp      += ($finalHk * $empRate);
                $empTotalOtRp      += ($payableOt * $otRatePerHour);
            }

            $employeeTotals[$emp->nik] = [
                'total_hk'       => $empTotalHk,
                'total_ot'       => $empTotalOt,
                'total_ot_final' => $empTotalPayableOt,
                'total_ot_rp'    => $empTotalOtRp,
                'total_hk_rp'    => $empTotalHkRp,
                'total_rp'       => ($empTotalHkRp + $empTotalOtRp),
            ];
        }

        return compact('employees', 'employeeTotals');
    }

    public function exportSummary(Request $request)
    {
        $month = (int) $request->input('month', date('n'));
        $year  = (int) $request->input('year', date('Y'));

        $data          = $this->getWagesData($month, $year);
        $data['month'] = $month;
        $data['year']  = $year;

        $fileName = "Wages_Summary_" . $year . "_" . $month . ".xls";

        return response(view('sub-job.export-summary', $data))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}
