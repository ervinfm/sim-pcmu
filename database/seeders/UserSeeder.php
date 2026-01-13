<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Password default untuk semua akun (biar gampang testing)
        $defaultPassword = Hash::make('password');

        // ---------------------------------------------------------
        // 1. SUPER ADMIN (PCM) - Level Tertinggi
        // ---------------------------------------------------------
        // Bisa akses semua menu (Anggota, Keuangan, Aset, Admin, Web)
        $pcm = OrganizationUnit::where('type', 'PCM')->first();
        
        if ($pcm) {
            User::create([
                'name' => 'Jefriyanto, SP.',
                'email' => 'admin@pcm.com',
                'password' => $defaultPassword,
                'role' => 'super_admin',
                'organization_unit_id' => $pcm->id,
                'is_active' => true,
            ]);
        }

    }
}