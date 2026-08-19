<?php
namespace App\Http\Controllers;

use App\Models\RssProductionBudget;
use Illuminate\Http\Request;

class RssProductionBudgetController extends Controller
{
    public function index()
    {
        // Ambil data dan urutkan dari tahun & bulan terbaru
        $budgets = RssProductionBudget::orderBy('year', 'desc')->orderBy('month', 'desc')->get();
        return view('rss-budget.index', compact('budgets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'month'      => 'required|integer|min:1|max:12',
            'year'       => 'required|integer',
            'target_qty' => 'required|numeric|min:0',
        ]);

        // Gunakan updateOrCreate agar kalau datanya sudah ada, dia cuma meng-update, bukan duplikat
        RssProductionBudget::updateOrCreate(
            ['month' => $request->month, 'year' => $request->year],
            ['target_qty' => $request->target_qty]
        );

        return redirect()->back()->with('success', 'Budget produksi RSS#1 berhasil disimpan!');
    }
}
