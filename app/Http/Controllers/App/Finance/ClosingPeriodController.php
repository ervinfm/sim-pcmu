<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceClosingPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClosingPeriodController extends Controller
{
    public function index()
    {
        $periods = FinanceClosingPeriod::where('organization_unit_id', auth()->user()->organization_unit_id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return Inertia::render('App/Finance/ClosingPeriod/Index', [
            'periods' => $periods
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $unitId = auth()->user()->organization_unit_id;

        // Cek apakah sudah ditutup sebelumnya
        $exists = FinanceClosingPeriod::where('organization_unit_id', $unitId)
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Periode ini sudah ditutup sebelumnya.');
        }

        FinanceClosingPeriod::create([
            'organization_unit_id' => $unitId,
            'year' => $request->year,
            'month' => $request->month,
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Tutup buku berhasil. Transaksi periode tersebut kini terkunci.');
    }

    public function destroy($id)
    {
        // Hanya Super Admin atau Role Tertentu yang boleh membuka kunci
        if (auth()->user()->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang boleh membuka kunci periode.');
        }

        $period = FinanceClosingPeriod::findOrFail($id);
        $period->delete();

        return back()->with('success', 'Periode dibuka kembali.');
    }
}