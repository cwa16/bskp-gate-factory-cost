<?php
namespace App\Http\Controllers;

use App\Models\SubJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubJobPlanActualSummaryController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end   = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        /* ================= DATES ================= */
        $dates = collect();
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $dates->push($d->copy());
        }

        /* ================= MASTER SUB JOB ================= */
        $subJobs = SubJob::orderBy('area')
            ->orderBy('code')
            ->get();

        /* ================= PLAN (BY SUB JOB) ================= */
        $plan = DB::table('user_sub_jobs as usj')
            ->select(
                'usj.sub_job',
                'usj.work_date',
                DB::raw('COUNT(DISTINCT usj.nik) as total')
            )
            ->whereBetween('usj.work_date', [
                $start->toDateString(),
                $end->toDateString()
            ])
            ->groupBy('usj.sub_job', 'usj.work_date')
            ->get()
            ->groupBy(fn ($r) => $r->sub_job);

        /* ================= ACTUAL (BY SUB JOB) ================= */
        $actual = DB::table('test_absen_regs as a')
            ->join('user_sub_jobs as usj', function ($join) {
                $join->on(
                        DB::raw('usj.nik COLLATE utf8mb4_unicode_ci'),
                        '=',
                        DB::raw('a.user_id COLLATE utf8mb4_unicode_ci')
                    )
                    ->on('usj.work_date', '=', 'a.date');
            })
            ->select(
                'usj.sub_job',
                'a.date as work_date',
                DB::raw('COUNT(DISTINCT a.user_id) as total')
            )
            ->whereBetween('a.date', [
                $start->toDateString(),
                $end->toDateString()
            ])
            ->where('a.desc', 'H')
            ->groupBy('usj.sub_job', 'a.date')
            ->get()
            ->groupBy(fn ($r) => $r->sub_job);

        return view('sub-job-plan-actual.index', compact(
            'month',
            'dates',
            'subJobs',
            'plan',
            'actual'
        ));
    }
}
