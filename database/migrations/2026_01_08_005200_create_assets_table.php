<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            // 1. Kepemilikan & Identitas
            $table->foreignId('organization_unit_id')->constrained()->onDelete('cascade');
            
            // PENTING: Ini sumber data untuk Barcode/QR Code
            // Contoh format: INV/PCM/2026/001
            $table->string('inventory_code')->unique(); 
            
            $table->string('name'); // Nama Barang (Misal: Mobil Ambulans)
            
            // 2. Detail Aset
            // Kategori: TANAH, BANGUNAN, KENDARAAN, ELEKTRONIK, PERABOT, LAINNYA
            $table->string('category'); 
            
            // 3. Valuasi
            $table->date('acquisition_date'); // Tanggal Perolehan
            $table->decimal('acquisition_value', 15, 2); // Harga Beli
            $table->decimal('current_value', 15, 2)->nullable(); // Taksiran Harga Sekarang (Penyusutan)
            
            // 4. Kondisi
            $table->enum('condition', ['BAIK', 'RUSAK_RINGAN', 'RUSAK_BERAT','HILANG', 'PEMUSNAHAN']);
            $table->string('location')->nullable(); // Lokasi Fisik (Misal: Gudang Belakang / Ruang Ketua)
            $table->text('description')->nullable(); // Keterangan tambahan

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};