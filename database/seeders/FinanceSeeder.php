<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\OrganizationUnit;
use App\Models\FinanceCoa;
use App\Models\FinanceJournal;
use App\Models\FinanceJournalDetail;
use App\Models\FinanceTransaction;
use Carbon\Carbon;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT BAGAN AKUN STANDAR (GLOBAL COA)
        // Akun ini milik Pusat (NULL unit_id) dan otomatis diwariskan ke semua unit
        $coas = $this->createStandardCOA();

        // 2. SIMULASI TRANSAKSI (Hanya jika ada Unit & User)
        $unit = OrganizationUnit::first();
        $user = User::first(); 

        if ($unit && $user) {
            $this->command->info('Seeding transaksi dummy untuk Unit: ' . $unit->name);
            
            // A. Setup Saldo Awal (PENTING: Fitur Baru)
            $this->createOpeningBalance($unit, $user, $coas);

            // B. Transaksi Harian (Pemasukan & Pengeluaran)
            $this->createDailyTransactions($unit, $user, $coas);
        }
    }

    /**
     * Membuat Struktur Hierarki COA
     */
    private function createStandardCOA()
    {
        $accounts = [];

        // --- LEVEL 1: ROOT ---
        $asset = FinanceCoa::create(['code' => '1-0000', 'name' => 'ASET', 'type' => 'ASSET', 'organization_unit_id' => null]);
        $liab  = FinanceCoa::create(['code' => '2-0000', 'name' => 'KEWAJIBAN', 'type' => 'LIABILITY', 'organization_unit_id' => null]);
        $equity= FinanceCoa::create(['code' => '3-0000', 'name' => 'EKUITAS / MODAL', 'type' => 'EQUITY', 'organization_unit_id' => null]);
        $rev   = FinanceCoa::create(['code' => '4-0000', 'name' => 'PENERIMAAN', 'type' => 'REVENUE', 'organization_unit_id' => null]);
        $exp   = FinanceCoa::create(['code' => '5-0000', 'name' => 'PENGELUARAN', 'type' => 'EXPENSE', 'organization_unit_id' => null]);

        // --- LEVEL 2 & 3: DETAILS ---

        // 1. ASET -> KAS & BANK
        $kasBank = FinanceCoa::create(['parent_id' => $asset->id, 'code' => '1-1000', 'name' => 'Kas & Bank', 'type' => 'ASSET', 'organization_unit_id' => null]);
        
        $accounts['kas_tunai'] = FinanceCoa::create([
            'parent_id' => $kasBank->id, 'code' => '1-1100', 'name' => 'Kas Tunai Operasional', 
            'type' => 'ASSET', 'is_cash' => true, 'organization_unit_id' => null
        ]);
        
        $accounts['bank_bsi'] = FinanceCoa::create([
            'parent_id' => $kasBank->id, 'code' => '1-1200', 'name' => 'Bank BSI', 
            'type' => 'ASSET', 'is_cash' => true, 'organization_unit_id' => null
        ]);

        // 3. EKUITAS (PENTING UNTUK SALDO AWAL)
        $accounts['modal_awal'] = FinanceCoa::create([
            'parent_id' => $equity->id, 'code' => '3-1000', 'name' => 'Ekuitas Saldo Awal', 
            'type' => 'EQUITY', 'organization_unit_id' => null
        ]);

        // 4. PENERIMAAN
        $zis = FinanceCoa::create(['parent_id' => $rev->id, 'code' => '4-1000', 'name' => 'Penerimaan ZIS', 'type' => 'REVENUE', 'organization_unit_id' => null]);
        $accounts['infaq'] = FinanceCoa::create([
            'parent_id' => $zis->id, 'code' => '4-1100', 'name' => 'Infaq Jumat', 
            'type' => 'REVENUE', 'organization_unit_id' => null
        ]);

        // 5. PENGELUARAN
        $ops = FinanceCoa::create(['parent_id' => $exp->id, 'code' => '5-1000', 'name' => 'Beban Operasional', 'type' => 'EXPENSE', 'organization_unit_id' => null]);
        $accounts['listrik'] = FinanceCoa::create([
            'parent_id' => $ops->id, 'code' => '5-1100', 'name' => 'Listrik & Air', 
            'type' => 'EXPENSE', 'organization_unit_id' => null
        ]);
        $accounts['konsumsi'] = FinanceCoa::create([
            'parent_id' => $ops->id, 'code' => '5-1200', 'name' => 'Konsumsi Rapat', 
            'type' => 'EXPENSE', 'organization_unit_id' => null
        ]);

        return $accounts;
    }

    /**
     * Simulasi Saldo Awal (Migrasi Sistem)
     */
    private function createOpeningBalance($unit, $user, $coas)
    {
        $date = Carbon::now()->subMonths(1)->startOfMonth(); // 1 Bulan lalu
        $amount = 15000000; // 15 Juta

        // 1. Header Jurnal
        $journal = FinanceJournal::create([
            'organization_unit_id' => $unit->id,
            'user_id' => $user->id,
            'journal_number' => 'OPB/SEED/' . rand(1000,9999),
            'transaction_date' => $date,
            'description' => 'Saldo Awal Migrasi Sistem',
            'total_amount' => $amount
        ]);

        // 2. Transaksi UI (Flag is_opening_balance = true)
        FinanceTransaction::create([
            'organization_unit_id' => $unit->id,
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'type' => 'INCOME',
            'date' => $date,
            'cash_coa_id' => $coas['bank_bsi']->id, // Uang ada di Bank
            'category_coa_id' => $coas['modal_awal']->id, // Lawannya Modal
            'amount' => $amount,
            'description' => 'Saldo Awal Migrasi Sistem',
            'is_opening_balance' => true, // <--- FITUR BARU
        ]);

        // 3. Jurnal Detail (Debit: Bank, Kredit: Modal)
        FinanceJournalDetail::create([
            'journal_id' => $journal->id,
            'coa_id' => $coas['bank_bsi']->id,
            'debit' => $amount,
            'credit' => 0,
        ]);
        FinanceJournalDetail::create([
            'journal_id' => $journal->id,
            'coa_id' => $coas['modal_awal']->id,
            'debit' => 0,
            'credit' => $amount,
        ]);
    }

    /**
     * Simulasi Transaksi Harian
     */
    private function createDailyTransactions($unit, $user, $coas)
    {
        // KASUS 1: Terima Infaq (Income)
        $this->recordTransaction(
            $unit, $user, 'INCOME', 500000, 
            $coas['kas_tunai']->id, $coas['infaq']->id, 
            'Penerimaan Kotak Infaq Jumat'
        );

        // KASUS 2: Bayar Listrik (Expense)
        $this->recordTransaction(
            $unit, $user, 'EXPENSE', 150000, 
            $coas['kas_tunai']->id, $coas['listrik']->id, 
            'Bayar Token Listrik Kantor'
        );
    }

    /**
     * Helper untuk record transaksi lengkap (Trx + Jurnal + Details)
     */
    private function recordTransaction($unit, $user, $type, $amount, $cashId, $categoryId, $desc)
    {
        // 1. Jurnal
        $journal = FinanceJournal::create([
            'organization_unit_id' => $unit->id,
            'user_id' => $user->id,
            'journal_number' => 'JV/' . date('Ymd') . '/' . rand(100,999),
            'transaction_date' => Carbon::now(),
            'description' => $desc,
            'total_amount' => $amount
        ]);

        // 2. Transaksi UI
        FinanceTransaction::create([
            'organization_unit_id' => $unit->id,
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'type' => $type,
            'date' => Carbon::now(),
            'cash_coa_id' => $cashId,
            'category_coa_id' => $categoryId,
            'amount' => $amount,
            'description' => $desc,
            'is_opening_balance' => false,
        ]);

        // 3. Detail Akuntansi
        if ($type === 'INCOME') {
            // Debit: Kas, Kredit: Pendapatan
            FinanceJournalDetail::create(['journal_id' => $journal->id, 'coa_id' => $cashId, 'debit' => $amount, 'credit' => 0]);
            FinanceJournalDetail::create(['journal_id' => $journal->id, 'coa_id' => $categoryId, 'debit' => 0, 'credit' => $amount]);
        } else {
            // Debit: Beban, Kredit: Kas
            FinanceJournalDetail::create(['journal_id' => $journal->id, 'coa_id' => $categoryId, 'debit' => $amount, 'credit' => 0]);
            FinanceJournalDetail::create(['journal_id' => $journal->id, 'coa_id' => $cashId, 'debit' => 0, 'credit' => $amount]);
        }
    }
}