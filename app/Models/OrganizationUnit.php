<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrganizationUnit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'establishment_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // --- SCOPES (FILTER PINTASAN) ---
    public function scopeStruktural(Builder $query) { return $query->where('category', 'STRUKTURAL'); }
    public function scopeAum(Builder $query)        { return $query->where('category', 'AUM'); }
    public function scopeOrtom(Builder $query)      { return $query->where('category', 'ORTOM'); }

    // --- RELASI ---

    public function parent()
    {
        return $this->belongsTo(OrganizationUnit::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(OrganizationUnit::class, 'parent_id');
    }

    // Update: Nama relasi disesuaikan dengan tabel territories
    public function territories()
    {
        return $this->hasMany(OrganizationTerritory::class);
    }

    // Relasi ke Pengurus (PENTING: Tambahan ini yang belum ada di file lama)
    public function structures()
    {
        return $this->hasMany(OrganizationStructure::class);
    }

    // Helper untuk mengambil pimpinan aktif saja
    public function currentStructures()
    {
        return $this->hasMany(OrganizationStructure::class)->where('is_active', true);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }
    
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    // --- HELPER ATTRIBUTE ---
    public function getCategoryColorAttribute()
    {
        return match($this->category) {
            'STRUKTURAL' => 'success', // Hijau
            'AUM' => 'info', // Biru
            'ORTOM' => 'warning', // Kuning/Orange
            default => 'secondary'
        };
    }
}