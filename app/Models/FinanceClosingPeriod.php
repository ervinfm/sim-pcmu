<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceClosingPeriod extends Model
{
    use HasFactory;

    protected $table = 'finance_closing_periods';
    
    protected $fillable = [
        'organization_unit_id',
        'year',
        'month',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    // Relasi ke Unit
    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    // Relasi ke User yang menutup buku
    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}