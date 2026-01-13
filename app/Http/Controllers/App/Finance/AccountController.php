<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceCoa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\FinanceJournalDetail;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Ambil semua akun yang relevan (Global + Unit Sendiri)
        $query = FinanceCoa::query()
            ->where(function($q) use ($user) {
                $q->whereNull('organization_unit_id'); // Akun Standar Pusat
                if ($user->organization_unit_id) {
                    $q->orWhere('organization_unit_id', $user->organization_unit_id); // Akun Unit
                }
            });

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        // Urutkan berdasarkan Kode agar rapi (1-1000, 1-2000, dst)
        $accounts = $query->orderBy('code')->paginate(15)->withQueryString();

        return Inertia::render('App/Finance/Accounts/Index', [
            'accounts' => $accounts,
            'filters' => $request->only(['search'])
        ]);
    }

    public function show(FinanceCoa $account)
    {
        // 1. Proteksi: Cek apakah user berhak melihat akun ini
        $user = auth()->user();
        if ($account->organization_unit_id !== null && $account->organization_unit_id !== $user->organization_unit_id && $user->role !== 'super_admin') {
            abort(403, 'Akses ditolak.');
        }

        // 2. Ambil Mutasi (Buku Besar Pembantu)
        // Kita ambil dari tabel journal_details yang terhubung ke akun ini
        $mutations = FinanceJournalDetail::with('journal')
            ->where('coa_id', $account->id)
            ->whereHas('journal', function($q) {
                $q->where('status', 'POSTED'); // Hanya yang sudah posting
            })
            ->orderByDesc('created_at') // Terbaru diatas
            ->paginate(20)
            ->withQueryString();

        // 3. Hitung Saldo Saat Ini (Realtime)
        $currentBalance = FinanceJournalDetail::where('coa_id', $account->id)
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();
        
        // Rumus Saldo Normal:
        // ASET/BEBAN: Debit - Kredit
        // KEWAJIBAN/MODAL/PENDAPATAN: Kredit - Debit
        $isDebitNormal = in_array($account->type, ['ASSET', 'EXPENSE']);
        $balance = $isDebitNormal 
            ? ($currentBalance->total_debit - $currentBalance->total_credit)
            : ($currentBalance->total_credit - $currentBalance->total_debit);

        return Inertia::render('App/Finance/Accounts/Show', [
            'account' => $account,
            'mutations' => $mutations,
            'balance' => $balance,
            'isDebitNormal' => $isDebitNormal
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20', // Unik per unit idealnya, tapi kita buat simple dulu
            'type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'is_cash' => 'boolean'
        ]);

        $user = auth()->user();

        FinanceCoa::create([
            'organization_unit_id' => $user->organization_unit_id,
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type,
            'parent_id' => null, // Simplified hierarchy
            'is_cash' => $request->is_cash ?? false,
            'is_active' => true
        ]);

        return back()->with('success', 'Akun berhasil dibuat.');
    }

    public function update(Request $request, FinanceCoa $account)
    {
        // Proteksi: Admin Unit tidak boleh edit Akun Global (Pusat)
        if ($account->organization_unit_id === null && auth()->user()->role !== 'super_admin') {
            return back()->with('error', 'Anda tidak memiliki izin mengedit Akun Standar Pusat.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'is_cash' => 'boolean'
        ]);

        $account->update([
            'name' => $request->name,
            'is_cash' => $request->is_cash
        ]);

        return back()->with('success', 'Data akun diperbarui.');
    }

    public function destroy(FinanceCoa $account)
    {
        // Proteksi 1: Cek kepemilikan
        if ($account->organization_unit_id === null && auth()->user()->role !== 'super_admin') {
            return back()->with('error', 'Akun Standar Pusat tidak dapat dihapus.');
        }

        // Proteksi 2: Cek apakah sudah dipakai transaksi (Restrict di database akan throw error, kita tangkap)
        try {
            $account->delete();
            return back()->with('success', 'Akun berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") { // Integrity constraint violation
                return back()->with('error', 'Gagal hapus: Akun ini sudah memiliki riwayat transaksi.');
            }
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}