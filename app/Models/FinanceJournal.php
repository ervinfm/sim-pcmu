<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceJournal extends Model
{
    use HasFactory;

    protected $table = 'finance_journals';
    
    protected $fillable = [
        'organization_unit_id',
        'user_id',
        'journal_number',   // JU/2025/01/001
        'transaction_date',
        'reference',        // No Invoice / Keterangan Singkat
        'description',      // Keterangan Lengkap
        'total_amount',     // Checksum (Total Debit)
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi 1-to-Many ke Rincian Debit/Kredit
    public function details()
    {
        return $this->hasMany(FinanceJournalDetail::class, 'journal_id');
    }

    // Relasi balik ke Transaksi UI (Optional, jika jurnal ini hasil generate otomatis)
    public function transaction()
    {
        return $this->hasOne(FinanceTransaction::class, 'journal_id');
    }
}