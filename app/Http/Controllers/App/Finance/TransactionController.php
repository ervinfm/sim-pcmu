<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceTransaction;
use App\Models\FinanceJournal;
use App\Models\FinanceJournalDetail;
use App\Models\FinanceCoa;
use App\Models\OrganizationUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Menampilkan Daftar Transaksi & Saldo Dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // 1. QUERY DASAR
        $query = FinanceTransaction::with(['cashCoa', 'categoryCoa', 'organizationUnit'])
            ->latest('date')
            ->latest('created_at');

        // 2. FILTER AKSES (SCOPING)
        // Jika bukan Super Admin, kunci ke unitnya sendiri
        if ($user->role !== 'super_admin') {
            $query->where('organization_unit_id', $user->organization_unit_id);
        } else {
            // Jika Super Admin, cek apakah ada filter unit dari request
            if ($request->has('unit_id') && $request->unit_id) {
                $query->where('organization_unit_id', $request->unit_id);
            }
        }

        // 3. FILTER PENCARIAN
        if ($request->search) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // 4. HITUNG SALDO KAS (DASHBOARD CARD)
        // Kita hitung saldo real-time dari jurnal
        $balances = $this->calculateCashBalances($user);

        return Inertia::render('App/Finance/Transactions/Index', [
            'transactions' => $query->paginate(10)->withQueryString(),
            'balances' => $balances, // Data untuk Kartu Saldo (Kas Tunai, Bank, Total)
            'filters' => $request->only(['search', 'type', 'unit_id']),
            'units' => $user->role === 'super_admin' ? OrganizationUnit::select('id', 'name')->get() : []
        ]);
    }

    /**
     * Form Tambah Transaksi
     */
    public function create()
    {
        $user = auth()->user();
        
        // Ambil Daftar Akun (COA)
        // Logic: Tampilkan akun Global (unit_id NULL) DAN akun khusus Unit user
        $coaQuery = FinanceCoa::where('is_active', true)
            ->where(function($q) use ($user) {
                $q->whereNull('organization_unit_id');
                if ($user->organization_unit_id) {
                    $q->orWhere('organization_unit_id', $user->organization_unit_id);
                }
            })
            ->orderBy('code');

        // Pisahkan akun untuk Dropdown
        $cashAccounts = (clone $coaQuery)->where('is_cash', true)->get(); // Kas & Bank
        $revenueAccounts = (clone $coaQuery)->where('type', 'REVENUE')->get(); // Kategori Pemasukan
        $expenseAccounts = (clone $coaQuery)->where('type', 'EXPENSE')->get(); // Kategori Pengeluaran

        return Inertia::render('App/Finance/Transactions/Form', [
            'cashAccounts' => $cashAccounts,
            'revenueAccounts' => $revenueAccounts,
            'expenseAccounts' => $expenseAccounts,
        ]);
    }

    /**
     * Simpan Transaksi (CORE LOGIC HYBRID)
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:INCOME,EXPENSE,TRANSFER',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'cash_coa_id' => 'required|exists:finance_coas,id', // Akun Kas Utama
            
            // Jika TRANSFER, butuh akun tujuan. Jika INCOME/EXPENSE butuh Kategori.
            'category_coa_id' => 'nullable|required_unless:type,TRANSFER|exists:finance_coas,id',
            'destination_coa_id' => 'nullable|required_if:type,TRANSFER|exists:finance_coas,id',
            
            'description' => 'required|string|max:255',
            'proof' => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();
        $unitId = $user->organization_unit_id;

        DB::beginTransaction(); // Wajib pakai transaksi DB agar konsisten
        try {
            // 1. Upload Bukti (Jika ada)
            $proofPath = null;
            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('uploads/finance', 'public');
            }

            // 2. Buat Header Jurnal (Accounting Record)
            $journal = FinanceJournal::create([
                'organization_unit_id' => $unitId,
                'user_id' => $user->id,
                'transaction_date' => $request->date,
                'reference_no' => 'TRX-' . time(), // Bisa dibuat lebih canggih formatnya
                'description' => $request->description,
                'status' => 'POSTED',
                'total_amount' => $request->amount
            ]);

            // 3. Logic Double Entry (Jurnal Details)
            if ($request->type === 'INCOME') {
                // PEMASUKAN: Debit Kas, Kredit Pendapatan
                $this->createJournalEntry($journal->id, $request->cash_coa_id, $request->amount, 0); // D
                $this->createJournalEntry($journal->id, $request->category_coa_id, 0, $request->amount); // K
            } 
            elseif ($request->type === 'EXPENSE') {
                // PENGELUARAN: Debit Beban, Kredit Kas
                $this->createJournalEntry($journal->id, $request->category_coa_id, $request->amount, 0); // D
                $this->createJournalEntry($journal->id, $request->cash_coa_id, 0, $request->amount); // K
            }
            elseif ($request->type === 'TRANSFER') {
                // TRANSFER: Debit Kas Tujuan, Kredit Kas Asal
                // Note: Di form transfer, cash_coa_id adalah SUMBER, destination adalah TUJUAN
                $this->createJournalEntry($journal->id, $request->destination_coa_id, $request->amount, 0); // D (Masuk)
                $this->createJournalEntry($journal->id, $request->cash_coa_id, 0, $request->amount); // K (Keluar)
            }

            // 4. Buat Record Transaksi UI (Single Entry View)
            FinanceTransaction::create([
                'organization_unit_id' => $unitId,
                'user_id' => $user->id,
                'journal_id' => $journal->id,
                'type' => $request->type,
                'date' => $request->date,
                'cash_coa_id' => $request->cash_coa_id,
                
                // Jika transfer, category_coa_id kita pakai untuk simpan akun tujuan (hack sedikit agar hemat kolom)
                // Atau biarkan null jika Anda ingin strict. Disini kita pakai category_coa_id untuk visualisasi
                'category_coa_id' => $request->type === 'TRANSFER' ? $request->destination_coa_id : $request->category_coa_id,
                
                'amount' => $request->amount,
                'description' => $request->description,
                'proof_path' => $proofPath
            ]);

            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dicatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Transaksi (Rollback Jurnal)
     */
    public function destroy(FinanceTransaction $transaction)
    {
        // Security Check
        if (auth()->user()->role !== 'super_admin' && $transaction->organization_unit_id !== auth()->user()->organization_unit_id) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // Hapus Bukti Fisik
            if ($transaction->proof_path) {
                Storage::disk('public')->delete($transaction->proof_path);
            }

            // Hapus Jurnal (Cascade akan menghapus Journal Details)
            // Transaksi UI juga akan terhapus jika on delete cascade diset di migrasi
            // Tapi aman-nya kita hapus manual atau rely on relation
            
            if ($transaction->journal) {
                $transaction->journal->delete(); // Ini akan men-trigger delete details
            } else {
                $transaction->delete(); // Fallback jika jurnal hilang
            }

            DB::commit();
            return back()->with('success', 'Transaksi dibatalkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // --- HELPER METHODS ---

   /**
     * Simpan Akun Baru (Simple & Dynamic)
     */
    public function storeAccount(Request $request)
    {
        // 1. Validasi Input & Password Konfirmasi
        $request->validate([
            'password' => ['required', 'current_password'], // Wajib Password Login
            'name' => 'required|string|max:255',
            'type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'is_cash' => 'boolean'
        ]);

        $user = auth()->user();

        // 2. Generate Kode Otomatis Sederhana (Agar user tidak pusing mikir kode)
        // Format: TIPE-TIMESTAMP (Contoh: ASSET-17012345) agar unik
        $autoCode = $request->type . '-' . time();

        // 3. Simpan ke Database
        \App\Models\FinanceCoa::create([
            'organization_unit_id' => $user->organization_unit_id,
            'name' => $request->name,
            'code' => $autoCode,
            'type' => $request->type,
            'parent_id' => null, // Kita buat level sejajar dulu agar simpel
            'is_cash' => $request->is_cash ?? false,
            'is_active' => true
        ]);

        return back()->with('success', 'Akun berhasil ditambahkan.');
    }
    
    /**
     * Helper untuk insert baris jurnal
     */
    private function createJournalEntry($journalId, $coaId, $debit, $credit)
    {
        FinanceJournalDetail::create([
            'journal_id' => $journalId,
            'coa_id' => $coaId,
            'debit' => $debit,
            'credit' => $credit,
            'fund_type' => 'UNRESTRICTED' // Default Dana Bebas, nanti bisa dikembangkan
        ]);
    }

    /**
     * Helper Hitung Saldo untuk Dashboard
     */
    private function calculateCashBalances($user)
    {
        // Jika Super Admin tidak pilih unit, tampilkan total global
        // Jika User Unit, tampilkan saldo unitnya saja
        
        $unitId = $user->organization_unit_id; // Bisa null (Global)

        // Query Agregat
        // Kita cari COA yang is_cash = true
        // Lalu sum (Debit - Kredit) dari tabel journal_details
        
        $query = FinanceCoa::where('is_cash', true)->withSum(['journalDetails as balance' => function($q) use ($unitId) {
            // Filter jurnal berdasarkan Unit
            if ($unitId) {
                $q->whereHas('journal', function($sub) use ($unitId) {
                    $sub->where('organization_unit_id', $unitId);
                });
            }
            // Rumus Saldo Normal Aset = Debit - Kredit
            $q->select(DB::raw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0)'));
        }], 'balance');

        $accounts = $query->get();

        return [
            'total' => $accounts->sum('balance'),
            'details' => $accounts->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'balance' => $a->balance ?? 0
            ])
        ];
    }
}