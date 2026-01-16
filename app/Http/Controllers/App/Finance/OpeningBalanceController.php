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

        $cashAccounts = FinanceCoa::where('is_cash', true)
            ->where(function($q) use ($unitId) {
                $q->whereNull('organization_unit_id')->orWhere('organization_unit_id', $unitId);
            })->get();

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
            'equity_coa_id' => 'required|exists:finance_coas,id',
            'items' => 'required|array',
            'items.*.cash_coa_id' => 'required|exists:finance_coas,id',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $unitId = $user->organization_unit_id;

        DB::transaction(function () use ($request, $user, $unitId) {
            foreach ($request->items as $item) {
                if ($item['amount'] <= 0) continue;

                // 1. Header Jurnal
                $journal = FinanceJournal::create([
                    'organization_unit_id' => $unitId,
                    'user_id' => $user->id,
                    'journal_number' => 'JV/OPENING/' . date('Y'),
                    'transaction_date' => $request->date,
                    'description' => 'Saldo Awal Sistem',
                    'total_amount' => $item['amount'],
                    'status' => 'POSTED'
                ]);

                // 2. Transaksi UI
                FinanceTransaction::create([
                    'organization_unit_id' => $unitId,
                    'user_id' => $user->id,
                    'journal_id' => $journal->id,
                    'type' => 'INCOME',
                    'date' => $request->date,
                    'cash_coa_id' => $item['cash_coa_id'],
                    'category_coa_id' => $request->equity_coa_id,
                    'amount' => $item['amount'],
                    'description' => 'Setup Saldo Awal',
                    'is_opening_balance' => true,
                    'fund_type' => 'UNRESTRICTED', // [FIX] Default Dana Bebas
                ]);

                // 3. Debit Kas
                FinanceJournalDetail::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $item['cash_coa_id'],
                    'debit' => $item['amount'],
                    'credit' => 0,
                    'fund_type' => 'UNRESTRICTED' // [FIX]
                ]);

                // 4. Kredit Ekuitas
                FinanceJournalDetail::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $request->equity_coa_id,
                    'debit' => 0,
                    'credit' => $item['amount'],
                    'fund_type' => 'UNRESTRICTED' // [FIX]
                ]);
            }
        });

        return redirect()->route('finance.transactions.index')->with('success', 'Saldo awal berhasil disetup.');
    }
}