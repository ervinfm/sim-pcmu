<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceCoa;
use App\Models\FinanceTransaction;
use App\Models\FinanceJournal;
use App\Models\FinanceJournalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OpeningBalanceController extends Controller
{
    public function index()
    {
        // Cek apakah Unit ini sudah pernah input saldo awal
        $hasOpeningBalance = FinanceTransaction::where('organization_unit_id', auth()->user()->organization_unit_id)
            ->where('is_opening_balance', true)
            ->exists();

        return Inertia::render('App/Finance/OpeningBalance/Index', [
            'hasOpeningBalance' => $hasOpeningBalance
        ]);
    }

    public function create()
    {
        $unitId = auth()->user()->organization_unit_id;

        // Ambil Akun Kas/Bank (Untuk Debit)
        $cashAccounts = FinanceCoa::where('is_cash', true)
            ->where(function($q) use ($unitId) {
                $q->whereNull('organization_unit_id')->orWhere('organization_unit_id', $unitId);
            })->get();

        // Ambil Akun Modal/Ekuitas (Untuk Kredit/Penyeimbang)
        // Biasanya akun 3-XXX (Equity)
        $equityAccounts = FinanceCoa::where('type', 'EQUITY')
            ->where(function($q) use ($unitId) {
                $q->whereNull('organization_unit_id')->orWhere('organization_unit_id', $unitId);
            })->get();

        return Inertia::render('App/Finance/OpeningBalance/Form', [
            'cashAccounts' => $cashAccounts,
            'equityAccounts' => $equityAccounts
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.cash_coa_id' => 'required|exists:finance_coas,id',
            'items.*.amount' => 'required|numeric|min:0',
            'equity_coa_id' => 'required|exists:finance_coas,id', // Akun Penyeimbang
        ]);

        DB::transaction(function () use ($request) {
            $user = auth()->user();
            $unitId = $user->organization_unit_id;
            
            foreach ($request->items as $item) {
                if ($item['amount'] <= 0) continue;

                // 1. Buat Header Jurnal
                $journal = FinanceJournal::create([
                    'organization_unit_id' => $unitId,
                    'user_id' => $user->id,
                    'journal_number' => 'OPB/' . date('Ymd') . '/' . uniqid(),
                    'transaction_date' => $request->date,
                    'description' => 'Saldo Awal: ' . FinanceCoa::find($item['cash_coa_id'])->name,
                    'total_amount' => $item['amount'],
                ]);

                // 2. Buat Transaksi (Log UI)
                FinanceTransaction::create([
                    'organization_unit_id' => $unitId,
                    'user_id' => $user->id,
                    'journal_id' => $journal->id,
                    'type' => 'INCOME', // Dianggap Income agar menambah Kas
                    'date' => $request->date,
                    'cash_coa_id' => $item['cash_coa_id'],
                    'category_coa_id' => $request->equity_coa_id,
                    'amount' => $item['amount'],
                    'description' => 'Setup Saldo Awal',
                    'is_opening_balance' => true,
                ]);

                // 3. Buat Detail Jurnal (Debit Kas, Kredit Modal)
                // Debit: Kas Bertambah
                FinanceJournalDetail::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $item['cash_coa_id'],
                    'debit' => $item['amount'],
                    'credit' => 0,
                ]);

                // Kredit: Modal/Ekuitas Bertambah
                FinanceJournalDetail::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $request->equity_coa_id,
                    'debit' => 0,
                    'credit' => $item['amount'],
                ]);
            }
        });

        return redirect()->route('finance.opening-balances.index')->with('success', 'Saldo awal berhasil disetup.');
    }
}