<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'document_date' => 'date',
    ];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }
}