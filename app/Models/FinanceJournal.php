<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceJournal extends Model
{
    use HasFactory;

    protected $table = 'finance_journals';
    protected $guarded = ['id'];

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

    // Relasi ke Rincian Debit/Kredit
    public function details()
    {
        return $this->hasMany(FinanceJournalDetail::class, 'journal_id');
    }

    // Relasi ke Transaksi UI (Jika berasal dari input user)
    public function transaction()
    {
        return $this->hasOne(FinanceTransaction::class, 'journal_id');
    }
}