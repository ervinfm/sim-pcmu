<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceTransaction extends Model
{
    use HasFactory;

    protected $table = 'finance_transactions';
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Jurnal Asli (Double Entry)
    public function journal()
    {
        return $this->belongsTo(FinanceJournal::class, 'journal_id');
    }

    // Akun Kas/Bank yang digunakan (Sumber Dana)
    public function cashCoa()
    {
        return $this->belongsTo(FinanceCoa::class, 'cash_coa_id');
    }

    // Akun Kategori (Pendapatan/Beban)
    public function categoryCoa()
    {
        return $this->belongsTo(FinanceCoa::class, 'category_coa_id');
    }
}