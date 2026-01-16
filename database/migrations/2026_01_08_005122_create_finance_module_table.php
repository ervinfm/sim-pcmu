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
        // 1. TABEL CLOSING PERIODS (Sub-Modul: Tutup Buku)
        Schema::create('finance_closing_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')
                  ->nullable()
                  ->constrained('organization_units')
                  ->onDelete('cascade');
            
            $table->integer('year');
            $table->integer('month');
            
            $table->boolean('is_closed')->default(true); 
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->unique(['organization_unit_id', 'year', 'month'], 'closing_unique_idx');
        });

        // 2. TABEL CHART OF ACCOUNTS (Sub-Modul: Master Akun)
        Schema::create('finance_coas', function (Blueprint $table) {
            $table->id();
            
            // Multi-Tenancy: NULL = Akun Standar Pusat (Global), Terisi = Akun Unit
            $table->foreignId('organization_unit_id')
                  ->nullable()
                  ->constrained('organization_units')
                  ->onDelete('cascade');

            $table->string('code')->index(); 
            $table->string('name'); 
            
            // Tipe Akun Dasar
            $table->enum('type', ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'])->index();
            
            // Hierarki (Parent-Child)
            $table->foreignId('parent_id')->nullable()->constrained('finance_coas')->onDelete('cascade');
            
            // Helper Flags
            $table->boolean('is_cash')->default(false); // Penanda Akun Kas/Bank
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });

        // 3. TABEL BUDGETS (Sub-Modul: Anggaran / RAPB)
        Schema::create('finance_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->foreignId('coa_id')->constrained('finance_coas')->onDelete('cascade');
            
            $table->year('fiscal_year'); 
            $table->decimal('amount', 15, 2); // Pagu Anggaran
            $table->text('description')->nullable();
            
            $table->timestamps();
            $table->unique(['organization_unit_id', 'coa_id', 'fiscal_year'], 'budget_unique_idx');
        });

        // 4. TABEL JOURNALS HEADER (Core Accounting)
        Schema::create('finance_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('journal_number')->unique(); // No Bukti (JU/2025/01/001)
            $table->date('transaction_date')->index();
            
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('POSTED'); // DRAFT, POSTED, VOID
            
            $table->timestamps();
        });

        // 5. TABEL JOURNAL DETAILS (Sub-Modul: Buku Besar)
        Schema::create('finance_journal_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('finance_journals')->onDelete('cascade');
            $table->foreignId('coa_id')->constrained('finance_coas')->onDelete('restrict');
            
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            
            // [MODIFIKASI PENTING] Klasifikasi Dana Terikat (Wakaf/Zakat) vs Bebas
            // UNRESTRICTED: Dana Operasional (Bebas)
            // RESTRICTED: Dana Terikat (Zakat, Infaq Khusus, Bencana)
            // ENDOWMENT: Dana Abadi (Wakaf Uang)
            $table->string('fund_type')->default('UNRESTRICTED')->index(); 
            
            $table->timestamps();
        });

        // 6. TABEL TRANSACTIONS (Sub-Modul: Transaksi / UI Input)
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained('organization_units')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->foreignId('journal_id')->nullable()->constrained('finance_journals')->onDelete('set null');

            $table->enum('type', ['INCOME', 'EXPENSE', 'TRANSFER']);
            $table->date('date')->index();
            
            $table->foreignId('cash_coa_id')->constrained('finance_coas'); 
            $table->foreignId('category_coa_id')->nullable()->constrained('finance_coas'); 
            
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('proof_path')->nullable(); 
            
            // [MODIFIKASI PENTING] Menyimpan pilihan user saat input
            $table->string('fund_type')->default('UNRESTRICTED');

            $table->boolean('is_opening_balance')->default(false); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('finance_journal_details');
        Schema::dropIfExists('finance_journals');
        Schema::dropIfExists('finance_budgets');
        Schema::dropIfExists('finance_coas');
        Schema::dropIfExists('finance_closing_periods');
    }
};