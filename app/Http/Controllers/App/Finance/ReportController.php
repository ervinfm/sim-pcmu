<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceCoa;
use App\Models\FinanceJournalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('App/Finance/Reports/Index');
    }

    // Laporan Neraca (Balance Sheet)
    public function balanceSheet(Request $request)
    {
        $unitId = auth()->user()->organization_unit_id;
        $endDate = $request->input('end_date', date('Y-m-d'));

        // Ambil Akun ASSET, LIABILITY, EQUITY beserta saldonya
        $accounts = FinanceCoa::whereIn('type', ['ASSET', 'LIABILITY', 'EQUITY'])
            ->where(function($q) use ($unitId) {
                $q->whereNull('organization_unit_id')->orWhere('organization_unit_id', $unitId);
            })
            ->withSum(['journalDetails as debit_total' => function($q) use ($unitId, $endDate) {
                $q->whereHas('journal', fn($j) => $j->where('transaction_date', '<=', $endDate)
                   ->when($unitId, fn($sq) => $sq->where('organization_unit_id', $unitId)));
            }], 'debit')
            ->withSum(['journalDetails as credit_total' => function($q) use ($unitId, $endDate) {
                $q->whereHas('journal', fn($j) => $j->where('transaction_date', '<=', $endDate)
                   ->when($unitId, fn($sq) => $sq->where('organization_unit_id', $unitId)));
            }], 'credit')
            ->get()
            ->map(function($coa) {
                // Hitung Saldo Normal
                if ($coa->type === 'ASSET') {
                    $coa->balance = ($coa->debit_total ?? 0) - ($coa->credit_total ?? 0);
                } else {
                    // Liability & Equity saldo normal di Kredit
                    $coa->balance = ($coa->credit_total ?? 0) - ($coa->debit_total ?? 0);
                }
                return $coa;
            });

        return Inertia::render('App/Finance/Reports/BalanceSheet', [
            'assets' => $accounts->where('type', 'ASSET')->values(),
            'liabilities' => $accounts->where('type', 'LIABILITY')->values(),
            'equities' => $accounts->where('type', 'EQUITY')->values(),
            'filterDate' => $endDate
        ]);
    }

    // Laporan Laba Rugi (Income Statement)
    public function incomeStatement(Request $request)
    {
        $unitId = auth()->user()->organization_unit_id;
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));

        // Ambil Akun REVENUE dan EXPENSE
        $accounts = FinanceCoa::whereIn('type', ['REVENUE', 'EXPENSE'])
            ->where(function($q) use ($unitId) {
                $q->whereNull('organization_unit_id')->orWhere('organization_unit_id', $unitId);
            })
            ->withSum(['journalDetails as debit_total' => function($q) use ($unitId, $startDate, $endDate) {
                $q->whereHas('journal', fn($j) => $j->whereBetween('transaction_date', [$startDate, $endDate])
                   ->when($unitId, fn($sq) => $sq->where('organization_unit_id', $unitId)));
            }], 'debit')
            ->withSum(['journalDetails as credit_total' => function($q) use ($unitId, $startDate, $endDate) {
                $q->whereHas('journal', fn($j) => $j->whereBetween('transaction_date', [$startDate, $endDate])
                   ->when($unitId, fn($sq) => $sq->where('organization_unit_id', $unitId)));
            }], 'credit')
            ->get()
            ->map(function($coa) {
                if ($coa->type === 'EXPENSE') {
                    $coa->balance = ($coa->debit_total ?? 0) - ($coa->credit_total ?? 0);
                } else {
                    // Revenue saldo normal di Kredit
                    $coa->balance = ($coa->credit_total ?? 0) - ($coa->debit_total ?? 0);
                }
                return $coa;
            });

        $revenues = $accounts->where('type', 'REVENUE')->values();
        $expenses = $accounts->where('type', 'EXPENSE')->values();

        return Inertia::render('App/Finance/Reports/IncomeStatement', [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'totalRevenue' => $revenues->sum('balance'),
            'totalExpense' => $expenses->sum('balance'),
            'filters' => ['start' => $startDate, 'end' => $endDate]
        ]);
    }
}