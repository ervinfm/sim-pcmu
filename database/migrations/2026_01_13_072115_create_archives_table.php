<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            
            // Multi-tenancy
            $table->foreignId('organization_unit_id')
                  ->constrained('organization_units')
                  ->onDelete('cascade');

            $table->enum('category', ['SURAT_MASUK', 'SURAT_KELUAR', 'SK', 'LAINNYA']);
            $table->string('reference_number'); // Nomor Surat / No SK
            $table->string('title'); // Perihal
            $table->date('document_date');
            
            $table->string('sender')->nullable(); // Pengirim (untuk Surat Masuk)
            $table->string('receiver')->nullable(); // Penerima (untuk Surat Keluar)
            
            $table->string('file_path')->nullable(); // Lokasi File PDF/Gambar
            $table->text('description')->nullable(); // Ringkasan isi

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};