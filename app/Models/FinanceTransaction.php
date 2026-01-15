<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceTransaction extends Model
{
    use HasFactory;

    protected $table = 'finance_transactions';
    
    protected $fillable = [
        'organization_unit_id',
        'user_id',
        'journal_id',       // Link ke Jurnal Akuntansi
        'type',             // INCOME, EXPENSE, TRANSFER
        'date',
        'cash_coa_id',      // Akun Kas/Bank
        'category_coa_id',  // Akun Lawan
        'amount',
        'description',
        'proof_path',       // Foto Bukti
        'is_opening_balance', // Flag Saldo Awal
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'is_opening_balance' => 'boolean',
    ];

    // --- RELATIONSHIPS ---

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journal()
    {
        return $this->belongsTo(FinanceJournal::class, 'journal_id');
    }

    // Relasi ke Akun Kas (Sumber Dana)
    public function cashCoa()
    {
        return $this->belongsTo(FinanceCoa::class, 'cash_coa_id');
    }

    // Relasi ke Akun Kategori (Lawan Transaksi)
    public function categoryCoa()
    {
        return $this->belongsTo(FinanceCoa::class, 'category_coa_id');
    }
}