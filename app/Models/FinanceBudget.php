<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceBudget extends Model
{
    use HasFactory;

    protected $table = 'finance_budgets';
    
    protected $fillable = [
        'organization_unit_id',
        'coa_id',
        'fiscal_year', // Thn Anggaran (2025, 2026)
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fiscal_year' => 'integer',
    ];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function coa()
    {
        return $this->belongsTo(FinanceCoa::class, 'coa_id');
    }
}