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
        // 1. TABEL KATEGORI (Perhatikan nama tabelnya: post_categories)
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('NEWS'); 
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. TABEL POSTINGAN UTAMA
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            
            // Relasi User & Organisasi
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('organization_unit_id')->nullable()->constrained('organization_units')->nullOnDelete();
            
            // [PERBAIKAN DISINI]: Referensi harus ke 'post_categories', bukan 'categories'
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('post_categories') // <--- SUDAH DIPERBAIKI
                  ->nullOnDelete();

            // Konten
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            
            // Media
            $table->string('thumbnail')->nullable();
            
            // Metadata Event
            $table->dateTime('event_date_start')->nullable();
            $table->dateTime('event_date_end')->nullable();
            $table->string('event_location')->nullable();

            // Status
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
            $table->timestamp('published_at')->nullable();
            
            // SEO & Views
            $table->string('meta_keywords')->nullable();
            $table->integer('views_count')->default(0);

            $table->timestamps();
        });

        // 3. TABEL LAMPIRAN FILE
        Schema::create('post_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->integer('download_count')->default(0);
            
            $table->timestamps();
        });

        // 4. TABEL GALERI FOTO
        Schema::create('post_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tabel harus berurutan (Child dulu baru Parent) agar tidak error foreign key
        Schema::dropIfExists('post_galleries');
        Schema::dropIfExists('post_attachments');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_categories');
    }
};