<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceBudget extends Model
{
    use HasFactory;

    protected $table = 'finance_budgets';
    protected $guarded = ['id'];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function coa()
    {
        return $this->belongsTo(FinanceCoa::class, 'coa_id');
    }
}