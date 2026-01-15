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
        // 1. TABEL CLOSING PERIODS (BARU: Untuk Fitur Tutup Buku)
        // Mencegah perubahan data pada periode yang sudah diaudit/dilaporkan.
        Schema::create('finance_closing_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')
                  ->nullable() // Nullable jika ingin menutup buku secara global (jarang, biasanya per unit)
                  ->constrained('organization_units')
                  ->onDelete('cascade');
            
            $table->integer('year');  // Contoh: 2025
            $table->integer('month'); // Contoh: 1 (Januari)
            
            $table->boolean('is_closed')->default(true); 
            $table->timestamp('closed_at')->nullable();
            
            // Siapa yang melakukan tutup buku
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();

            // Mencegah duplikasi: Satu unit hanya punya satu status untuk bulan tertentu
            $table->unique(['organization_unit_id', 'year', 'month'], 'closing_unique_idx');
        });

        // 2. TABEL CHART OF ACCOUNTS (COA)
        // Master Data Akun (Jantung Sistem)
        Schema::create('finance_coas', function (Blueprint $table) {
            $table->id();
            
            // Multi-Tenancy:
            // NULL = Akun Standar Pusat (Global)
            // Terisi = Akun Tambahan Unit
            $table->foreignId('organization_unit_id')
                  ->nullable()
                  ->constrained('organization_units')
                  ->onDelete('cascade');

            $table->string('code')->index(); // Kode Akun (1-100, 5-201)
            $table->string('name'); 
            
            // Persamaan Dasar Akuntansi
            $table->enum('type', ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'])->index();
            
            // Hierarki (Parent-Child)
            $table->foreignId('parent_id')->nullable()->constrained('finance_coas')->onDelete('cascade');
            
            // Helper Flags
            $table->boolean('is_cash')->default(false); // Penanda ini akun Kas/Bank (untuk opsi pembayaran)
            $table->boolean('is_active')->default(true); // Soft delete logic
            
            $table->timestamps();
        });

        // 3. TABEL BUDGETS (ANGGARAN)
        Schema::create('finance_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->foreignId('coa_id')->constrained('finance_coas')->onDelete('cascade');
            
            // [MODIFIKASI] Menambahkan Tahun Anggaran
            $table->year('fiscal_year'); 
            
            $table->decimal('amount', 15, 2); // Nilai Anggaran
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Satu akun hanya boleh punya satu anggaran per tahun per unit
            $table->unique(['organization_unit_id', 'coa_id', 'fiscal_year'], 'budget_unique_idx');
        });

        // 4. TABEL JOURNALS (HEADER)
        // Pembungkus Transaksi Akuntansi
        Schema::create('finance_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Pembuat Jurnal
            
            $table->string('journal_number')->unique(); // No Bukti (JU/2025/01/001)
            $table->date('transaction_date')->index();
            
            $table->string('reference')->nullable(); // No Invoice / Keterangan Singkat
            $table->text('description')->nullable();
            
            $table->decimal('total_amount', 15, 2)->default(0); // Checksum (Debit harus sama dengan Kredit)
            
            $table->timestamps();
        });

        // 5. TABEL JOURNAL DETAILS (LEDGER)
        // Rincian Debit Kredit (Double Entry)
        Schema::create('finance_journal_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('finance_journals')->onDelete('cascade');
            $table->foreignId('coa_id')->constrained('finance_coas')->onDelete('restrict'); // Jangan hapus COA jika ada jurnal
            
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            
            $table->timestamps();
        });

        // 6. TABEL TRANSACTIONS (USER INTERFACE / LOG)
        // Single Entry untuk kemudahan User
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Link ke Jurnal Akuntansi (1 Transaksi = 1 Jurnal Header)
            $table->foreignId('journal_id')->nullable()->constrained('finance_journals')->onDelete('set null');

            $table->enum('type', ['INCOME', 'EXPENSE', 'TRANSFER']);
            $table->date('date')->index();
            
            // Akun Sumber & Tujuan (Simplified)
            $table->foreignId('cash_coa_id')->constrained('finance_coas'); // Kas/Bank yang terlibat
            $table->foreignId('category_coa_id')->nullable()->constrained('finance_coas'); // Lawan (Pendapatan/Beban/Bank Tujuan)
            
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('proof_path')->nullable(); // Foto Bukti
            
            // [MODIFIKASI] Flag untuk Saldo Awal
            $table->boolean('is_opening_balance')->default(false); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Urutan drop dibalik agar tidak kena FK Constraint violation
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('finance_journal_details');
        Schema::dropIfExists('finance_journals');
        Schema::dropIfExists('finance_budgets');
        Schema::dropIfExists('finance_coas');
        Schema::dropIfExists('finance_closing_periods');
    }
};