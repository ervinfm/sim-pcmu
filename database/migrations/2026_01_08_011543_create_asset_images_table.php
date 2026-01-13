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
        Schema::create('asset_images', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Aset Induk
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            
            // Lokasi File Foto (Disimpan di folder Public agar bisa dilihat di web)
            $table->string('image_path');
            
            // Keterangan foto (Misal: "Tampak Depan", "Tampak Samping")
            $table->string('caption')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_images');
    }
};
