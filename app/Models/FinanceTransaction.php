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
        'journal_id',
        'type',             
        'date',
        'cash_coa_id',      
        'category_coa_id',  
        'amount',
        'description',
        'proof_path',
        'fund_type',        
        'is_opening_balance'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'is_opening_balance' => 'boolean',
    ];

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

    // [PERBAIKAN] Kembali ke nama 'cashCoa' sesuai kode lama Anda
    public function cashCoa()
    {
        return $this->belongsTo(FinanceCoa::class, 'cash_coa_id');
    }

    // [PERBAIKAN] Kembali ke nama 'categoryCoa' sesuai kode lama Anda
    public function categoryCoa()
    {
        return $this->belongsTo(FinanceCoa::class, 'category_coa_id');
    }
}