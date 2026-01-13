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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            
            // Kunci Unik (Identitas Setting)
            // Contoh: 'slider_1_image', 'contact_email', 'visi_misi'
            $table->string('key')->unique();
            
            // Nilai Setting (Bisa teks pendek, panjang, atau path gambar)
            $table->longText('value')->nullable();
            
            // Tipe Data (Membantu Frontend menentukan cara menampilkan)
            // TEXT = Input biasa, IMAGE = Upload file, HTML = Text Editor
            $table->string('type')->default('TEXT'); 
            
            // Label (Nama yang enak dibaca admin di form setting)
            $table->string('label')->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
