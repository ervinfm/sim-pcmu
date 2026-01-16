<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\OrganizationUnit;
use App\Models\FinanceCoa;
use App\Models\FinanceJournal;
use App\Models\FinanceJournalDetail;
use App\Models\FinanceTransaction;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT COA STANDAR (Format X.XX.XX.XXX)
        $coas = $this->createStandardCOA();

        // 2. SIMULASI TRANSAKSI
        $unit = OrganizationUnit::first();
        $user = User::first(); 

        if ($unit && $user) {
            $this->command->info('Seeding transaksi dummy untuk Unit: ' . $unit->name);
            
            // Ambil ID akun penting untuk transaksi
            // Note: Kita harus mencari ID berdasarkan kode baru
            $cashId = FinanceCoa::where('code', '1.10.02.001')->first()->id; // BSI
            $equityId = FinanceCoa::where('code', '3.10.01.001')->first()->id; // Saldo Awal
            
            // A. Saldo Awal
            $this->createOpeningBalance($unit, $user, $cashId, $equityId);

            // B. Transaksi Harian
            $this->createDailyTransactions($unit, $user);
        }
    }

    private function createStandardCOA()
    {
        // Helper function untuk create akun
        $create = function($code, $name, $type, $parentId = null, $isCash = false) {
            return FinanceCoa::create([
                'organization_unit_id' => null, // Global / Pusat
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'parent_id' => $parentId,
                'is_cash' => $isCash,
                'is_active' => true
            ]);
        };

        // --- LEVEL 1: ROOT ---
        $a = $create('1', 'ASET', 'ASSET');
        $l = $create('2', 'KEWAJIBAN', 'LIABILITY');
        $e = $create('3', 'ASET NETO / EKUITAS', 'EQUITY');
        $r = $create('4', 'PENERIMAAN', 'REVENUE');
        $x = $create('5', 'PENGELUARAN', 'EXPENSE');

        // --- LEVEL 2 & 3 & 4 (HIERARKI) ---

        // 1. ASET
        // 1.10 Aset Lancar
        $a1 = $create('1.10', 'Aset Lancar', 'ASSET', $a->id);
            // 1.10.01 Kas Tunai
            $a1_1 = $create('1.10.01', 'Kas Tunai', 'ASSET', $a1->id, true);
                $create('1.10.01.001', 'Kas Operasional Kantor', 'ASSET', $a1_1->id, true);
                $create('1.10.01.002', 'Kas Kecil', 'ASSET', $a1_1->id, true);
            // 1.10.02 Bank
            $a1_2 = $create('1.10.02', 'Bank & Setara Kas', 'ASSET', $a1->id, true);
                $create('1.10.02.001', 'Bank BSI - Operasional', 'ASSET', $a1_2->id, true);
                $create('1.10.02.002', 'Bank Muamalat - Pembangunan', 'ASSET', $a1_2->id, true);
            // 1.10.03 Piutang
            $a1_3 = $create('1.10.03', 'Piutang & Uang Muka', 'ASSET', $a1->id);
                $create('1.10.03.001', 'Piutang Anggota/Staf', 'ASSET', $a1_3->id);
        
        // 1.20 Aset Tetap
        $a2 = $create('1.20', 'Aset Tetap (Inventaris)', 'ASSET', $a->id);
            $create('1.20.01', 'Tanah & Bangunan', 'ASSET', $a2->id);
            $create('1.20.02', 'Kendaraan', 'ASSET', $a2->id);
            $create('1.20.03', 'Peralatan & Mesin', 'ASSET', $a2->id);

        // 2. KEWAJIBAN
        // 2.10 Kewajiban Pendek
        $l1 = $create('2.10', 'Kewajiban Jangka Pendek', 'LIABILITY', $l->id);
            $create('2.10.01', 'Utang Usaha / Kegiatan', 'LIABILITY', $l1->id);
            // 2.10.02 Titipan Dana (Penting untuk Pusat)
            $l1_2 = $create('2.10.02', 'Titipan Dana Unit (RAK)', 'LIABILITY', $l1->id);
                $create('2.10.02.001', 'Titipan Dana PRM', 'LIABILITY', $l1_2->id);
                $create('2.10.02.002', 'Titipan Dana AUM', 'LIABILITY', $l1_2->id);

        // 3. EKUITAS
        // 3.10 Tidak Terikat
        $e1 = $create('3.10', 'Aset Neto Tidak Terikat', 'EQUITY', $e->id);
            $create('3.10.01.001', 'Saldo Awal', 'EQUITY', $create('3.10.01', 'Saldo Awal Organisasi', 'EQUITY', $e1->id)->id);
            $create('3.10.02.001', 'Surplus/Defisit Periode Berjalan', 'EQUITY', $create('3.10.02', 'Surplus/Defisit', 'EQUITY', $e1->id)->id);

        // 4. PENERIMAAN
        // 4.10 Penerimaan Organisasi
        $r1 = $create('4.10', 'Penerimaan Organisasi', 'REVENUE', $r->id);
            $create('4.10.01.001', 'Iuran Anggota / SWP', 'REVENUE', $create('4.10.01', 'Iuran & Sumbangan Wajib', 'REVENUE', $r1->id)->id);
            $create('4.10.02.001', 'Infak Tidak Terikat', 'REVENUE', $create('4.10.02', 'Infak & Sedekah Umum', 'REVENUE', $r1->id)->id);

        // 5. PENGELUARAN
        // 5.10 Beban Operasional
        $x1 = $create('5.10', 'Beban Operasional', 'EXPENSE', $x->id);
            $x1_1 = $create('5.10.01', 'Beban Kantor', 'EXPENSE', $x1->id);
                $create('5.10.01.001', 'Listrik, Air & Internet', 'EXPENSE', $x1_1->id);
                $create('5.10.01.002', 'ATK & Cetak', 'EXPENSE', $x1_1->id);
                $create('5.10.01.003', 'Konsumsi Rapat', 'EXPENSE', $x1_1->id);
    }

    private function createOpeningBalance($unit, $user, $cashId, $equityId)
    {
        $amount = 15000000;
        $journal = FinanceJournal::create([
            'organization_unit_id' => $unit->id, 'user_id' => $user->id,
            'journal_number' => 'JV/OPENING/2026', 'transaction_date' => '2026-01-01',
            'description' => 'Saldo Awal 2026', 'total_amount' => $amount, 'status' => 'POSTED'
        ]);

        FinanceTransaction::create([
            'organization_unit_id' => $unit->id, 'user_id' => $user->id, 'journal_id' => $journal->id,
            'type' => 'INCOME', 'date' => '2026-01-01', 'cash_coa_id' => $cashId, 'category_coa_id' => $equityId,
            'amount' => $amount, 'description' => 'Saldo Awal Tahun Buku 2026',
            'is_opening_balance' => true, 'fund_type' => 'UNRESTRICTED'
        ]);

        FinanceJournalDetail::create(['journal_id' => $journal->id, 'coa_id' => $cashId, 'debit' => $amount, 'credit' => 0, 'fund_type' => 'UNRESTRICTED']);
        FinanceJournalDetail::create(['journal_id' => $journal->id, 'coa_id' => $equityId, 'debit' => 0, 'credit' => $amount, 'fund_type' => 'UNRESTRICTED']);
    }

    private function createDailyTransactions($unit, $user)
    {
        // Ambil ID Akun secara dinamis berdasarkan kode baru
        $kasTunai = FinanceCoa::where('code', '1.10.01.001')->first()->id;
        $pendapatanInfak = FinanceCoa::where('code', '4.10.02.001')->first()->id;
        $bebanListrik = FinanceCoa::where('code', '5.10.01.001')->first()->id;

        $transactions = [
            ['date' => '2026-01-05', 'type' => 'INCOME', 'desc' => 'Infak Jumat', 'amount' => 2500000, 'cash' => $kasTunai, 'cat' => $pendapatanInfak],
            ['date' => '2026-01-07', 'type' => 'EXPENSE', 'desc' => 'Bayar Token Listrik', 'amount' => 500000, 'cash' => $kasTunai, 'cat' => $bebanListrik],
        ];

        foreach ($transactions as $trx) {
            $journal = FinanceJournal::create([
                'organization_unit_id' => $unit->id, 'user_id' => $user->id,
                'journal_number' => 'JV/'.rand(100,999), 'transaction_date' => $trx['date'],
                'description' => $trx['desc'], 'total_amount' => $trx['amount'], 'status' => 'POSTED'
            ]);

            FinanceTransaction::create([
                'organization_unit_id' => $unit->id, 'user_id' => $user->id, 'journal_id' => $journal->id,
                'type' => $trx['type'], 'date' => $trx['date'], 'cash_coa_id' => $trx['cash'], 'category_coa_id' => $trx['cat'],
                'amount' => $trx['amount'], 'description' => $trx['desc'], 'fund_type' => 'UNRESTRICTED'
            ]);

            if ($trx['type'] === 'INCOME') {
                FinanceJournalDetail::create(['journal_id' => $journal->id, 'coa_id' => $trx['cash'], 'debit' => $trx['amount'], 'credit' => 0]);
                FinanceJournalDetail::create(['journal_id' => $journal->id, 'coa_id' => $trx['cat'], 'debit' => 0, 'credit' => $trx['amount']]);
            } else {
                FinanceJournalDetail::create(['journal_id' => $journal->id, 'coa_id' => $trx['cat'], 'debit' => $trx['amount'], 'credit' => 0]);
                FinanceJournalDetail::create(['journal_id' => $journal->id, 'coa_id' => $trx['cash'], 'debit' => 0, 'credit' => $trx['amount']]);
            }
        }
    }
}