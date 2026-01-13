<?php

namespace Database\Seeders;

use App\Models\OrganizationUnit;
use App\Models\OrganizationTerritory;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // CLUSTER 1: PIMPINAN MUHAMMADIYAH (STRUKTURAL)
        // ==========================================
        
        // 1. Root: Pimpinan Cabang (PCM)
        $pcm = OrganizationUnit::create([
            'name' => 'PCM MUARA AMAN',
            'category' => 'STRUKTURAL',
            'type' => 'PCM',
            'address' => 'Jl. Kampung Jawa No. 123, Kel. Pasar Muara Aman',
            'sk_number' => '468/1943',
            'establishment_date' => '1934-04-28',
            'email' => 'pcmmuaraaman@gmail.com',
            'latitude' => -3.123321, 
            'longitude' => 102.217312
        ]);

        // 2. Children: Pimpinan Ranting (PRM) & Wilayah (Territories)
        $prms = [
            'PRM. Pasar Muara Aman' => [
                'lat' => -3.124500, 'lng' => 102.218500,
                'territories' => [
                    'KEL. PASAR MUARA AMAN', 
                    'DESA NANGAI AMEN', 
                    'DESA LOKASARI', 
                    'DESA GANDUNG'
                ]
            ],
            'PRM. Kampung Muara Aman' => [
                'lat' => -3.121200, 'lng' => 102.216000,
                'territories' => [
                    'KEL. KAMPUNG MUARA AMAN', 
                    'DESA KAMPUNG DALAM', 
                    'DESA GANDUNG BARU', 
                    'DESA TALANG ULU'
                ]
            ],
            'PRM. Kampung Jawa' => [
                'lat' => -3.126000, 'lng' => 102.212000,
                'territories' => [
                    'DESA KAMPUNG JAWA', 
                    'DESA TUNGGANG', 
                    'DESA LEBONG TAMBANG', 
                    'DESA LADANG PALEMBANG'
                ]
            ],
            'PRM. Embong' => [
                'lat' => -3.152000, 'lng' => 102.245000,
                'territories' => [
                    'DESA EMBONG I', 
                    'DESA EMBONG', 
                    'DESA KOTA AGUNG', 
                    'DESA KOTA BARU', 
                    'DESA LEMEU', 
                    'DESA PANGKALAN', 
                    'DESA TANGUA'
                ]
            ],
            'PRM. Ketenong' => [
                'lat' => -3.095000, 'lng' => 102.205000,
                'territories' => [
                    'DESA KETENONG I', 
                    'DESA KETENONG II', 
                    'DESA KETENONG JAYA', 
                    'DESA SEBELAT', 
                    'DESA SUNGAI LISAI'
                ]
            ],
            'PRM. Selebar Jaya' => [
                'lat' => -3.115000, 'lng' => 102.225000,
                'territories' => [
                    'DESA SELEBAR JAYA', 
                    'KEL. AMEN', 
                    'DESA GARUT', 
                    'DESA NANGAI TAYAU', 
                    'DESA NANGAI TAYAU I', 
                    'DESA PYANG MBIK', 
                    'DESA SUKAU MERGO', 
                    'DESA SUKAU RAJO', 
                    'DESA SUNGAI GERONG', 
                    'DESA TALANG BUNUT'
                ]
            ],
            'PRM. Talang Pelupuh' => [
                'lat' => -3.108000, 'lng' => 102.198000,
                'territories' => [
                    'DESA AIR KOPRAS', 
                    'DESA BIOA PUTIAK'
                ]
            ],
        ];

        foreach ($prms as $name => $data) {
            $prm = OrganizationUnit::create([
                'name' => $name,
                'category' => 'STRUKTURAL',
                'type' => 'PRM',
                'parent_id' => $pcm->id,
                'latitude' => $data['lat'],
                'longitude' => $data['lng']
            ]);

            // Insert Wilayah (Territories)
            foreach ($data['territories'] as $territoryName) {
                OrganizationTerritory::create([
                    'organization_unit_id' => $prm->id,
                    'name' => $territoryName
                ]);
            }
        }

        // ==========================================
        // CLUSTER 2: AMAL USAHA MUHAMMADIYAH (AUM)
        // ==========================================
        $aums = [
            ['name' => 'SMKS 6 Muhammadiyah', 'type' => 'SMK'],
            ['name' => 'SMP Muhammadiyah 05', 'type' => 'SMP'],
            ['name' => 'TK ABA 1', 'type' => 'TK'],
            ['name' => 'TK ABA 6', 'type' => 'TK'],
            ['name' => 'KL Lazismu', 'type' => 'LAZISMU'],
        ];

        foreach ($aums as $aum) {
            OrganizationUnit::create([
                'name' => $aum['name'],
                'category' => 'AUM',
                'type' => $aum['type'],
                'parent_id' => $pcm->id, // AUM di bawah pembinaan PCM
                // Koordinat bisa diisi null dulu atau ambil dari parent
                'latitude' => $pcm->latitude, 
                'longitude' => $pcm->longitude,
            ]);
        }

        // ==========================================
        // CLUSTER 3: ORGANISASI OTONOM (ORTOM)
        // ==========================================
        $ortoms = [
            ['name' => 'PCA. Muara Aman', 'type' => 'AISYIYAH'],
            ['name' => 'PCPM. Muara Aman', 'type' => 'PEMUDA'],
            ['name' => 'PCNA. Muara Aman', 'type' => 'NA'],
            ['name' => 'Hizbul Wathon', 'type' => 'HW'],
            ['name' => 'IMM', 'type' => 'IMM'],
            ['name' => 'IPM Muara Aman', 'type' => 'IPM'],
            ['name' => 'UKL TSPM Muara Aman', 'type' => 'TAPAK_SUCI'],
        ];

        foreach ($ortoms as $ortom) {
            OrganizationUnit::create([
                'name' => $ortom['name'],
                'category' => 'ORTOM',
                'type' => $ortom['type'],
                'parent_id' => $pcm->id,
                'latitude' => $pcm->latitude,
                'longitude' => $pcm->longitude,
            ]);
        }
    }
}