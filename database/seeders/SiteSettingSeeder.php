<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        // KITA GUNAKAN PREFIX AGAR MUDAH DIKELOMPOKKAN
        $settings = [
            // --- A. IDENTITAS APLIKASI & ORGANISASI ---
            ['key' => 'app_name', 'label' => 'Nama Aplikasi', 'value' => 'SIM-PCMU', 'type' => 'TEXT'],
            ['key' => 'app_logo', 'label' => 'Logo Aplikasi', 'value' => null, 'type' => 'IMAGE'],
            ['key' => 'org_official_name', 'label' => 'Nama Resmi Organisasi', 'value' => 'Pimpinan Cabang Muhammadiyah Muara Aman', 'type' => 'TEXT'],
            ['key' => 'org_sk_number', 'label' => 'Nomor SK Pendirian', 'value' => '-', 'type' => 'TEXT'],
            ['key' => 'org_establishment_year', 'label' => 'Tahun Berdiri', 'value' => '1912', 'type' => 'TEXT'],
            ['key' => 'org_address', 'label' => 'Alamat Kantor Lengkap', 'value' => 'Jl. KHA Dahlan...', 'type' => 'TEXTAREA'],

            // --- B. LANDING PAGE: HERO & HEADER ---
            ['key' => 'landing_banner_image', 'label' => 'Banner/Hero Image', 'value' => null, 'type' => 'IMAGE'],
            ['key' => 'landing_title', 'label' => 'Tulisan Utama (Headline)', 'value' => 'Mencerahkan Semesta', 'type' => 'TEXT'],
            ['key' => 'landing_subtitle', 'label' => 'Sub Tulisan Utama', 'value' => 'Portal Resmi Pimpinan Cabang Muhammadiyah Muara Aman', 'type' => 'TEXT'],
            ['key' => 'landing_show_clock', 'label' => 'Tampilkan Jam Digital', 'value' => '1', 'type' => 'BOOLEAN'], // 1 = Aktif

            // --- C. LANDING PAGE: SAMBUTAN KETUA ---
            ['key' => 'chairman_name', 'label' => 'Nama Ketua PCM', 'value' => 'H. Ahmad Dahlan', 'type' => 'TEXT'],
            ['key' => 'chairman_photo', 'label' => 'Foto Ketua', 'value' => null, 'type' => 'IMAGE'],
            ['key' => 'chairman_speech', 'label' => 'Kata Sambutan', 'value' => '<p>Assalamualaikum...</p>', 'type' => 'HTML'],

            // --- D. PROFIL ORGANISASI (SEJARAH & VISI MISI) ---
            ['key' => 'org_history', 'label' => 'Sejarah Perjalanan', 'value' => '<p>Sejarah singkat...</p>', 'type' => 'HTML'],
            ['key' => 'org_vision', 'label' => 'Visi Organisasi', 'value' => '<p>Visi...</p>', 'type' => 'HTML'],
            ['key' => 'org_mission', 'label' => 'Misi Organisasi', 'value' => '<p>Misi...</p>', 'type' => 'HTML'],

            // --- E. KONTAK & FOOTER ---
            ['key' => 'contact_email', 'label' => 'Email Resmi', 'value' => 'info@pcmuaraaman.or.id', 'type' => 'TEXT'],
            ['key' => 'contact_phone', 'label' => 'No Telepon / WA', 'value' => '08123456789', 'type' => 'TEXT'],
            ['key' => 'social_facebook', 'label' => 'Link Facebook', 'value' => 'https://facebook.com', 'type' => 'TEXT'],
            ['key' => 'social_instagram', 'label' => 'Link Instagram', 'value' => 'https://instagram.com', 'type' => 'TEXT'],
            ['key' => 'social_youtube', 'label' => 'Link Youtube', 'value' => 'https://youtube.com', 'type' => 'TEXT'],
            ['key' => 'footer_text', 'label' => 'Tulisan Footer (Motto)', 'value' => 'Amar Ma\'ruf Nahi Mungkar', 'type' => 'TEXTAREA'],

            // --- F. PENGATURAN SISTEM (INTERNAL) ---
            ['key' => 'system_maintenance_mode', 'label' => 'Mode Maintenance (Hanya Admin bisa login)', 'value' => '0', 'type' => 'BOOLEAN'],
            ['key' => 'system_privacy_policy', 'label' => 'Kebijakan Privasi', 'value' => '-', 'type' => 'HTML'],
            ['key' => 'system_help_content', 'label' => 'Bantuan Penggunaan', 'value' => '-', 'type' => 'HTML'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}