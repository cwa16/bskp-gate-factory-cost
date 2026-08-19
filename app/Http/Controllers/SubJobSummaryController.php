<?php

namespace App\Http\Controllers;

use App\Models\SubJob;
use App\Models\UserSubJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubJobSummaryController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end   = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        /* ================= DATES ================= */
        $dates = [];
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $dates[] = $d->copy();
        }

        /* ================= SUB JOB ================= */
        $subJobs = SubJob::orderBy('code')->get();

        /* ================= REKAP DATA ================= */
        $summary = UserSubJob::select(
            'sub_job',
            'work_date',
            DB::raw('COUNT(*) as total')
        )
            ->whereBetween('work_date', [$start, $end])
            ->groupBy('sub_job', 'work_date')
            ->get()
            ->groupBy('sub_job');

        /*
        Struktur:
        [
          1 => [
              { work_date: 2026-01-01, total: 5 },
              ...
          ],
          2 => [...]
        ]
        */

        return view('sub-job.summary', compact(
            'month',
            'dates',
            'subJobs',
            'summary'
        ));
    }
}
