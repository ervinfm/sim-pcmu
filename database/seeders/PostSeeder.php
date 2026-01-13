<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostCategory;
use App\Models\Post;
use App\Models\User;
use App\Models\OrganizationUnit;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 1. SEED KATEGORI
        // Kita buat kategori standar untuk organisasi
        $categories = [
            [
                'name' => 'Berita Utama',
                'slug' => 'berita-utama',
                'type' => 'NEWS',
                'description' => 'Berita terkini seputar kegiatan pimpinan dan organisasi.'
            ],
            [
                'name' => 'Agenda Kegiatan',
                'slug' => 'agenda',
                'type' => 'AGENDA',
                'description' => 'Jadwal pengajian, rapat, dan kegiatan sosial.'
            ],
            [
                'name' => 'Artikel & Opini',
                'slug' => 'artikel',
                'type' => 'NEWS',
                'description' => 'Tulisan inspiratif dan opini kader.'
            ],
            [
                'name' => 'Pengumuman',
                'slug' => 'pengumuman',
                'type' => 'NEWS',
                'description' => 'Informasi penting dari pimpinan untuk warga.'
            ],
            [
                'name' => 'Arsip Dokumen',
                'slug' => 'arsip',
                'type' => 'ARCHIVE',
                'description' => 'Kumpulan SK, Edaran, dan Maklumat.'
            ],
        ];

        foreach ($categories as $cat) {
            PostCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Ambil ID kategori untuk dipakai di postingan
        $catNews = PostCategory::where('slug', 'berita-utama')->first()->id;
        $catAgenda = PostCategory::where('slug', 'agenda')->first()->id;
        $catArticle = PostCategory::where('slug', 'artikel')->first()->id;

        // Ambil User Super Admin (asumsi ID 1) & Unit-unit yang ada
        $superAdmin = User::where('role', 'super_admin')->first() ?? User::first();
        $units = OrganizationUnit::all();

        // 2. SEED POSTINGAN (DUMMY DATA)
        
        // A. Postingan dari Super Admin (Global/Pusat)
        for ($i = 0; $i < 5; $i++) {
            $title = $faker->sentence(6);
            Post::create([
                'user_id' => $superAdmin->id,
                'organization_unit_id' => null, // Global
                'category_id' => $catNews,
                'title' => $title,
                'slug' => Str::slug($title) . '-' . Str::random(4),
                'excerpt' => $faker->paragraph(2),
                'content' => '<p>' . implode('</p><p>', $faker->paragraphs(5)) . '</p>',
                'status' => 'PUBLISHED',
                'published_at' => now()->subDays(rand(1, 30)),
                'views_count' => rand(100, 5000)
            ]);
        }

        // B. Postingan Agenda (Ada Event Date)
        for ($i = 0; $i < 3; $i++) {
            $title = "Pengajian Akbar: " . $faker->sentence(3);
            $startDate = $faker->dateTimeBetween('now', '+1 month');
            
            Post::create([
                'user_id' => $superAdmin->id,
                'organization_unit_id' => null,
                'category_id' => $catAgenda,
                'title' => $title,
                'slug' => Str::slug($title) . '-' . Str::random(4),
                'excerpt' => 'Undangan terbuka untuk seluruh warga Muhammadiyah.',
                'content' => '<p>Detail acara pengajian rutin bulanan...</p>',
                'status' => 'PUBLISHED',
                'published_at' => now(),
                'event_date_start' => $startDate,
                'event_date_end' => (clone $startDate)->modify('+3 hours'),
                'event_location' => 'Masjid Raya ' . $faker->city
            ]);
        }

        // C. Postingan dari Unit-unit (PRM/AUM)
        if ($units->count() > 0) {
            foreach ($units->take(5) as $unit) { // Ambil 5 unit sampel
                // Buat user dummy utk unit ini jika belum ada (optional, pakai superadmin dulu gpp)
                // Disini kita pakai superadmin tapi set unit_id nya seolah-olah dari unit tsb
                
                for ($k = 0; $k < 2; $k++) {
                    $title = "Kegiatan di " . $unit->name . ": " . $faker->sentence(4);
                    Post::create([
                        'user_id' => $superAdmin->id, // Author tetap user yg ada
                        'organization_unit_id' => $unit->id, // Publisher UNIT
                        'category_id' => $catArticle,
                        'title' => $title,
                        'slug' => Str::slug($title) . '-' . Str::random(4),
                        'excerpt' => $faker->paragraph(1),
                        'content' => '<p>' . implode('</p><p>', $faker->paragraphs(3)) . '</p>',
                        'status' => 'PUBLISHED',
                        'published_at' => now()->subDays(rand(1, 60)),
                    ]);
                }
            }
        }
    }
}