<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. TABEL SATUAN (Global Reference)
        Schema::create('asset_units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // PCS, UNIT, SET
            $table->string('name'); 
            $table->timestamps();
        });

        // 2. TABEL LOKASI (Per Unit Organisasi)
        Schema::create('asset_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->string('name'); 
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. TABEL UTAMA ASET
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Pencatat
            
            $table->string('inventory_code')->unique(); // INV/001/2024/0001
            $table->string('name');
            $table->string('category'); // LAND, BUILDING, VEHICLE, ELECTRONIC...
            
            $table->foreignId('asset_unit_id')->constrained('asset_units');
            $table->foreignId('asset_location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            
            $table->date('acquisition_date'); // Tgl Perolehan
            $table->decimal('acquisition_value', 15, 2); // Nilai Perolehan
            $table->decimal('current_value', 15, 2)->nullable(); // Nilai Penyusutan (Opsional)
            
            $table->string('source_of_acquisition'); // PURCHASE, GRANT, WAKAF
            $table->string('condition'); // GOOD, BROKEN, LOST
            $table->string('status')->default('ACTIVE'); // ACTIVE, WRITE_OFF, AUCTION
            
            $table->json('specifications')->nullable(); // Flexible Field (Merk, Seri, Luas, dll)
            $table->text('description')->nullable(); // Keterangan tambahan
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. TABEL FOTO ASET
        Schema::create('asset_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('image_path');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // 5. TABEL DOKUMEN ASET (Sertifikat, BPKB, Faktur)
        Schema::create('asset_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('name');
            $table->string('file_path');
            $table->timestamps();
        });

        // 6. TABEL PEMINJAMAN (LOAN / SIRKULASI)
        Schema::create('asset_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            
            // --- BAGIAN INI YANG DIPERBAIKI ---
            // 1. Relasi ke Member (Internal)
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            
            // 2. Kolom Tipe Peminjam (WAJIB ADA)
            $table->string('borrower_type')->default('INTERNAL'); // INTERNAL / EXTERNAL
            
            // 3. Info Peminjam Eksternal
            $table->string('borrower_name')->nullable();
            $table->string('borrower_contact')->nullable();
            
            // Tanggal & Kondisi
            $table->date('loan_date');
            $table->date('return_date_plan');
            $table->date('return_date_actual')->nullable();
            
            $table->string('condition_before')->default('GOOD');
            $table->string('condition_after')->nullable();
            
            $table->string('status')->default('PENDING'); 
            $table->text('description')->nullable();
            
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_loans');
        Schema::dropIfExists('asset_documents');
        Schema::dropIfExists('asset_images');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_locations');
        Schema::dropIfExists('asset_units');
    }
};