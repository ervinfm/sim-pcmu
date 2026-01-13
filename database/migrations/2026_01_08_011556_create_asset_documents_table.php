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
        Schema::create('asset_documents', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            
            // Lokasi File Dokumen (Nanti disimpan di folder Private/Storage)
            $table->string('file_path');
            
            // Nama Dokumen (Misal: "Sertifikat SHM No. 12345")
            $table->string('name');
            
            // Jenis Dokumen (Sertifikat, BPKB, STNK, Faktur)
            $table->string('type')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_documents');
    }
};
