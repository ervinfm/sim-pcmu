<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceBudget;
use App\Models\FinanceCoa;
use App\Models\FinanceJournalDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    /**
     * Menampilkan Daftar Anggaran & Realisasi (RAPB)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $fiscalYear = $request->input('year', date('Y'));

        // Helper function untuk kalkulasi (bisa dipindah ke Service/Trait)
        $calcRealization = function($budget) use ($user, $fiscalYear) {
            $query = \App\Models\FinanceJournalDetail::where('coa_id', $budget->coa_id)
                ->whereHas('journal', fn($q) => $q->where('organization_unit_id', $user->organization_unit_id)
                                                ->whereYear('transaction_date', $fiscalYear)
                                                ->where('status', 'POSTED'));
                                                
            $agg = $query->selectRaw('SUM(debit) as deb, SUM(credit) as cred')->first();
            // Rumus Realisasi: Pendapatan (Kredit - Debit), Beban (Debit - Kredit)
            $isRevenue = in_array($budget->coa->type, ['REVENUE']);
            $realization = $isRevenue ? ($agg->cred - $agg->deb) : ($agg->deb - $agg->cred);
            
            $budget->realization = $realization;
            $budget->remaining = $budget->amount - $realization;
            $budget->percentage = $budget->amount > 0 ? ($realization / $budget->amount) * 100 : 0;
            return $budget;
        };

        // Ambil Data & Kelompokkan
        $allBudgets = \App\Models\FinanceBudget::with('coa')
            ->where('organization_unit_id', $user->organization_unit_id)
            ->where('fiscal_year', $fiscalYear)
            ->get()
            ->map($calcRealization);

        return Inertia::render('App/Finance/Budgets/Index', [
            'incomes' => $allBudgets->filter(fn($b) => $b->coa->type === 'REVENUE')->values(),
            'expenses' => $allBudgets->filter(fn($b) => $b->coa->type === 'EXPENSE')->values(),
            'accounts' => \App\Models\FinanceCoa::whereIn('type', ['REVENUE', 'EXPENSE'])->orderBy('code')->get(), // Untuk Dropdown Modal
            'filters' => ['year' => $fiscalYear],
            'summary' => [
                'total_income_plan' => $allBudgets->where('coa.type', 'REVENUE')->sum('amount'),
                'total_income_real' => $allBudgets->where('coa.type', 'REVENUE')->sum('realization'),
                'total_expense_plan' => $allBudgets->where('coa.type', 'EXPENSE')->sum('amount'),
                'total_expense_real' => $allBudgets->where('coa.type', 'EXPENSE')->sum('realization'),
            ]
        ]);
    }

    /**
     * Simpan / Update Anggaran
     */
    public function store(Request $request)
    {
        $request->validate([
            'coa_id' => 'required|exists:finance_coas,id',
            'fiscal_year' => 'required|integer|min:2020|max:2030',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $unitId = auth()->user()->organization_unit_id;

        // Gunakan updateOrCreate untuk mencegah duplikasi (Upsert)
        // Kuncinya adalah: Unit + Akun + Tahun
        FinanceBudget::updateOrCreate(
            [
                'organization_unit_id' => $unitId,
                'coa_id' => $request->coa_id,
                'fiscal_year' => $request->fiscal_year,
            ],
            [
                'amount' => $request->amount,
                'description' => $request->description
            ]
        );

        return back()->with('success', 'Anggaran berhasil disimpan.');
    }

    /**
     * Hapus Item Anggaran
     */
    public function destroy(FinanceBudget $budget)
    {
        // Proteksi: Pastikan user hanya menghapus anggaran unitnya sendiri
        if (auth()->user()->role !== 'super_admin' && $budget->organization_unit_id !== auth()->user()->organization_unit_id) {
            abort(403);
        }

        $budget->delete();

        return back()->with('success', 'Item anggaran dihapus.');
    }
}