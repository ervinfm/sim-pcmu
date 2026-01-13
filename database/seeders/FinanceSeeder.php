<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\OrganizationUnit;
use Carbon\Carbon;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT BAGAN AKUN STANDAR (COA)
        // Kita buat akun Global (organization_unit_id = null) agar bisa dipakai semua unit
        $this->createStandardCOA();

        // 2. SIMULASI TRANSAKSI
        // Kita ambil satu unit sampel (misal: Unit Pertama yang ditemukan)
        $unit = OrganizationUnit::first();
        $user = User::first(); // Super Admin

        if ($unit && $user) {
            $this->createDummyTransactions($unit->id, $user->id);
        }
    }

    private function createStandardCOA()
    {
        $coas = [
            // --- 1. ASET (ASSET) ---
            [
                'code' => '1-0000', 'name' => 'ASET', 'type' => 'ASSET', 'children' => [
                    ['code' => '1-1000', 'name' => 'KAS & SETARA KAS', 'type' => 'ASSET', 'children' => [
                        ['code' => '1-1100', 'name' => 'Kas Tunai', 'type' => 'ASSET', 'is_cash' => true],
                        ['code' => '1-1200', 'name' => 'Bank BSI', 'type' => 'ASSET', 'is_cash' => true],
                        ['code' => '1-1300', 'name' => 'Bank Muamalat', 'type' => 'ASSET', 'is_cash' => true],
                    ]],
                    ['code' => '1-2000', 'name' => 'PIUTANG', 'type' => 'ASSET', 'children' => [
                        ['code' => '1-2100', 'name' => 'Piutang Pegawai', 'type' => 'ASSET'],
                    ]],
                    ['code' => '1-3000', 'name' => 'ASET TETAP', 'type' => 'ASSET', 'children' => [
                        ['code' => '1-3100', 'name' => 'Tanah & Bangunan', 'type' => 'ASSET'],
                        ['code' => '1-3200', 'name' => 'Kendaraan', 'type' => 'ASSET'],
                        ['code' => '1-3300', 'name' => 'Peralatan Kantor', 'type' => 'ASSET'],
                    ]]
                ]
            ],

            // --- 2. KEWAJIBAN (LIABILITY) ---
            [
                'code' => '2-0000', 'name' => 'KEWAJIBAN', 'type' => 'LIABILITY', 'children' => [
                    ['code' => '2-1000', 'name' => 'KEWAJIBAN JANGKA PENDEK', 'type' => 'LIABILITY', 'children' => [
                        ['code' => '2-1100', 'name' => 'Utang Usaha', 'type' => 'LIABILITY'],
                    ]]
                ]
            ],

            // --- 3. ASET NETO / EKUITAS (EQUITY) ---
            [
                'code' => '3-0000', 'name' => 'ASET NETO', 'type' => 'EQUITY', 'children' => [
                    ['code' => '3-1000', 'name' => 'ASET NETO TIDAK TERIKAT', 'type' => 'EQUITY', 'children' => [
                        ['code' => '3-1100', 'name' => 'Saldo Dana Lancar', 'type' => 'EQUITY'],
                    ]],
                    ['code' => '3-2000', 'name' => 'ASET NETO TERIKAT', 'type' => 'EQUITY', 'children' => [
                        ['code' => '3-2100', 'name' => 'Dana Pembangunan', 'type' => 'EQUITY'],
                    ]]
                ]
            ],

            // --- 4. PENERIMAAN (REVENUE) ---
            [
                'code' => '4-0000', 'name' => 'PENERIMAAN', 'type' => 'REVENUE', 'children' => [
                    ['code' => '4-1000', 'name' => 'PENERIMAAN TIDAK TERIKAT', 'type' => 'REVENUE', 'children' => [
                        ['code' => '4-1100', 'name' => 'Infaq Kotak Jumat', 'type' => 'REVENUE'],
                        ['code' => '4-1200', 'name' => 'Donasi Umum / Operasional', 'type' => 'REVENUE'],
                        ['code' => '4-1300', 'name' => 'Iuran Anggota', 'type' => 'REVENUE'],
                    ]],
                    ['code' => '4-2000', 'name' => 'PENERIMAAN TERIKAT', 'type' => 'REVENUE', 'children' => [
                        ['code' => '4-2100', 'name' => 'Infaq Yatim Piatu', 'type' => 'REVENUE'],
                        ['code' => '4-2200', 'name' => 'Wakaf Uang', 'type' => 'REVENUE'],
                        ['code' => '4-2300', 'name' => 'Zakat Maal', 'type' => 'REVENUE'],
                    ]]
                ]
            ],

            // --- 5. PENGELUARAN (EXPENSE) ---
            [
                'code' => '5-0000', 'name' => 'PENGELUARAN', 'type' => 'EXPENSE', 'children' => [
                    ['code' => '5-1000', 'name' => 'BEBAN OPERASIONAL', 'type' => 'EXPENSE', 'children' => [
                        ['code' => '5-1100', 'name' => 'Listrik, Air & Telepon', 'type' => 'EXPENSE'],
                        ['code' => '5-1200', 'name' => 'ATK & Keperluan Kantor', 'type' => 'EXPENSE'],
                        ['code' => '5-1300', 'name' => 'Konsumsi & Rapat', 'type' => 'EXPENSE'],
                        ['code' => '5-1400', 'name' => 'Transportasi', 'type' => 'EXPENSE'],
                    ]],
                    ['code' => '5-2000', 'name' => 'BEBAN SDM', 'type' => 'EXPENSE', 'children' => [
                        ['code' => '5-2100', 'name' => 'Insentif Mubaligh/Khatib', 'type' => 'EXPENSE'],
                        ['code' => '5-2200', 'name' => 'Gaji/Honorarium Staff', 'type' => 'EXPENSE'],
                        ['code' => '5-2300', 'name' => 'Insentif Marbot', 'type' => 'EXPENSE'],
                    ]],
                    ['code' => '5-3000', 'name' => 'PENYALURAN PROGRAM', 'type' => 'EXPENSE', 'children' => [
                        ['code' => '5-3100', 'name' => 'Santunan Sosial', 'type' => 'EXPENSE'],
                        ['code' => '5-3200', 'name' => 'Bantuan Pendidikan', 'type' => 'EXPENSE'],
                    ]]
                ]
            ],
        ];

        foreach ($coas as $l1) {
            $parentL1 = $this->insertCOA($l1);
            if (isset($l1['children'])) {
                foreach ($l1['children'] as $l2) {
                    $parentL2 = $this->insertCOA($l2, $parentL1);
                    if (isset($l2['children'])) {
                        foreach ($l2['children'] as $l3) {
                            $this->insertCOA($l3, $parentL2);
                        }
                    }
                }
            }
        }
    }

    private function insertCOA($data, $parentId = null)
    {
        $coaId = DB::table('finance_coas')->insertGetId([
            'organization_unit_id' => null, // Global
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type'],
            'parent_id' => $parentId,
            'is_cash' => $data['is_cash'] ?? false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $coaId;
    }

    private function createDummyTransactions($unitId, $userId)
    {
        // Ambil ID akun penting untuk simulasi
        $kasTunai = DB::table('finance_coas')->where('code', '1-1100')->value('id');
        $bankBSI = DB::table('finance_coas')->where('code', '1-1200')->value('id');
        $modalAwal = DB::table('finance_coas')->where('code', '3-1100')->value('id'); // Aset Neto
        
        $infaqJumat = DB::table('finance_coas')->where('code', '4-1100')->value('id');
        $listrik = DB::table('finance_coas')->where('code', '5-1100')->value('id');
        $honorKhatib = DB::table('finance_coas')->where('code', '5-2100')->value('id');

        // --- 1. SALDO AWAL (JURNAL MANUAL) ---
        // Debit: Kas Tunai 5jt, Bank 10jt | Kredit: Aset Neto 15jt
        $journalId = DB::table('finance_journals')->insertGetId([
            'organization_unit_id' => $unitId,
            'user_id' => $userId,
            'transaction_date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'reference_no' => 'SA-001',
            'description' => 'Saldo Awal Pembukuan',
            'status' => 'POSTED',
            'total_amount' => 15000000,
            'created_at' => now(), 'updated_at' => now()
        ]);

        DB::table('finance_journal_details')->insert([
            ['journal_id' => $journalId, 'coa_id' => $kasTunai, 'debit' => 5000000, 'credit' => 0, 'fund_type' => 'UNRESTRICTED'],
            ['journal_id' => $journalId, 'coa_id' => $bankBSI, 'debit' => 10000000, 'credit' => 0, 'fund_type' => 'UNRESTRICTED'],
            ['journal_id' => $journalId, 'coa_id' => $modalAwal, 'debit' => 0, 'credit' => 15000000, 'fund_type' => 'UNRESTRICTED'],
        ]);

        // --- 2. TRANSAKSI HARIAN (INCOME) ---
        // Penerimaan Infaq Jumat Rp 2.500.000 (Setiap Jumat bulan ini)
        $dates = [
            Carbon::now()->startOfMonth()->addDays(4), 
            Carbon::now()->startOfMonth()->addDays(11)
        ];

        foreach ($dates as $date) {
            $amount = 2500000;
            $this->insertTransaction($unitId, $userId, 'INCOME', $date, $kasTunai, $infaqJumat, $amount, 'Infaq Jumat Pekan ini');
        }

        // --- 3. TRANSAKSI HARIAN (EXPENSE) ---
        // Bayar Listrik
        $this->insertTransaction($unitId, $userId, 'EXPENSE', Carbon::now()->startOfMonth()->addDays(10), $bankBSI, $listrik, 750000, 'Bayar Tagihan Listrik PLN');
        
        // Bayar Honor Khatib
        $this->insertTransaction($unitId, $userId, 'EXPENSE', Carbon::now()->startOfMonth()->addDays(4), $kasTunai, $honorKhatib, 300000, 'Bisyarah Khatib Jumat');
    }

    private function insertTransaction($unitId, $userId, $type, $date, $cashCoa, $categoryCoa, $amount, $desc)
    {
        // A. Jurnal (Accounting Record)
        $journalId = DB::table('finance_journals')->insertGetId([
            'organization_unit_id' => $unitId,
            'user_id' => $userId,
            'transaction_date' => $date,
            'reference_no' => 'TRX-' . rand(1000, 9999),
            'description' => $desc,
            'status' => 'POSTED',
            'total_amount' => $amount,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // B. Jurnal Detail (Debit/Kredit Logic)
        if ($type === 'INCOME') {
            // Debit: Kas, Kredit: Pendapatan
            DB::table('finance_journal_details')->insert([
                ['journal_id' => $journalId, 'coa_id' => $cashCoa, 'debit' => $amount, 'credit' => 0, 'fund_type' => 'UNRESTRICTED'],
                ['journal_id' => $journalId, 'coa_id' => $categoryCoa, 'debit' => 0, 'credit' => $amount, 'fund_type' => 'UNRESTRICTED'],
            ]);
        } else {
            // Debit: Beban, Kredit: Kas
            DB::table('finance_journal_details')->insert([
                ['journal_id' => $journalId, 'coa_id' => $categoryCoa, 'debit' => $amount, 'credit' => 0, 'fund_type' => 'UNRESTRICTED'],
                ['journal_id' => $journalId, 'coa_id' => $cashCoa, 'debit' => 0, 'credit' => $amount, 'fund_type' => 'UNRESTRICTED'],
            ]);
        }

        // C. Transaksi UI (Frontend Record)
        DB::table('finance_transactions')->insert([
            'organization_unit_id' => $unitId,
            'user_id' => $userId,
            'journal_id' => $journalId,
            'type' => $type,
            'date' => $date,
            'cash_coa_id' => $cashCoa,
            'category_coa_id' => $categoryCoa,
            'amount' => $amount,
            'description' => $desc,
            'created_at' => now(), 'updated_at' => now()
        ]);
    }
}