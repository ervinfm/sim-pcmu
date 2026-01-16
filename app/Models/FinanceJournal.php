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
        'journal_number',   
        'transaction_date',
        'reference',        
        'description',      
        'total_amount',     
        'status',           // [BARU] POSTED, DRAFT, VOID
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

    public function details()
    {
        return $this->hasMany(FinanceJournalDetail::class, 'journal_id');
    }

    public function transaction()
    {
        return $this->hasOne(FinanceTransaction::class, 'journal_id');
    }
}