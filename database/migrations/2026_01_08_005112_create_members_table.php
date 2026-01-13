<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            // --- RELASI (RELATIONSHIPS) ---
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->foreignId('organization_unit_id')
                  ->constrained('organization_units')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // --- DATA PRIBADI ---
            $table->string('nbm')->nullable()->unique();
            $table->string('nik')->nullable();
            $table->string('full_name');
            $table->enum('gender', ['L', 'P']);
            $table->string('birth_place');
            $table->date('birth_date');
            $table->string('photo_path')->nullable();

            // --- KONTAK & ALAMAT ---
            $table->string('phone_number')->nullable();
            $table->text('address');
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('regency')->nullable();

            // --- PENDIDIKAN & PEKERJAAN ---
            $table->enum('last_education', [
                'SD', 'SMP', 'SMA', 'DIPLOMA', 'S1', 'S2', 'S3', 'TIDAK_SEKOLAH'
            ])->default('SMA');
            $table->string('education_institution')->nullable();
            $table->string('job')->nullable(); 
            
            $table->boolean('is_aum_employee')->default(false);
            $table->string('aum_workplace')->nullable();

            // --- KEAKTIFAN ORGANISASI ---
            $table->json('active_ortoms')->nullable(); 
            $table->string('muhammadiyah_position')->nullable();
            $table->text('other_org_position')->nullable();

            // --- PERKADERAN ---
            $table->boolean('has_training')->default(false);
            $table->json('training_history')->nullable();

            // --- STATUS KEANGGOTAAN (BARU) ---
            // ACTIVE: Aktif
            // INACTIVE: Tidak Aktif / Pasif
            // MOVED: Pindah Wilayah/Cabang
            // DECEASED: Meninggal Dunia
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'MOVED', 'DECEASED'])
                  ->default('ACTIVE');

            $table->timestamps();
        });

        Schema::table('organization_structures', function (Blueprint $table) {
            $table->foreign('member_id')
                  ->references('id')
                  ->on('members')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};