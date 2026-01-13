<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceCoa extends Model
{
    use HasFactory;

    protected $table = 'finance_coas';
    protected $guarded = ['id'];

    protected $casts = [
        'is_cash' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relasi ke Unit
    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    // Self-Join: Akun Induk (Parent)
    public function parent()
    {
        return $this->belongsTo(FinanceCoa::class, 'parent_id');
    }

    // Self-Join: Sub-Akun (Children)
    public function children()
    {
        return $this->hasMany(FinanceCoa::class, 'parent_id');
    }

    // Relasi ke Jurnal Detail (Untuk cek saldo/mutasi)
    public function journalDetails()
    {
        return $this->hasMany(FinanceJournalDetail::class, 'coa_id');
    }

    // Relasi ke Anggaran
    public function budgets()
    {
        return $this->hasMany(FinanceBudget::class, 'coa_id');
    }
}