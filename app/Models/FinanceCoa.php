<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FinanceCoa extends Model
{
    use HasFactory;

    protected $table = 'finance_coas';
    
    protected $fillable = [
        'organization_unit_id',
        'code',
        'name',
        'type',       // ASSET, LIABILITY, EQUITY, REVENUE, EXPENSE
        'parent_id',
        'is_cash',    // true jika ini Akun Kas/Bank
        'is_active',
    ];

    protected $casts = [
        'is_cash' => 'boolean',
        'is_active' => 'boolean',
    ];

    // --- RELATIONSHIPS ---

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function parent()
    {
        return $this->belongsTo(FinanceCoa::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(FinanceCoa::class, 'parent_id');
    }

    // Untuk Laporan: Ambil semua jurnal yang menggunakan akun ini
    public function journalDetails()
    {
        return $this->hasMany(FinanceJournalDetail::class, 'coa_id');
    }

    // --- SCOPES (Helper Query) ---

    // Ambil hanya yang aktif
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    // Ambil hanya akun kas/bank
    public function scopeCash(Builder $query)
    {
        return $query->where('is_cash', true);
    }

    // Ambil akun yang boleh diakses oleh Unit ini (Global + Unit Sendiri)
    public function scopeForUnit(Builder $query, $unitId)
    {
        return $query->where(function($q) use ($unitId) {
            $q->whereNull('organization_unit_id') // Global
              ->orWhere('organization_unit_id', $unitId); // Lokal
        });
    }
}