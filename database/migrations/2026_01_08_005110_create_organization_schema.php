<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABEL UTAMA: ORGANIZATION UNITS (Wadah Organisasi)
        Schema::create('organization_units', function (Blueprint $table) {
            $table->id();
            
            // Cluster: STRUKTURAL (Pimpinan), AUM (Amal Usaha), ORTOM (Otonom)
            $table->enum('category', ['STRUKTURAL', 'AUM', 'ORTOM'])->default('STRUKTURAL')->index();
            
            // Tipe Detail
            $table->enum('type', [
                'PCM', 'PRM', 
                'TK', 'SD', 'SMP', 'SMA', 'SMK', 'PONPES', 'MASJID', 'MUSHOLA', 'KLINIK', 'PANTI', 'LAZISMU',
                'AISYIYAH', 'PEMUDA', 'NA', 'IPM', 'IMM', 'HW', 'TAPAK_SUCI',
                'LAINNYA'
            ])->default('LAINNYA');

            $table->string('name');
            $table->string('code')->nullable();
            
            // Self Join (Parent -> Child)
            $table->foreignId('parent_id')->nullable()->constrained('organization_units')->nullOnDelete();

            // Data Umum
            $table->string('sk_number')->nullable();
            $table->date('establishment_date')->nullable();
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            // GIS
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            
            // Branding
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable(); 

            $table->timestamps();
        });

        // 2. TABEL WILAYAH: ORGANIZATION TERRITORIES (Ex-Villages)
        // Mencatat wilayah administratif yang menjadi tanggung jawab Unit (Biasanya PRM)
        Schema::create('organization_territories', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('organization_unit_id')
                  ->constrained('organization_units')
                  ->onDelete('cascade'); // Jika PRM hapus, wilayahnya hilang

            $table->string('name'); // Nama Desa/Kelurahan
            $table->string('responsibility')->nullable();
            $table->string('additional_information')->nullable();
            $table->string('postal_code')->nullable();
            
            $table->timestamps();
        });

        // 3. TABEL PENGURUS: ORGANIZATION STRUCTURES
        // Mencatat Siapa (Member) menjabat Apa di Unit Mana
        Schema::create('organization_structures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_unit_id')
                  ->constrained('organization_units')
                  ->onDelete('cascade');

            $table->foreignId('member_id')->index();
            $table->string('position_name'); // Ketua, Sekretaris, dll
            $table->string('position_type')->default('PIMPINAN_HARIAN'); // PH, MAJELIS, LEMBAGA
            
            $table->string('sk_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Hapus urutan terbalik dari dependensi
        Schema::dropIfExists('organization_structures');
        Schema::dropIfExists('organization_territories');
        Schema::dropIfExists('organization_units');
    }
};