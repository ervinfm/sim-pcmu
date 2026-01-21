<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Archive;
use App\Models\OrganizationUnit; // Import Model OrganizationUnit
use App\Models\User;             // Import Model User
use Illuminate\Support\Str;

class ArchiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. AMBIL DATA INDUK (PARENT) UNTUK RELASI
        // Kita butuh OrganizationUnit ID karena kolom ini NOT NULL
        $orgUnit = OrganizationUnit::first();

        // Safety Check: Jika OrganizationUnit belum ada sama sekali, buat 1 dummy
        if (!$orgUnit) {
            $orgUnit = OrganizationUnit::create([
                'name' => 'Kantor Layanan Lazismu Pusat',
                'category' => 'AUM',
                'type' => 'LAZISMU',
                'address' => 'Jl. Menteng Raya No. 62',
                'description' => 'Unit dummy untuk keperluan seeding archive.'
            ]);
        }

        // Ambil user pertama sebagai uploader, atau buat jika kosong
        $user = User::first() ?? User::factory()->create();

        // 2. SKENARIO DATA
        $scenarios = [
            // --- KELOMPOK 1: DOKUMEN RESMI (PDF & WORD) ---
            [
                'title' => 'Surat Keputusan Direksi No. 102 Tahun 2024',
                'type' => 'document',
                'extension' => 'pdf',
                'category' => 'SK', // Sesuaikan dengan enum jika mungkin, atau string bebas
                'status' => 'published',
                'description' => 'Dokumen asli SK pengangkatan manajer baru.',
                'reference_number' => '102/DIR/2024',
            ],
            [
                'title' => 'Draft Proposal Kerjasama Vendor IT',
                'type' => 'document',
                'extension' => 'docx',
                'category' => 'SURAT_KELUAR',
                'status' => 'draft',
                'description' => 'Masih dalam proses revisi bagian anggaran.',
                'reference_number' => null,
            ],
            [
                'title' => 'Notulensi Rapat Tahunan Pemegang Saham',
                'type' => 'document',
                'extension' => 'doc',
                'category' => 'LAINNYA',
                'status' => 'archived',
                'description' => 'Arsip lama tahun 2020.',
                'reference_number' => null,
            ],

            // --- KELOMPOK 2: DATA KEUANGAN (EXCEL/SPREADSHEET) ---
            [
                'title' => 'Laporan Keuangan Q3 2024',
                'type' => 'spreadsheet',
                'extension' => 'xlsx',
                'category' => 'LAINNYA',
                'status' => 'published',
                'description' => 'Data detail pemasukan dan pengeluaran kuartal 3.',
                'reference_number' => null,
            ],
            [
                'title' => 'Rekapitulasi Gaji Karyawan Januari',
                'type' => 'spreadsheet',
                'extension' => 'csv',
                'category' => 'LAINNYA',
                'status' => 'private',
                'description' => 'Data sensitif, hanya untuk HRD.',
                'reference_number' => null,
            ],

            // --- KELOMPOK 3: PRESENTASI (PPT) ---
            [
                'title' => 'Materi Onboarding Karyawan Baru',
                'type' => 'presentation',
                'extension' => 'pptx',
                'category' => 'LAINNYA',
                'status' => 'published',
                'description' => 'Slide presentasi pengenalan budaya perusahaan.',
                'reference_number' => null,
            ],

            // --- KELOMPOK 4: GAMBAR & DESAIN (IMAGE) ---
            [
                'title' => 'Dokumentasi Gathering Bali 2024',
                'type' => 'image',
                'extension' => 'jpg',
                'category' => 'LAINNYA',
                'status' => 'published',
                'description' => 'Foto bersama tim di pantai Kuta.',
                'reference_number' => null,
            ],
            [
                'title' => 'Mockup UI/UX Dashboard v2',
                'type' => 'image',
                'extension' => 'png',
                'category' => 'LAINNYA',
                'status' => 'draft',
                'description' => 'Revisi desain dashboard admin panel.',
                'reference_number' => null,
            ],
            [
                'title' => 'Logo Perusahaan Vector High Res',
                'type' => 'image',
                'extension' => 'svg',
                'category' => 'LAINNYA',
                'status' => 'published',
                'description' => 'Master logo untuk keperluan cetak.',
                'reference_number' => null,
            ],

            // --- KELOMPOK 5: VIDEO & AUDIO (MULTIMEDIA) ---
            [
                'title' => 'Rekaman Webinar "Future of AI"',
                'type' => 'video',
                'extension' => 'mp4',
                'category' => 'LAINNYA',
                'status' => 'published',
                'file_size' => 450000,
                'description' => 'Video full durasi 2 jam materi pelatihan.',
                'reference_number' => null,
            ],
            [
                'title' => 'Jingle Iklan Radio 2024',
                'type' => 'audio',
                'extension' => 'mp3',
                'category' => 'LAINNYA',
                'status' => 'published',
                'description' => 'Audio file untuk promosi radio.',
                'reference_number' => null,
            ],

            // --- KELOMPOK 6: ARSIP TEKNIS (COMPRESSED/CODE) ---
            [
                'title' => 'Backup Database Januari 2024',
                'type' => 'archive',
                'extension' => 'zip',
                'category' => 'LAINNYA',
                'status' => 'private',
                'description' => 'Full backup SQL dan assets.',
                'reference_number' => null,
            ],
        ];

        // 3. EKSEKUSI INSERT
        foreach ($scenarios as $data) {
            Archive::factory()->create([
                // Wajib diisi (Foreign Key)
                'organization_unit_id' => $orgUnit->id, 
                'user_id' => $user->id,

                // Data dari Skenario
                'title'     => $data['title'],
                'slug'      => Str::slug($data['title']),
                'type'      => $data['type'],
                'file_extension' => $data['extension'],
                'category'  => $data['category'],
                'status'    => $data['status'],
                'description' => $data['description'],
                'reference_number' => $data['reference_number'] ?? null,
                
                // Simulasi path file
                'file_path' => 'uploads/' . date('Y/m') . '/' . Str::slug($data['title']) . '.' . $data['extension'],
                'file_size' => $data['file_size'] ?? fake()->numberBetween(500, 15000),
            ]);
        }
    }
}