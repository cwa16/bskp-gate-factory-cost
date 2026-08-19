<?php
namespace App\Http\Controllers;

use App\Models\BudgetCostPerKg;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetCostPerKgController extends Controller
{
    public function index(Request $request)
    {
        $month       = $request->input('month', date('n'));
        $year        = $request->input('year', date('Y'));
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        $areas = DB::table('sub_jobs')->distinct()->pluck('area')->toArray();

        // Tarik data budget bulan tersebut
        $budgets = BudgetCostPerKg::whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->get();

        // Mapping data agar mudah ditampilkan di Blade: $budgetMap['2026-03-01']['Proses'] = 350
        $budgetMap = [];
        foreach ($budgets as $b) {
            $budgetMap[$b->work_date][$b->area] = $b->budget_cpk;
        }

        return view('budget-cpk.index', compact('month', 'year', 'daysInMonth', 'areas', 'budgetMap'));
    }

    public function store(Request $request)
    {
        // Data dari view akan berbentuk array: $request->budgets['2026-03-01']['Proses'] = 350
        $budgetsInput = $request->input('budgets', []);

        DB::beginTransaction();
        try {
            foreach ($budgetsInput as $date => $areaData) {
                foreach ($areaData as $area => $val) {
                    // Jika user mengisi angka (termasuk 0)
                    if ($val !== null && $val !== '') {
                        BudgetCostPerKg::updateOrCreate(
                            ['work_date' => $date, 'area' => $area],
                            ['budget_cpk' => $val]
                        );
                    } else {
                        // Jika field dikosongkan, hapus dari database
                        BudgetCostPerKg::where('work_date', $date)->where('area', $area)->delete();
                    }
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Budget Harian berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
