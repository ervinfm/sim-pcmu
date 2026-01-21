<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\AssetUnit;
use App\Models\AssetLocation;
use App\Models\AssetLoan;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Models\Member;
use Carbon\Carbon;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Memulai Seeding Aset Muhammadiyah Muara Aman (Fixed Structure)...');

        // ---------------------------------------------------------
        // 1. SETUP GLOBAL DATA
        // ---------------------------------------------------------
        $admin = User::first();
        
        // Buat Satuan Barang
        $unitsData = [
            ['code' => 'UNIT', 'name' => 'Unit'],
            ['code' => 'PCS',  'name' => 'Pieces (Pcs)'],
            ['code' => 'SET',  'name' => 'Set / Paket'],
            ['code' => 'M2',   'name' => 'Meter Persegi'],
            ['code' => 'BH',   'name' => 'Buah'],
            ['code' => 'LBR',  'name' => 'Lembar'],
            ['code' => 'RIM',  'name' => 'Rim'],
            ['code' => 'BOX',  'name' => 'Box / Dus'],
            ['code' => 'EXM',  'name' => 'Eksemplar'],
        ];
        foreach ($unitsData as $u) {
            AssetUnit::firstOrCreate(['code' => $u['code']], $u);
        }
        
        // Helper ID Satuan
        $uUnit = AssetUnit::where('code', 'UNIT')->first()->id;
        $uPcs  = AssetUnit::where('code', 'PCS')->first()->id;
        $uSet  = AssetUnit::where('code', 'SET')->first()->id;
        $uM2   = AssetUnit::where('code', 'M2')->first()->id;

        // ---------------------------------------------------------
        // 2. SETUP STRUKTUR ORGANISASI (SESUAI MIGRATION)
        // ---------------------------------------------------------
        
        // A. PCM (INDUK)
        $pcm = OrganizationUnit::firstOrCreate(
            ['code' => 'PCM-MA'],
            [
                'name' => 'Pimpinan Cabang Muhammadiyah Muara Aman', 
                'category' => 'STRUKTURAL', // Ganti 'level' jadi 'category'
                'type' => 'PCM',            // Ganti 'level' jadi 'type'
                'parent_id' => null
            ]
        );
        
        $locGudangPCM = AssetLocation::firstOrCreate(
            ['organization_unit_id' => $pcm->id, 'name' => 'Gudang Inventaris PCM'],
            ['description' => 'Penyimpanan aset umum cabang']
        );

        // B. SETUP MEMBER DUMMY
        $member1 = Member::firstOrCreate(['nbm' => '1001'], ['full_name' => 'Ketua PCM (Dummy)', 'organization_unit_id' => $pcm->id, 'phone_number' => '081111', 'gender'=>'L', 'birth_place'=>'Lebong', 'birth_date'=>'1980-01-01', 'address'=>'-', 'status'=>'ACTIVE']);
        $member2 = Member::firstOrCreate(['nbm' => '1002'], ['full_name' => 'Sekretaris Kokam (Dummy)', 'organization_unit_id' => $pcm->id, 'phone_number' => '082222', 'gender'=>'L', 'birth_place'=>'Lebong', 'birth_date'=>'1990-01-01', 'address'=>'-', 'status'=>'ACTIVE']);

        // ---------------------------------------------------------
        // 3. GENERATE ASET PER UNIT (AUM & ORTOM)
        // ---------------------------------------------------------

        // === 1. MASJID AL JIHAD MUARA AMAN ===
        $masjid = OrganizationUnit::firstOrCreate(
            ['name' => 'Masjid Al Jihad Muara Aman'],
            ['code' => 'MSJ-JIHAD', 'category' => 'AUM', 'type' => 'MASJID', 'parent_id' => $pcm->id]
        );
        $locMasjid = AssetLocation::firstOrCreate(['organization_unit_id' => $masjid->id, 'name' => 'Ruang Utama']);
        
        $this->createAsset($masjid, $locMasjid, $uM2, 'LAND', 'Tanah Wakaf Masjid Al Jihad', 'INV/MSJ/2010/LND/001', 
            ['luas' => 1500, 'status_tanah' => 'Wakaf', 'lokasi' => 'Jl. Kampung Jawa'], 2000000000, 'WAKAF');

        // === 2. KL LAZISMU ===
        $lazismu = OrganizationUnit::firstOrCreate(
            ['name' => 'KL Lazismu Muara Aman'],
            ['code' => 'KL-LAZIS', 'category' => 'AUM', 'type' => 'LAZISMU', 'parent_id' => $pcm->id]
        );
        $locLazis = AssetLocation::firstOrCreate(['organization_unit_id' => $lazismu->id, 'name' => 'Kantor Operasional']);
        
        $ambulans = $this->createAsset($lazismu, $locLazis, $uUnit, 'VEHICLE', 'Ambulans Lazismu', 'INV/LAZIS/2022/VEH/001', 
            ['nopol' => 'BD 9999 MU', 'merk' => 'Daihatsu', 'tipe' => 'Grand Max'], 250000000, 'WAKAF');

        // === 3. SMKS 6 MUHAMMADIYAH ===
        $smk = OrganizationUnit::firstOrCreate(
            ['name' => 'SMKS 6 Muhammadiyah'],
            ['code' => 'SMK-6', 'category' => 'AUM', 'type' => 'SMK', 'parent_id' => $pcm->id]
        );
        $locLabSmk = AssetLocation::firstOrCreate(['organization_unit_id' => $smk->id, 'name' => 'Lab Komputer']);
        $this->createAsset($smk, $locLabSmk, $uUnit, 'ELECTRONIC', 'Server UNBK', 'INV/SMK6/2021/ELE/002', [], 25000000, 'PURCHASE');

        // === 4. SMP MUHAMMADIYAH 05 ===
        $smp = OrganizationUnit::firstOrCreate(
            ['name' => 'SMP Muhammadiyah 05'],
            ['code' => 'SMP-05', 'category' => 'AUM', 'type' => 'SMP', 'parent_id' => $pcm->id]
        );
        $locKelasSmp = AssetLocation::firstOrCreate(['organization_unit_id' => $smp->id, 'name' => 'Ruang Kelas 7']);
        $this->createAsset($smp, $locKelasSmp, $uSet, 'FURNITURE', 'Meja Kursi Siswa (30 Set)', 'INV/SMP5/2022/FUR/001', [], 15000000, 'PURCHASE');

        // === 5. TK ABA ===
        $tk1 = OrganizationUnit::firstOrCreate(['name' => 'TK ABA 1'], ['code' => 'TK-ABA1', 'category' => 'AUM', 'type' => 'TK', 'parent_id' => $pcm->id]);
        $locTk1 = AssetLocation::firstOrCreate(['organization_unit_id' => $tk1->id, 'name' => 'Area Bermain']);
        $this->createAsset($tk1, $locTk1, $uSet, 'OTHER', 'Playground Outdoor', 'INV/ABA1/2020/OTH/001', [], 5000000, 'PURCHASE');

        $tk6 = OrganizationUnit::firstOrCreate(['name' => 'TK ABA 6'], ['code' => 'TK-ABA6', 'category' => 'AUM', 'type' => 'TK', 'parent_id' => $pcm->id]);

        // === 6. ORTOM SPESIFIK ===
        
        // UKL TSPM (TAPAK SUCI)
        $tspm = OrganizationUnit::firstOrCreate(
            ['name' => 'UKL TSPM Muara Aman'],
            ['code' => 'TSPM-MA', 'category' => 'ORTOM', 'type' => 'TAPAK_SUCI', 'parent_id' => $pcm->id]
        );
        $locSanggar = AssetLocation::firstOrCreate(['organization_unit_id' => $tspm->id, 'name' => 'Sanggar Latihan']);
        $matras = $this->createAsset($tspm, $locSanggar, $uM2, 'OTHER', 'Matras Gelanggang', 'INV/TSPM/2023/OTH/001', [], 8000000, 'PURCHASE');

        // HIZBUL WATHON (HW)
        $hw = OrganizationUnit::firstOrCreate(
            ['name' => 'Hizbul Wathon Muara Aman'],
            ['code' => 'HW-MA', 'category' => 'ORTOM', 'type' => 'HW', 'parent_id' => $pcm->id]
        );
        $locHw = AssetLocation::firstOrCreate(['organization_unit_id' => $hw->id, 'name' => 'Gudang HW']);
        $tenda = $this->createAsset($hw, $locHw, $uSet, 'OTHER', 'Tenda Regu', 'INV/HW/2022/OTH/001', [], 7500000, 'PURCHASE');
        $drumband = $this->createAsset($hw, $locHw, $uSet, 'OTHER', 'Peralatan Drumband', 'INV/HW/2019/OTH/002', [], 20000000, 'PURCHASE', 'SLIGHTLY_DAMAGED');

        // ORTOM LAINNYA (PCA, PCPM, PCNA, IMM, IPM)
        $ortomList = [
            ['name' => 'PCA. Muara Aman',  'type' => 'AISYIYAH', 'code' => 'PCA-MA'],
            ['name' => 'PCPM. Muara Aman', 'type' => 'PEMUDA',   'code' => 'PCPM-MA'],
            ['name' => 'PCNA. Muara Aman', 'type' => 'NA',       'code' => 'PCNA-MA'],
            ['name' => 'IMM',              'type' => 'IMM',      'code' => 'IMM-MA'],
            ['name' => 'IPM Muara Aman',   'type' => 'IPM',      'code' => 'IPM-MA'],
        ];

        foreach ($ortomList as $o) {
            $org = OrganizationUnit::firstOrCreate(
                ['name' => $o['name']],
                ['code' => $o['code'], 'category' => 'ORTOM', 'type' => $o['type'], 'parent_id' => $pcm->id]
            );
            $loc = AssetLocation::firstOrCreate(['organization_unit_id' => $org->id, 'name' => 'Sekretariat']);
            $this->createAsset($org, $loc, $uSet, 'FURNITURE', 'Meja Rapat', 'INV/'.$o['code'].'/2023/FUR/001', [], 3000000, 'PURCHASE');
        }

        // ---------------------------------------------------------
        // 4. SIMULASI PEMINJAMAN (LOAN)
        // ---------------------------------------------------------
        
        // 1. Ambulans Lazismu dipinjam PCPM (Status: BORROWED/Aktif)
        AssetLoan::create([
            'asset_id' => $ambulans->id,
            'member_id' => $member1->id,
            'borrower_type' => 'INTERNAL',
            'loan_date' => Carbon::now()->subDays(1),
            'return_date_plan' => Carbon::now()->addDays(1),
            'condition_before' => 'GOOD',
            'status' => 'BORROWED',
            'description' => 'Siaga Kesehatan Kegiatan Pemuda',
            'approved_by' => $admin?->id
        ]);

        // 2. Tenda HW dipinjam (Status: COMPLETED/Selesai)
        AssetLoan::create([
            'asset_id' => $tenda->id,
            'member_id' => $member2->id,
            'borrower_type' => 'INTERNAL',
            'loan_date' => Carbon::now()->subMonth(1),
            'return_date_plan' => Carbon::now()->subMonth(1)->addDays(3),
            'return_date_actual' => Carbon::now()->subMonth(1)->addDays(3),
            'condition_before' => 'GOOD',
            'condition_after' => 'GOOD',
            'status' => 'COMPLETED',
            'description' => 'Kegiatan Perkemahan Sabtu Minggu',
            'approved_by' => $admin?->id
        ]);

        // 3. Matras Tapak Suci dipinjam External (Karang Taruna) (Status: OVERDUE/Telat)
        AssetLoan::create([
            'asset_id' => $matras->id,
            'borrower_type' => 'EXTERNAL',
            'borrower_name' => 'Karang Taruna Desa',
            'borrower_contact' => '08123456789',
            'loan_date' => Carbon::now()->subDays(5),
            'return_date_plan' => Carbon::now()->subDays(2), // Harusnya balik 2 hari lalu
            'condition_before' => 'GOOD',
            'status' => 'BORROWED',
            'description' => 'Latihan gabungan desa',
            'approved_by' => $admin?->id
        ]);

        // 4. Drumband HW diajukan untuk pawai (Status: PENDING)
        AssetLoan::create([
            'asset_id' => $drumband->id,
            'member_id' => $member1->id,
            'borrower_type' => 'INTERNAL',
            'loan_date' => Carbon::now()->addDays(2),
            'return_date_plan' => Carbon::now()->addDays(3),
            'condition_before' => 'SLIGHTLY_DAMAGED',
            'status' => 'PENDING',
            'description' => 'Pawai Taaruf Musycab',
            'approved_by' => null
        ]);

        $this->command->info('✅ Database Aset Muhammadiyah Muara Aman Berhasil Diisi!');
    }

    private function createAsset($org, $loc, $unitId, $cat, $name, $code, $specs, $val, $src, $cond = 'GOOD')
    {
        // Cek dulu apakah aset dengan kode ini sudah ada agar tidak error duplicate
        $exists = Asset::where('inventory_code', $code)->exists();
        if ($exists) return Asset::where('inventory_code', $code)->first();

        return Asset::create([
            'organization_unit_id' => $org->id,
            'user_id' => 1, // Admin
            'asset_location_id' => $loc->id,
            'asset_unit_id' => $unitId,
            'category' => $cat,
            'name' => $name,
            'inventory_code' => $code,
            'specifications' => $specs,
            'acquisition_date' => Carbon::now()->subYears(rand(1, 5)),
            'acquisition_value' => $val,
            'source_of_acquisition' => $src,
            'condition' => $cond,
            'status' => $cond === 'HEAVILY_DAMAGED' ? 'MAINTENANCE' : 'ACTIVE'
        ]);
    }
}