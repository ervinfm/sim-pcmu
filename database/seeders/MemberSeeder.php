<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\OrganizationUnit;
use Faker\Factory as Faker;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $prmIds = OrganizationUnit::where('type', 'PRM')->pluck('id')->toArray();

        if (empty($prmIds)) {
            $this->command->error('⚠️ Tabel organization_units kosong. Jalankan OrganizationSeeder dulu!');
            return;
        }

        $educations = ['SD', 'SMP', 'SMA', 'DIPLOMA', 'S1', 'S2', 'S3', 'TIDAK_SEKOLAH'];
        $ortoms = ['Pemuda Muhammadiyah', 'Nasyiatul Aisyiyah', 'IPM', 'IMM', 'Tapak Suci', 'Hizbul Wathan'];
        $trainings = ['Baitul Arqam Dasar', 'Baitul Arqam Madya', 'LID', 'LIP', 'Darul Arqam'];
        $jobs = ['Guru', 'Petani', 'Wiraswasta', 'PNS', 'Dosen', 'Pedagang', 'Dokter', 'Karyawan Swasta'];
        
        // Opsi Status
        $statuses = ['ACTIVE', 'ACTIVE', 'ACTIVE', 'INACTIVE', 'MOVED', 'DECEASED'];

        for ($i = 0; $i < 50; $i++) { // Saya naikkan jadi 50 data agar grafik lebih terlihat
            
            $gender = $faker->randomElement(['L', 'P']);
            $firstName = $gender === 'L' ? $faker->firstNameMale : $faker->firstNameFemale;
            $fullName = $firstName . ' ' . $faker->lastName;

            $isAumEmployee = $faker->boolean(40); // 40% pegawai AUM
            $aumWorkplace = $isAumEmployee ? 'SD Muhammadiyah ' . $faker->randomDigitNotNull : null;

            $hasTraining = $faker->boolean(60); // 60% sudah training
            $trainingHistory = $hasTraining ? $faker->randomElements($trainings, rand(1, 3)) : null;

            Member::create([
                'user_id' => null,
                'organization_unit_id' => $faker->randomElement($prmIds),

                // Data Pribadi
                'nbm' => $faker->unique()->numberBetween(1000000, 1400000),
                'nik' => $faker->unique()->nik($gender === 'P' ? 'female' : 'male'),
                'full_name' => $fullName,
                'gender' => $gender,
                'birth_place' => $faker->city,
                'birth_date' => $faker->dateTimeBetween('-70 years', '-20 years')->format('Y-m-d'),
                'photo_path' => null,

                // Kontak
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->streetAddress,
                'village' => $faker->streetName,
                'district' => $faker->citySuffix,
                'regency' => 'Lebong',

                // Pekerjaan & Pendidikan
                'last_education' => $faker->randomElement($educations),
                'education_institution' => 'Universitas ' . $faker->city,
                'job' => $faker->randomElement($jobs),
                'is_aum_employee' => $isAumEmployee,
                'aum_workplace' => $aumWorkplace,

                // Organisasi
                'active_ortoms' => $faker->randomElements($ortoms, rand(0, 2)),
                'muhammadiyah_position' => $faker->randomElement([
                    // --- Pimpinan Harian (PCM & PRM) ---
                    'Ketua',
                    'Wakil Ketua',
                    'Sekretaris',
                    'Wakil Sekretaris',
                    'Bendahara',
                    'Wakil Bendahara',
                    
                    // --- Unsur Pembantu Pimpinan (Majelis & Lembaga di Cabang) ---
                    'Ketua Majelis',
                    'Sekretaris Majelis',
                    'Anggota Majelis',
                    'Ketua Lembaga',
                    'Anggota Lembaga',
                    
                    // --- Amal Usaha Muhammadiyah (AUM - Sekolah/Madrasah) ---
                    'Kepala Sekolah',
                    'Wakil Kepala Sekolah',
                    'Kepala Madrasah',
                    'Guru',
                    'Kepala Tata Usaha',
                    'Staf Tata Usaha',
                    'Penjaga Sekolah',
                    
                    // --- Amal Usaha Muhammadiyah (AUM - Masjid/Musholla) ---
                    'Ketua Takmir',
                    'Imam Masjid',
                    'Marbot',
                    
                    // --- Amal Usaha Muhammadiyah (AUM - Kesehatan/Klinik/Panti) ---
                    'Direktur',
                    'Kepala Panti',
                    'Dokter',
                    'Perawat',
                    
                    // --- Umum ---
                    'Anggota',
                    'Simpatisan'
                ]),
                'other_org_position' => null,

                // Perkaderan
                'has_training' => $hasTraining,
                'training_history' => $trainingHistory,

                // STATUS (Updated)
                // Memilih status secara acak tapi lebih banyak yg ACTIVE
                'status' => $faker->randomElement($statuses), 
            ]);
        }
    }
}