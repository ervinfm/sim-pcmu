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
        // Prefix: asset_
        Schema::create('asset_units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // PCS, UNIT, SET, M2
            $table->string('name'); // Pieces, Unit, Set, Meter Persegi
            $table->timestamps();
        });

        // 2. TABEL LOKASI (Per Unit Organisasi)
        // Prefix: asset_
        Schema::create('asset_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            
            $table->string('name'); // Contoh: Gudang Utama, Ruang Guru, Masjid Al-Huda
            $table->text('description')->nullable();
            
            $table->timestamps();
        });

        // 3. TABEL UTAMA ASET
        // Nama: assets (Plural standar Laravel untuk model Asset)
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            // A. Identitas Pemilik
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // PIC / Pencatat

            // B. Referensi Pendukung (Relasi ke tabel di atas)
            $table->foreignId('asset_location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            $table->foreignId('asset_unit_id')->nullable()->constrained('asset_units')->nullOnDelete();

            // C. Identitas Barang
            // Format: INV/[UNIT]/[TAHUN]/[NO_URUT]
            $table->string('inventory_code')->unique(); 
            $table->string('name'); 
            
            // D. Kategorisasi Logic
            // Value: LAND, BUILDING, VEHICLE, ELECTRONIC, FURNITURE, MACHINERY, OTHER
            $table->string('category')->index(); 

            // E. Spesifikasi Dinamis (JSON)
            // Menyimpan: Luas (Tanah), Nopol (Kendaraan), Processor (Laptop)
            $table->json('specifications')->nullable();

            // F. Valuasi & Sumber
            $table->date('acquisition_date'); 
            $table->decimal('acquisition_value', 15, 2)->default(0); // Harga Beli
            $table->decimal('current_value', 15, 2)->nullable(); // Nilai Buku (Penyusutan)
            
            // Sumber: PURCHASE (Beli), WAKAF (Wakaf), GRANT (Hibah), GOV_AID (Bantuan Pemerintah)
            $table->string('source_of_acquisition')->default('PURCHASE'); 
            $table->integer('useful_life_years')->nullable(); // Masa manfaat (tahun)

            // G. Status & Kondisi
            // Condition: GOOD, SLIGHTLY_DAMAGED (Rusak Ringan), HEAVILY_DAMAGED (Rusak Berat), LOST
            $table->string('condition')->default('GOOD');
            // Status: ACTIVE, BORROWED, MAINTENANCE, WRITE_OFF (Dihapuskan), SOLD
            $table->string('status')->default('ACTIVE');
            
            $table->text('description')->nullable(); 

            $table->timestamps();
            $table->softDeletes(); // Fitur tong sampah (Restoreable)
        });

        // 4. TABEL FOTO ASET (Gallery)
        // Prefix: asset_
        Schema::create('asset_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            
            $table->string('image_path'); // Path file
            $table->boolean('is_primary')->default(false); // Foto sampul
            $table->string('caption')->nullable(); // Tampak Depan, Samping, dsb
            
            $table->timestamps();
        });

        // 5. TABEL DOKUMEN (Legalitas)
        // Prefix: asset_
        Schema::create('asset_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            
            $table->string('file_path');
            $table->string('name'); // BPKB, Sertifikat, Faktur
            $table->string('document_number')->nullable(); // No Seri Dokumen
            $table->date('expiry_date')->nullable(); // Masa Berlaku (STNK/Pajak)
            
            $table->timestamps();
        });

        // 6. TABEL PEMINJAMAN ASET (BARU: Fitur Peminjaman)
        // Prefix: asset_
        Schema::create('asset_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            
            // Peminjam: Bisa Internal (User) ATAU Eksternal (Warga)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); 
            $table->string('borrower_name')->nullable(); // Wajib diisi jika user_id NULL
            $table->string('borrower_contact')->nullable(); // No WA/HP
            
            // Tanggal
            $table->date('loan_date'); // Tgl Pinjam
            $table->date('return_date_plan'); // Rencana Kembali
            $table->date('return_date_actual')->nullable(); // Realisasi Kembali
            
            // Kondisi Barang (Audit Fisik)
            $table->string('condition_before')->default('GOOD'); // Kondisi saat keluar
            $table->string('condition_after')->nullable(); // Kondisi saat kembali
            
            // Status Workflow
            // PENDING (Menunggu ACC), APPROVED (Disetujui), BORROWED (Sedang Dipinjam), 
            // COMPLETED (Sudah Kembali), REJECTED (Ditolak), OVERDUE (Telat)
            $table->string('status')->default('PENDING'); 
            
            $table->text('description')->nullable(); // Keperluan: "Untuk Pengajian RT"
            
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Admin yang menyetujui
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Urutan Drop Penting: Anak dulu, baru Induk
        Schema::dropIfExists('asset_loans'); // Hapus Peminjaman dulu
        Schema::dropIfExists('asset_documents');
        Schema::dropIfExists('asset_images');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_locations');
        Schema::dropIfExists('asset_units');
    }
};