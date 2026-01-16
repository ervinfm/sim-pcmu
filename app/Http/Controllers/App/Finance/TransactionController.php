<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceTransaction;
use App\Models\FinanceJournal;
use App\Models\FinanceJournalDetail;
use App\Models\FinanceCoa;
use App\Models\FinanceClosingPeriod;
use App\Models\OrganizationUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Menampilkan Daftar Transaksi (Client Side Filtering)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = FinanceTransaction::with(['cashCoa', 'categoryCoa', 'organizationUnit'])
            ->latest('date')
            ->latest('created_at');

        if ($user->role !== 'super_admin') {
            $query->where('organization_unit_id', $user->organization_unit_id);
        } else {
            if ($request->has('unit_id') && $request->unit_id) {
                $query->where('organization_unit_id', $request->unit_id);
            }
        }

        // Hitung Saldo Real-time
        $balances = $this->calculateCashBalances($user);

        return Inertia::render('App/Finance/Transactions/Index', [
            // Ambil 2000 data terakhir untuk performa client-side yang optimal
            'transactions' => $query->limit(2000)->get(), 
            'balances' => $balances,
            'units' => $user->role === 'super_admin' ? OrganizationUnit::select('id', 'name')->get() : []
        ]);
    }

    /**
     * Form Tambah Transaksi
     */
    public function create()
    {
        $user = auth()->user();
        
        // Helper untuk mengambil akun
        $coaQuery = FinanceCoa::where('is_active', true)
            ->where(function($q) use ($user) {
                $q->whereNull('organization_unit_id'); // Akun Global
                if ($user->organization_unit_id) {
                    $q->orWhere('organization_unit_id', $user->organization_unit_id); // Akun Unit
                }
            })
            ->orderBy('code');

        return Inertia::render('App/Finance/Transactions/Form', [
            'transaction' => null, // Mode Create
            'cashAccounts' => (clone $coaQuery)->where('is_cash', true)->get(),
            'revenueAccounts' => (clone $coaQuery)->where('type', 'REVENUE')->get(),
            'expenseAccounts' => (clone $coaQuery)->where('type', 'EXPENSE')->get(),
            'fundTypes' => [
                ['value' => 'UNRESTRICTED', 'label' => 'Dana Bebas / Operasional'],
                ['value' => 'RESTRICTED', 'label' => 'Dana Terikat (Zakat/Donasi Khusus)'],
                ['value' => 'ENDOWMENT', 'label' => 'Dana Abadi / Wakaf'],
            ]
        ]);
    }

    /**
     * Simpan Transaksi Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:INCOME,EXPENSE,TRANSFER',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'cash_coa_id' => 'required|exists:finance_coas,id',
            'category_coa_id' => 'nullable|required_unless:type,TRANSFER|exists:finance_coas,id',
            'destination_coa_id' => 'nullable|required_if:type,TRANSFER|exists:finance_coas,id',
            'description' => 'required|string|max:255',
            'proof' => 'nullable|image|max:3072', // Max 3MB
            'fund_type' => 'required|in:UNRESTRICTED,RESTRICTED,ENDOWMENT',
        ]);

        $user = auth()->user();
        $unitId = $user->organization_unit_id;

        // 1. CEK TUTUP BUKU
        if ($this->isPeriodClosed($request->date, $unitId)) {
            return back()->with('error', 'GAGAL: Periode bulan tersebut sudah Tutup Buku.');
        }

        DB::beginTransaction();
        try {
            $proofPath = null;
            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('uploads/finance', 'public');
            }

            // 2. Buat Header Jurnal
            $journal = FinanceJournal::create([
                'organization_unit_id' => $unitId,
                'user_id' => $user->id,
                'journal_number' => 'JV/' . date('Ymd') . '/' . rand(1000,9999),
                'transaction_date' => $request->date,
                'description' => $request->description,
                'status' => 'POSTED',
                'total_amount' => $request->amount
            ]);

            // 3. Logic Double Entry (Jurnal Details)
            $fundType = $request->fund_type;

            if ($request->type === 'INCOME') {
                $this->createJournalEntry($journal->id, $request->cash_coa_id, $request->amount, 0, $fundType);
                $this->createJournalEntry($journal->id, $request->category_coa_id, 0, $request->amount, $fundType);
            } 
            elseif ($request->type === 'EXPENSE') {
                $this->createJournalEntry($journal->id, $request->category_coa_id, $request->amount, 0, $fundType);
                $this->createJournalEntry($journal->id, $request->cash_coa_id, 0, $request->amount, $fundType);
            }
            elseif ($request->type === 'TRANSFER') {
                $this->createJournalEntry($journal->id, $request->destination_coa_id, $request->amount, 0, $fundType);
                $this->createJournalEntry($journal->id, $request->cash_coa_id, 0, $request->amount, $fundType);
            }

            // 4. Buat Record Transaksi UI
            FinanceTransaction::create([
                'organization_unit_id' => $unitId,
                'user_id' => $user->id,
                'journal_id' => $journal->id,
                'type' => $request->type,
                'date' => $request->date,
                'cash_coa_id' => $request->cash_coa_id,
                'category_coa_id' => $request->type === 'TRANSFER' ? $request->destination_coa_id : $request->category_coa_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'proof_path' => $proofPath,
                'fund_type' => $fundType,
            ]);

            DB::commit();
            return redirect()->route('finance.transactions.index')->with('success', 'Transaksi berhasil dicatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($proofPath) Storage::disk('public')->delete($proofPath);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Form Edit Transaksi
     */
    public function edit(FinanceTransaction $transaction)
    {
        $user = auth()->user();

        // Security Check
        if ($user->role !== 'super_admin' && $transaction->organization_unit_id !== $user->organization_unit_id) {
            abort(403);
        }
        if ($this->isPeriodClosed($transaction->organization_unit_id, $transaction->date)) {
            return back()->with('error', 'Transaksi ini berada di periode yang sudah ditutup. Tidak bisa diedit.');
        }

        $coaQuery = FinanceCoa::where('is_active', true)
            ->where(function($q) use ($user) {
                $q->whereNull('organization_unit_id')
                  ->orWhere('organization_unit_id', $user->organization_unit_id);
            })
            ->orderBy('code');

        return Inertia::render('App/Finance/Transactions/Form', [
            'transaction' => $transaction, // Mode Edit
            'cashAccounts' => (clone $coaQuery)->where('is_cash', true)->get(),
            'revenueAccounts' => (clone $coaQuery)->where('type', 'REVENUE')->get(),
            'expenseAccounts' => (clone $coaQuery)->where('type', 'EXPENSE')->get(),
            'fundTypes' => [
                ['value' => 'UNRESTRICTED', 'label' => 'Dana Bebas / Operasional'],
                ['value' => 'RESTRICTED', 'label' => 'Dana Terikat (Zakat/Donasi Khusus)'],
                ['value' => 'ENDOWMENT', 'label' => 'Dana Abadi / Wakaf'],
            ]
        ]);
    }

    /**
     * Update Transaksi
     */
    public function update(Request $request, FinanceTransaction $transaction)
    {
        $request->validate([
            'type' => 'required|in:INCOME,EXPENSE,TRANSFER',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'cash_coa_id' => 'required|exists:finance_coas,id',
            'category_coa_id' => 'nullable|required_unless:type,TRANSFER|exists:finance_coas,id',
            'destination_coa_id' => 'nullable|required_if:type,TRANSFER|exists:finance_coas,id',
            'description' => 'required|string|max:255',
            'proof' => 'nullable|image|max:3072',
            'fund_type' => 'required|in:UNRESTRICTED,RESTRICTED,ENDOWMENT',
        ]);

        // Security Check: Tutup Buku (Cek tanggal lama DAN tanggal baru)
        if ($this->isPeriodClosed($transaction->organization_unit_id, $transaction->date)) {
            return back()->with('error', 'Periode transaksi asli sudah ditutup.');
        }
        if ($this->isPeriodClosed($transaction->organization_unit_id, $request->date)) {
            return back()->with('error', 'Tanggal baru berada di periode yang sudah ditutup.');
        }

        DB::beginTransaction();
        try {
            // 1. Handle File Upload
            $proofPath = $transaction->proof_path;
            if ($request->hasFile('proof')) {
                if ($transaction->proof_path) Storage::disk('public')->delete($transaction->proof_path);
                $proofPath = $request->file('proof')->store('uploads/finance', 'public');
            }

            // 2. Update Header Jurnal
            $journal = $transaction->journal;
            $journal->update([
                'transaction_date' => $request->date,
                'description' => $request->description,
                'total_amount' => $request->amount
            ]);

            // 3. Reset Detail Jurnal (Hapus & Buat Baru agar bersih)
            $journal->details()->delete();

            // 4. Buat Ulang Detail Jurnal
            $fundType = $request->fund_type;
            if ($request->type === 'INCOME') {
                $this->createJournalEntry($journal->id, $request->cash_coa_id, $request->amount, 0, $fundType);
                $this->createJournalEntry($journal->id, $request->category_coa_id, 0, $request->amount, $fundType);
            } 
            elseif ($request->type === 'EXPENSE') {
                $this->createJournalEntry($journal->id, $request->category_coa_id, $request->amount, 0, $fundType);
                $this->createJournalEntry($journal->id, $request->cash_coa_id, 0, $request->amount, $fundType);
            }
            elseif ($request->type === 'TRANSFER') {
                $this->createJournalEntry($journal->id, $request->destination_coa_id, $request->amount, 0, $fundType);
                $this->createJournalEntry($journal->id, $request->cash_coa_id, 0, $request->amount, $fundType);
            }

            // 5. Update Transaksi UI
            $transaction->update([
                'type' => $request->type,
                'date' => $request->date,
                'cash_coa_id' => $request->cash_coa_id,
                'category_coa_id' => $request->type === 'TRANSFER' ? $request->destination_coa_id : $request->category_coa_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'proof_path' => $proofPath,
                'fund_type' => $fundType,
            ]);

            DB::commit();
            return redirect()->route('finance.transactions.index')->with('success', 'Transaksi berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy(FinanceTransaction $transaction)
    {
        $user = auth()->user();

        if ($user->role !== 'super_admin' && $transaction->organization_unit_id !== $user->organization_unit_id) abort(403);
        
        if ($this->isPeriodClosed($transaction->organization_unit_id, $transaction->date)) {
            return back()->with('error', 'Gagal: Transaksi ini berada dalam periode yang sudah Tutup Buku.');
        }

        DB::beginTransaction();
        try {
            if ($transaction->proof_path) Storage::disk('public')->delete($transaction->proof_path);

            if ($transaction->journal) $transaction->journal->delete(); // Cascade details
            else $transaction->delete();

            DB::commit();
            return back()->with('success', 'Transaksi dibatalkan/dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // --- HELPER ---

    private function createJournalEntry($journalId, $coaId, $debit, $credit, $fundType)
    {
        FinanceJournalDetail::create([
            'journal_id' => $journalId,
            'coa_id' => $coaId,
            'debit' => $debit,
            'credit' => $credit,
            'fund_type' => $fundType
        ]);
    }

    private function isPeriodClosed($date, $unitId)
    {
        $transDate = Carbon::parse($date);
        
        return FinanceClosingPeriod::where('organization_unit_id', $unitId)
            ->where('year', $transDate->year)
            ->where('month', $transDate->month)
            ->where('is_closed', true)
            ->exists();
    }

    private function calculateCashBalances($user)
    {
        $unitId = $user->organization_unit_id;
        $query = FinanceCoa::where('is_cash', true)->withSum(['journalDetails as balance' => function($q) use ($unitId) {
            if ($unitId) {
                $q->whereHas('journal', fn($j) => $j->where('organization_unit_id', $unitId));
            }
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