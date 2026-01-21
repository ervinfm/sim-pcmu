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
        // 1. TABEL UTAMA ARSIP (Dokumen Fisik & Digital)
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            
            // --- KEPEMILIKAN & RELASI ---
            // Unit Organisasi (Wajib, agar file tidak tercampur antar unit)
            $table->foreignId('organization_unit_id')
                  ->constrained('organization_units')
                  ->onDelete('cascade');
            
            // User Uploader
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // --- IDENTITAS UTAMA (General) ---
            $table->string('title'); // Judul Arsip / Perihal Surat
            $table->string('slug')->unique(); // URL Friendly (ex: surat-keputusan-2024)
            $table->text('description')->nullable(); // Ringkasan / Keterangan
            
            // Kategori (Legal, Keuangan, Surat Masuk, Event, dll). 
            // Kita gunakan string agar fleksibel menerima input dari Seeder.
            $table->string('category')->index()->nullable(); 

            // --- SPESIFIKASI FILE (General) ---
            // Tipe umum: document, image, video, audio, archive, code
            $table->string('type')->default('document')->index(); 
            $table->string('file_path'); // Path penyimpanan di storage
            $table->string('file_extension')->nullable(); // pdf, jpg, docx
            $table->bigInteger('file_size')->default(0); // Ukuran dalam KB/Byte
            
            // --- KHUSUS SURAT MENYURAT (Nullable - Hanya diisi jika tipe = document/surat) ---
            $table->string('reference_number')->nullable(); // Nomor Surat
            $table->string('classification_code')->nullable(); // Kode Klasifikasi (I.A/1.b)
            $table->date('document_date')->nullable(); // Tanggal di surat
            $table->date('received_date')->nullable(); // Tanggal terima (Surat Masuk)
            $table->string('sender')->nullable(); // Pengirim
            $table->string('receiver')->nullable(); // Tujuan
            
            // --- KEAMANAN & STATUS ---
            // Level akses
            $table->enum('confidentiality', ['BIASA', 'RAHASIA', 'SANGAT_RAHASIA'])
                  ->default('BIASA');
            
            // Status: published (tampil), draft (konsep), archived (lama), private (hidden)
            $table->string('status')->default('published')->index();

            // --- FITUR TAMBAHAN ---
            $table->integer('download_count')->default(0); // Statistik
            $table->timestamp('published_at')->nullable(); // Jadwal tayang
            $table->string('qr_code_token')->unique()->nullable(); // Validasi keaslian
            $table->json('meta_data')->nullable(); // Field custom tambahan

            $table->timestamps();
            $table->softDeletes();
        });

        // 2. TABEL DISPOSISI (ALUR SURAT)
        // Tidak banyak berubah, tetap diperlukan untuk fitur disposisi surat
        Schema::create('archive_dispositions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('archive_id')
                  ->constrained('archives')
                  ->onDelete('cascade');

            // Pengirim Disposisi (Atasan)
            $table->foreignId('sender_member_id')
                  ->nullable()
                  ->constrained('members')
                  ->onDelete('set null');

            // Penerima Disposisi (Bawahan / Unit Lain)
            $table->foreignId('receiver_member_id')
                  ->nullable()
                  ->constrained('members')
                  ->onDelete('set null');
            
            $table->foreignId('receiver_unit_id')
                  ->nullable()
                  ->constrained('organization_units')
                  ->onDelete('set null');

            // Instruksi
            $table->string('instruction'); // "Tindak Lanjuti", "Arsipkan"
            $table->text('note')->nullable();
            $table->date('due_date')->nullable();

            // Tracking
            $table->timestamp('read_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_dispositions');
        Schema::dropIfExists('archives');
    }
};