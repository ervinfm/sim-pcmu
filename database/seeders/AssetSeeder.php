<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\AssetUnit;
use App\Models\AssetLocation;
use App\Models\AssetLoan;
use App\Models\OrganizationUnit;
use App\Models\User;
use Carbon\Carbon;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SETUP SATUAN BARANG (Global)
        $this->command->info('1. Seeding Satuan Barang...');
        $units = [
            ['code' => 'UNIT', 'name' => 'Unit'],
            ['code' => 'PCS',  'name' => 'Pieces (Pcs)'],
            ['code' => 'SET',  'name' => 'Set / Paket'],
            ['code' => 'M2',   'name' => 'Meter Persegi'],
            ['code' => 'BH',   'name' => 'Buah'],
            ['code' => 'LBR',  'name' => 'Lembar'],
            ['code' => 'RIM',  'name' => 'Rim'],
            ['code' => 'BOX',  'name' => 'Box / Dus'],
        ];

        foreach ($units as $u) {
            AssetUnit::firstOrCreate(['code' => $u['code']], $u);
        }
        
        // Helper ID Satuan
        $uUnit = AssetUnit::where('code', 'UNIT')->first()->id;
        $uPcs  = AssetUnit::where('code', 'PCS')->first()->id;
        $uSet  = AssetUnit::where('code', 'SET')->first()->id;
        $uM2   = AssetUnit::where('code', 'M2')->first()->id;
        $uBox  = AssetUnit::where('code', 'BOX')->first()->id;

        // User Default (Admin)
        $admin = User::first(); 
        
        // ==========================================================
        // SKENARIO 1: PCM (Struktural - Aset Bernilai Tinggi)
        // ==========================================================
        $this->command->info('2. Seeding Aset PCM (Kantor & Lahan)...');
        
        $pcm = OrganizationUnit::firstOrCreate(
            ['code' => 'PCM-01'], 
            ['name' => 'PCM Kartasura', 'type' => 'PCM', 'category' => 'STRUKTURAL']
        );
        
        $locPcmKantor = AssetLocation::firstOrCreate(['organization_unit_id' => $pcm->id, 'name' => 'Kantor Sekretariat PCM']);
        $locPcmLahan = AssetLocation::firstOrCreate(['organization_unit_id' => $pcm->id, 'name' => 'Lahan Pengembangan']);
        $locPcmArsip = AssetLocation::firstOrCreate(['organization_unit_id' => $pcm->id, 'name' => 'Ruang Arsip']);

        // 1. Tanah Wakaf
        Asset::create([
            'organization_unit_id' => $pcm->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locPcmLahan->id, 'asset_unit_id' => $uM2,
            'inventory_code' => 'INV/PCM/2020/LAND/001',
            'name' => 'Tanah Wakaf H. Ahmad Dahlan',
            'category' => 'LAND',
            'specifications' => ['luas' => '2500 m2', 'legalitas' => 'Sertifikat Wakaf (AIW)', 'nomor_sertifikat' => '12.34.56.78.9.001'],
            'acquisition_date' => '2020-01-01', 'acquisition_value' => 5000000000,
            'source_of_acquisition' => 'WAKAF', 'condition' => 'GOOD', 'status' => 'ACTIVE'
        ]);

        // 2. Gedung Dakwah
        Asset::create([
            'organization_unit_id' => $pcm->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locPcmLahan->id, 'asset_unit_id' => $uUnit,
            'inventory_code' => 'INV/PCM/2021/BLD/001',
            'name' => 'Gedung Dakwah Muhammadiyah',
            'category' => 'BUILDING',
            'specifications' => ['luas_bangunan' => '800 m2', 'jumlah_lantai' => 2, 'imb' => 'IMB-99823'],
            'acquisition_date' => '2021-05-20', 'acquisition_value' => 2500000000,
            'source_of_acquisition' => 'WAKAF', 'condition' => 'GOOD', 'status' => 'ACTIVE'
        ]);

        // 3. Mobil Dakwah
        Asset::create([
            'organization_unit_id' => $pcm->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locPcmKantor->id, 'asset_unit_id' => $uUnit,
            'inventory_code' => 'INV/PCM/2023/VEH/001',
            'name' => 'Toyota Innova Reborn (Mobil Dakwah)',
            'category' => 'VEHICLE',
            'specifications' => ['nopol' => 'AD 1912 MU', 'merk' => 'Toyota', 'tipe' => 'Innova G Diesel'],
            'acquisition_date' => '2023-05-10', 'acquisition_value' => 450000000,
            'source_of_acquisition' => 'PURCHASE', 'condition' => 'GOOD', 'status' => 'ACTIVE'
        ]);

        // 4. Laptop Admin (Rusak Ringan)
        Asset::create([
            'organization_unit_id' => $pcm->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locPcmKantor->id, 'asset_unit_id' => $uUnit,
            'inventory_code' => 'INV/PCM/2022/ELC/001',
            'name' => 'Laptop ASUS Vivobook Admin',
            'category' => 'ELECTRONIC',
            'specifications' => ['brand' => 'ASUS', 'processor' => 'Ryzen 5', 'ram' => '8GB'],
            'acquisition_date' => '2022-02-15', 'acquisition_value' => 8500000,
            'source_of_acquisition' => 'PURCHASE', 'condition' => 'SLIGHTLY_DAMAGED', 'status' => 'ACTIVE',
            'description' => 'Layar ada deadpixel di pojok kanan.'
        ]);

        // 5. Lemari Arsip Besi
        Asset::create([
            'organization_unit_id' => $pcm->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locPcmArsip->id, 'asset_unit_id' => $uUnit,
            'inventory_code' => 'INV/PCM/2019/FUR/001',
            'name' => 'Lemari Arsip Besi Tahan Api',
            'category' => 'FURNITURE',
            'specifications' => ['bahan' => 'Besi Baja', 'ukuran' => 'Large', 'kunci' => 'Digital'],
            'acquisition_date' => '2019-11-10', 'acquisition_value' => 4500000,
            'source_of_acquisition' => 'PURCHASE', 'condition' => 'GOOD', 'status' => 'ACTIVE'
        ]);

        // ==========================================================
        // SKENARIO 2: SEKOLAH (Bulk Data - 15 PC Lab)
        // ==========================================================
        $this->command->info('3. Seeding Aset Sekolah (Bulk Data PC Lab)...');

        $smk = OrganizationUnit::firstOrCreate(
            ['code' => 'SMK-MUH1'],
            ['name' => 'SMK Muhammadiyah 1', 'type' => 'SMK', 'category' => 'AUM']
        );
        $locLab = AssetLocation::firstOrCreate(['organization_unit_id' => $smk->id, 'name' => 'Lab Komputer RPL']);
        $locGuru = AssetLocation::firstOrCreate(['organization_unit_id' => $smk->id, 'name' => 'Ruang Guru']);

        // 6-20. Bulk Insert 15 PC
        for ($i = 1; $i <= 15; $i++) {
            $nomor = str_pad($i, 3, '0', STR_PAD_LEFT);
            Asset::create([
                'organization_unit_id' => $smk->id,
                'user_id' => $admin?->id,
                'asset_location_id' => $locLab->id,
                'asset_unit_id' => $uUnit,
                'inventory_code' => "INV/SMK/2024/ELC/PC-{$nomor}",
                'name' => "PC Lab Client {$nomor}",
                'category' => 'ELECTRONIC',
                'specifications' => ['processor' => 'Intel Core i5 Gen 12', 'ram' => '16 GB', 'storage' => 'SSD 512GB'],
                'acquisition_date' => '2024-01-15',
                'acquisition_value' => 7500000,
                'source_of_acquisition' => 'GOVERNMENT_AID',
                'condition' => 'GOOD',
                'status' => 'ACTIVE'
            ]);
        }

        // 21. AC Ruang Guru (Rusak Berat / Broken)
        Asset::create([
            'organization_unit_id' => $smk->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locGuru->id, 'asset_unit_id' => $uUnit,
            'inventory_code' => 'INV/SMK/2018/ELC/AC01',
            'name' => 'AC Panasonic 2PK',
            'category' => 'ELECTRONIC',
            'specifications' => ['brand' => 'Panasonic', 'pk' => '2 PK'],
            'acquisition_date' => '2018-05-05', 'acquisition_value' => 5500000,
            'source_of_acquisition' => 'PURCHASE', 'condition' => 'HEAVILY_DAMAGED', 'status' => 'BROKEN',
            'description' => 'Kompresor mati total, rencana dihapuskan.'
        ]);

        // ==========================================================
        // SKENARIO 3: KLINIK (Maintenance & Write Off)
        // ==========================================================
        $this->command->info('4. Seeding Aset Klinik (Maintenance)...');

        $klinik = OrganizationUnit::firstOrCreate(
            ['code' => 'KLINIK-01'],
            ['name' => 'Klinik PKU Muhammadiyah', 'type' => 'KLINIK', 'category' => 'AUM']
        );
        $locUgd = AssetLocation::firstOrCreate(['organization_unit_id' => $klinik->id, 'name' => 'IGD']);
        $locGenset = AssetLocation::firstOrCreate(['organization_unit_id' => $klinik->id, 'name' => 'Ruang Genset']);

        // 22. Ambulans (Sedang Service)
        Asset::create([
            'organization_unit_id' => $klinik->id, 'user_id' => $admin?->id,
            'asset_location_id' => null, 'asset_unit_id' => $uUnit,
            'inventory_code' => 'INV/PKU/2021/VEH/001',
            'name' => 'Ambulans Lazismu (APV)',
            'category' => 'VEHICLE',
            'specifications' => ['nopol' => 'B 1912 LAZ', 'merk' => 'Suzuki APV'],
            'acquisition_date' => '2021-12-01', 'acquisition_value' => 220000000,
            'source_of_acquisition' => 'WAKAF', 'condition' => 'SLIGHTLY_DAMAGED', 'status' => 'MAINTENANCE',
            'description' => 'Sedang ganti oli dan kampas rem di Bengkel Sejahtera.'
        ]);

        // 23. Genset Besar
        Asset::create([
            'organization_unit_id' => $klinik->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locGenset->id, 'asset_unit_id' => $uUnit,
            'inventory_code' => 'INV/PKU/2020/MCH/001',
            'name' => 'Genset Silent Perkins 60 kVA',
            'category' => 'MACHINERY',
            'specifications' => ['brand' => 'Perkins', 'kapasitas' => '60 kVA', 'bahan_bakar' => 'Solar'],
            'acquisition_date' => '2020-03-10', 'acquisition_value' => 180000000,
            'source_of_acquisition' => 'PURCHASE', 'condition' => 'GOOD', 'status' => 'ACTIVE'
        ]);

        // 24. Alat USG Lama (Write Off / Dihapuskan)
        Asset::create([
            'organization_unit_id' => $klinik->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locUgd->id, 'asset_unit_id' => $uUnit,
            'inventory_code' => 'INV/PKU/2010/MED/001',
            'name' => 'USG 2D Mindray Old',
            'category' => 'MACHINERY',
            'specifications' => ['brand' => 'Mindray', 'tipe' => '2D BW'],
            'acquisition_date' => '2010-01-01', 'acquisition_value' => 80000000,
            'source_of_acquisition' => 'PURCHASE', 'condition' => 'HEAVILY_DAMAGED', 'status' => 'WRITE_OFF',
            'description' => 'Sudah tidak layak pakai, diganti alat baru.'
        ]);

        // ==========================================================
        // SKENARIO 4: MASJID (Peminjaman Warga & Internal)
        // ==========================================================
        $this->command->info('5. Seeding Aset Masjid (Peminjaman)...');

        $masjid = OrganizationUnit::firstOrCreate(
            ['code' => 'MSJ-RAYA'],
            ['name' => 'Masjid Raya Al-Huda', 'type' => 'MASJID', 'category' => 'AUM']
        );
        $locGudangMasjid = AssetLocation::firstOrCreate(['organization_unit_id' => $masjid->id, 'name' => 'Gudang Perlengkapan']);
        $locUtama = AssetLocation::firstOrCreate(['organization_unit_id' => $masjid->id, 'name' => 'Ruang Utama']);

        // 25. Tenda (Dipinjam Warga)
        $tenda = Asset::create([
            'organization_unit_id' => $masjid->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locGudangMasjid->id, 'asset_unit_id' => $uSet,
            'inventory_code' => 'INV/MSJ/2023/FUR/001',
            'name' => 'Tenda Kajian 4x6 Meter',
            'category' => 'FURNITURE',
            'specifications' => ['warna' => 'Biru Putih', 'rangka' => 'Besi Pipa'],
            'acquisition_date' => '2023-01-01', 'acquisition_value' => 3500000,
            'source_of_acquisition' => 'PURCHASE', 'condition' => 'GOOD', 'status' => 'BORROWED'
        ]);
        
        AssetLoan::create([
            'asset_id' => $tenda->id,
            'user_id' => null, // Eksternal
            'borrower_name' => 'Bapak Hartono (RT 05)', 'borrower_contact' => '081234567890',
            'loan_date' => Carbon::now()->subDays(1), 'return_date_plan' => Carbon::now()->addDays(2),
            'condition_before' => 'GOOD', 'status' => 'BORROWED', 'description' => 'Kerja bakti kampung', 'approved_by' => $admin?->id
        ]);

        // 26. Sound System
        Asset::create([
            'organization_unit_id' => $masjid->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locUtama->id, 'asset_unit_id' => $uSet,
            'inventory_code' => 'INV/MSJ/2024/ELC/001',
            'name' => 'Paket Sound System TOA',
            'category' => 'ELECTRONIC',
            'specifications' => ['amplifier' => 'TOA ZA-2240', 'speaker' => '4 Column'],
            'acquisition_date' => '2024-03-01', 'acquisition_value' => 12000000,
            'source_of_acquisition' => 'WAKAF', 'condition' => 'GOOD', 'status' => 'ACTIVE'
        ]);

        // 27. Karpet (Gulungan)
        Asset::create([
            'organization_unit_id' => $masjid->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locGudangMasjid->id, 'asset_unit_id' => $uBox,
            'inventory_code' => 'INV/MSJ/2024/OTH/005',
            'name' => 'Karpet Cadangan Turki',
            'category' => 'OTHER',
            'specifications' => ['motif' => 'Hijau Polos', 'ketebalan' => '12mm'],
            'acquisition_date' => '2024-01-01', 'acquisition_value' => 5000000,
            'source_of_acquisition' => 'WAKAF', 'condition' => 'GOOD', 'status' => 'ACTIVE'
        ]);

        // ==========================================================
        // SKENARIO 5: ORTOM (Peminjaman Overdue)
        // ==========================================================
        $this->command->info('6. Seeding Aset Ortom (Overdue & History)...');

        $ortom = OrganizationUnit::firstOrCreate(
            ['code' => 'TS-01'],
            ['name' => 'Pimda Tapak Suci 01', 'type' => 'TAPAK_SUCI', 'category' => 'ORTOM']
        );
        $locSanggar = AssetLocation::firstOrCreate(['organization_unit_id' => $ortom->id, 'name' => 'Sanggar Latihan']);

        // 28. Body Protector (Overdue/Telat)
        $protector = Asset::create([
            'organization_unit_id' => $ortom->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locSanggar->id, 'asset_unit_id' => $uPcs,
            'inventory_code' => 'INV/TS/2023/EQP/001',
            'name' => 'Body Protector (Merah)',
            'category' => 'OTHER',
            'specifications' => ['merk' => 'Hokido', 'ukuran' => 'L'],
            'acquisition_date' => '2023-06-01', 'acquisition_value' => 450000,
            'source_of_acquisition' => 'PURCHASE', 'condition' => 'GOOD', 'status' => 'BORROWED'
        ]);
        
        AssetLoan::create([
            'asset_id' => $protector->id, 'user_id' => $admin?->id, // Internal
            'loan_date' => Carbon::now()->subDays(10), 'return_date_plan' => Carbon::now()->subDays(3), // Harusnya balik 3 hari lalu
            'condition_before' => 'GOOD', 'status' => 'OVERDUE', 
            'description' => 'Kejurnas Solo (Belum kembali)', 'approved_by' => $admin?->id
        ]);

        // 29. Samsak (History Peminjaman Selesai)
        $samsak = Asset::create([
            'organization_unit_id' => $ortom->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locSanggar->id, 'asset_unit_id' => $uPcs,
            'inventory_code' => 'INV/TS/2022/EQP/002',
            'name' => 'Samsak Gantung 1 Meter',
            'category' => 'OTHER',
            'specifications' => ['berat' => '50kg', 'bahan' => 'Kulit Sintetis'],
            'acquisition_date' => '2022-01-10', 'acquisition_value' => 850000,
            'source_of_acquisition' => 'PURCHASE', 'condition' => 'GOOD', 'status' => 'ACTIVE'
        ]);

        AssetLoan::create([
            'asset_id' => $samsak->id, 'user_id' => $admin?->id,
            'loan_date' => Carbon::now()->subMonth(1), 'return_date_plan' => Carbon::now()->subMonth(1)->addDays(5),
            'return_date_actual' => Carbon::now()->subMonth(1)->addDays(5),
            'condition_before' => 'GOOD', 'condition_after' => 'GOOD',
            'status' => 'COMPLETED', 'description' => 'Latihan Gabungan', 'approved_by' => $admin?->id
        ]);

        // 30. Matras
        Asset::create([
            'organization_unit_id' => $ortom->id, 'user_id' => $admin?->id,
            'asset_location_id' => $locSanggar->id, 'asset_unit_id' => $uM2,
            'inventory_code' => 'INV/TS/2023/EQP/005',
            'name' => 'Matras Puzzle Eva Foam',
            'category' => 'OTHER',
            'specifications' => ['ketebalan' => '3cm', 'warna' => 'Merah Biru', 'jumlah_keping' => '100'],
            'acquisition_date' => '2023-08-17', 'acquisition_value' => 3000000,
            'source_of_acquisition' => 'GRANT', 'condition' => 'GOOD', 'status' => 'ACTIVE'
        ]);

        $this->command->info('✅ Asset Seeding Complete! Total: 30 Aset + History Peminjaman.');
    }
}