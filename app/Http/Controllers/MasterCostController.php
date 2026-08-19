<?php
namespace App\Http\Controllers;

use App\Models\MasterCost;
use Illuminate\Http\Request;

class MasterCostController extends Controller
{
    public function index()
    {
        // Ambil data diurutkan tahun terbaru, lalu status
        $costs = MasterCost::orderBy('year', 'desc')
            ->orderBy('status', 'asc')
            ->get();

        return view('master_costs.index', compact('costs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'year'         => 'required|numeric',
            'status'       => 'required|string',
            'cost_per_day' => 'required|numeric|min:0',
        ]);

        // Cek duplikasi (Tahun & Status yang sama tidak boleh dobel)
        $exists = MasterCost::where('year', $request->year)
            ->where('status', $request->status)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Data Master Cost untuk Tahun & Status tersebut sudah ada!');
        }

        MasterCost::create($request->all());

        return back()->with('success', 'Master Cost berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'year'         => 'required|numeric',
            'status'       => 'required|string',
            'cost_per_day' => 'required|numeric|min:0',
        ]);

        $cost = MasterCost::findOrFail($id);

        // Cek duplikasi jika mengubah tahun/status ke kombinasi yang sudah ada (kecuali punya sendiri)
        $exists = MasterCost::where('year', $request->year)
            ->where('status', $request->status)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Gagal update! Kombinasi Tahun & Status sudah digunakan data lain.');
        }

        $cost->update($request->all());

        return back()->with('success', 'Master Cost berhasil diperbarui!');
    }

    public function destroy($id)
    {
        MasterCost::findOrFail($id)->delete();
        return back()->with('success', 'Data berhasil dihapus!');
    }
}
