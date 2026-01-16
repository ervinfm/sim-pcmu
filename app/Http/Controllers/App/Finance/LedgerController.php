<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceCoa;
use App\Models\FinanceJournalDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LedgerController extends Controller
{
    /**
     * MENAMPILKAN BUKU BESAR (GENERAL LEDGER)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $unitId = $user->organization_unit_id;

        // Filter Input
        $coaId = $request->input('coa_id');
        $startDate = $request->input('start_date', date('Y-m-01')); // Default tgl 1 bulan ini
        $endDate = $request->input('end_date', date('Y-m-d'));
        $fundType = $request->input('fund_type'); 

        // 1. Ambil List Akun untuk Dropdown
        $accounts = FinanceCoa::where('is_active', true)
            ->where(function($q) use ($unitId) {
                $q->whereNull('organization_unit_id')->orWhere('organization_unit_id', $unitId);
            })
            ->orderBy('code')
            ->select('id', 'code', 'name')
            ->get();

        $ledger = [];
        $openingBalance = 0;
        $selectedAccount = null;

        // 2. Logic Buku Besar
        if ($coaId) {
            $selectedAccount = FinanceCoa::find($coaId);

            // A. Hitung Saldo Awal (Transaksi SEBELUM start_date)
            // Rumus: Total Debit - Total Kredit (Filter status POSTED)
            $prevDebit = FinanceJournalDetail::where('coa_id', $coaId)
                ->whereHas('journal', fn($q) => $q->where('transaction_date', '<', $startDate)
                                                 ->where('status', 'POSTED') 
                                                 ->when($unitId, fn($sq) => $sq->where('organization_unit_id', $unitId)))
                ->sum('debit');

            $prevCredit = FinanceJournalDetail::where('coa_id', $coaId)
                ->whereHas('journal', fn($q) => $q->where('transaction_date', '<', $startDate)
                                                 ->where('status', 'POSTED')
                                                 ->when($unitId, fn($sq) => $sq->where('organization_unit_id', $unitId)))
                ->sum('credit');
            
            // Tentukan posisi saldo normal
            $isDebitNormal = in_array($selectedAccount->type, ['ASSET', 'EXPENSE']);
            $openingBalance = $isDebitNormal ? ($prevDebit - $prevCredit) : ($prevCredit - $prevDebit);

            // B. Ambil Transaksi Periode Ini
            $ledger = FinanceJournalDetail::with(['journal'])
                ->where('coa_id', $coaId)
                ->whereHas('journal', fn($q) => $q->whereBetween('transaction_date', [$startDate, $endDate])
                                                 ->where('status', 'POSTED')
                                                 ->when($unitId, fn($sq) => $sq->where('organization_unit_id', $unitId)))
                ->when($fundType, fn($q) => $q->where('fund_type', $fundType))
                ->get()
                ->sortBy(function($detail) {
                    return $detail->journal->transaction_date . $detail->created_at;
                })
                ->values();
        }

        return Inertia::render('App/Finance/Ledger/Index', [ // Perhatikan Path View-nya juga kita rapikan
            'accounts' => $accounts,
            'ledger' => $ledger,
            'openingBalance' => $openingBalance,
            'selectedAccount' => $selectedAccount,
            'filters' => $request->only(['coa_id', 'start_date', 'end_date', 'fund_type'])
        ]);
    }
}