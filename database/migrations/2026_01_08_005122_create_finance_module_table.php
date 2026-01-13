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
        // 1. TABEL CHART OF ACCOUNTS (COA) - PENGGANTI KATEGORI & AKUN
        // Ini adalah "Jantung" sistem akuntansi.
        Schema::create('finance_coas', function (Blueprint $table) {
            $table->id();
            
            // Milik siapa? Jika NULL = Standar Baku dari Pusat (Global)
            // Jika terisi = Akun tambahan khusus Unit tersebut
            $table->foreignId('organization_unit_id')
                  ->nullable()
                  ->constrained('organization_units')
                  ->onDelete('cascade');

            $table->string('code')->index(); // Kode Akun (Misal: 1-100, 4-200)
            $table->string('name'); // Nama Akun (Misal: Kas Tunai, Penerimaan Infaq)
            
            // Tipe Akun Dasar (Accounting Equation)
            // ASSET (Harta), LIABILITY (Utang), EQUITY (Modal/Aset Neto), 
            // REVENUE (Penerimaan), EXPENSE (Pengeluaran)
            $table->enum('type', ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'])->index();
            
            // Hierarchy (Sub-Akun)
            $table->foreignId('parent_id')->nullable()->constrained('finance_coas')->onDelete('cascade');
            
            // Flag Khusus untuk UX Frontend
            $table->boolean('is_cash')->default(false); // Apakah ini akun Kas/Bank? (Muncul di pilihan "Sumber Dana")
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });

        // 2. TABEL ANGGARAN (RAPB)
        // Untuk fitur perencanaan & kontrol (Budgeting)
        Schema::create('finance_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->foreignId('coa_id')->constrained('finance_coas')->onDelete('cascade'); // Akun apa yang dianggarkan?
            
            $table->year('fiscal_year'); // Tahun Anggaran (2026)
            $table->decimal('amount_planned', 15, 2)->default(0); // Rencana Anggaran
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });

        // 3. TABEL JURNAL (HEADER) - BACKEND CORE
        // Mencatat setiap kejadian akuntansi (Double Entry Parent)
        Schema::create('finance_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Siapa yang input?
            
            $table->date('transaction_date')->index();
            $table->string('reference_no')->nullable(); // No Bukti / Invoice
            $table->text('description')->nullable();
            
            $table->enum('status', ['DRAFT', 'POSTED'])->default('POSTED');
            $table->decimal('total_amount', 15, 2)->default(0); // Nilai transaksi (untuk display cepat)
            
            $table->timestamps();
        });

        // 4. TABEL JURNAL DETAIL (LINES) - BACKEND CORE
        // Rincian Debit & Kredit
        Schema::create('finance_journal_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('finance_journals')->onDelete('cascade');
            $table->foreignId('coa_id')->constrained('finance_coas')->onDelete('restrict'); // Jangan hapus akun jika sudah ada jurnal
            
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            
            // FUND ACCOUNTING (ISAK 35)
            // UNRESTRICTED: Dana Bebas (Operasional)
            // TEMPORARY_RESTRICTED: Dana Terikat Sementara (Bangunan, Kegiatan Tertentu)
            // PERMANENT_RESTRICTED: Dana Abadi (Wakaf Uang)
            $table->enum('fund_type', ['UNRESTRICTED', 'TEMPORARY_RESTRICTED', 'PERMANENT_RESTRICTED'])
                  ->default('UNRESTRICTED');

            $table->timestamps();
        });

        // 5. TABEL TRANSAKSI HARIAN (FRONTEND LAYER)
        // Tabel ini menyederhanakan input user (Single Entry) sebelum dikonversi ke Jurnal
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Link ke Jurnal (Agar sinkron)
            $table->foreignId('journal_id')->nullable()->constrained('finance_journals')->onDelete('set null');

            $table->enum('type', ['INCOME', 'EXPENSE', 'TRANSFER']); // Jenis Transaksi UI
            $table->date('date');
            
            // Logic Hybrid:
            // Jika INCOME: Cash/Bank (Debit) vs Revenue Category (Kredit)
            // Jika EXPENSE: Expense Category (Debit) vs Cash/Bank (Kredit)
            $table->foreignId('cash_coa_id')->constrained('finance_coas'); // Akun Kas/Bank
            $table->foreignId('category_coa_id')->nullable()->constrained('finance_coas'); // Akun Lawan (Pendapatan/Beban)
            
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('proof_path')->nullable(); // Foto Struk/Nota
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop urutan terbalik
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('finance_journal_details');
        Schema::dropIfExists('finance_journals');
        Schema::dropIfExists('finance_budgets');
        Schema::dropIfExists('finance_coas');
    }
};