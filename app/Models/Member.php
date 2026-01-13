<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $guarded = ['id']; 

    protected $casts = [
        'birth_date' => 'date',
        'is_aum_employee' => 'boolean',
        'has_training' => 'boolean',
        'active_ortoms' => 'array',    
        'training_history' => 'array', 
    ];

    // --- RELASI ---

    public function organizationUnit(){
        return $this->belongsTo(OrganizationUnit::class, 'organization_unit_id');
    }

    // 1. Relasi ke Tempat Asal (Basis)
    public function homeBase() {
        return $this->belongsTo(OrganizationUnit::class, 'organization_unit_id');
    }

    // 2. Relasi ke Jabatan/Peran (Dimana saja dia aktif?)
    public function assignments() {
        return $this->hasMany(OrganizationStructure::class);
    }

    // 3. Relasi Helper: Ambil Unit-unit dimana dia aktif
    public function affiliatedUnits() {
        return $this->belongsToMany(OrganizationUnit::class, 'organization_structures', 'member_id', 'organization_unit_id')
            ->withPivot('position_name', 'position_type');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Helper untuk Cek Status (Opsional, mempermudah di view nanti)
    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }
}