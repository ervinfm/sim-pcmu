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
        // Ambil data periode yang sudah ditutup untuk Unit ini
        $periods = FinanceClosingPeriod::where('organization_unit_id', auth()->user()->organization_unit_id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(function($period) {
                return [
                    'id' => $period->id,
                    'year' => $period->year,
                    'month' => $period->month,
                    'closed_at_formatted' => $period->closed_at->format('d M Y H:i'),
                    'closed_by_name' => $period->closer->name ?? 'Sistem',
                ];
            });

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

        // Validasi 1: Cek Duplikasi
        $exists = FinanceClosingPeriod::where('organization_unit_id', $unitId)
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Periode ini sudah ditutup sebelumnya.');
        }

        // Validasi 2: Jangan izinkan menutup periode masa depan (Next Month)
        // Kita hanya boleh menutup bulan yang sudah lewat atau bulan berjalan
        $inputDate = \Carbon\Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth();
        if ($inputDate->isFuture() && $inputDate->month > now()->month) {
             return back()->with('error', 'Anda tidak dapat menutup buku untuk bulan yang belum berakhir.');
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
        // Validasi Role: Hanya Super Admin
        if (auth()->user()->role !== 'super_admin') {
            return back()->with('error', 'Akses Ditolak. Hubungi Administrator Pusat untuk membuka kunci periode.');
        }

        $period = FinanceClosingPeriod::findOrFail($id);
        
        // Pastikan hanya menghapus milik unit user tersebut (jika super admin pun terikat unit)
        // Atau biarkan bebas jika super admin global.
        
        $period->delete();

        return back()->with('success', 'Periode berhasil dibuka kembali. Transaksi dapat diedit.');
    }
}