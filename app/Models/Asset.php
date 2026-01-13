<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_value' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    // Relasi ke Pemilik (Unit)
    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    // Relasi ke Banyak Foto
    public function images()
    {
        return $this->hasMany(AssetImage::class);
    }

    // Relasi ke Banyak Dokumen
    public function documents()
    {
        return $this->hasMany(AssetDocument::class);
    }
}