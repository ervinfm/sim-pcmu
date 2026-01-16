<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceJournalDetail extends Model
{
    use HasFactory;

    protected $table = 'finance_journal_details';
    
    protected $fillable = [
        'journal_id',
        'coa_id',
        'debit',
        'credit',
        'fund_type', // [BARU]
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function journal()
    {
        return $this->belongsTo(FinanceJournal::class, 'journal_id');
    }

    public function coa()
    {
        return $this->belongsTo(FinanceCoa::class, 'coa_id');
    }
}