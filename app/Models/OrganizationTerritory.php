<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationTerritory extends Model
{
    protected $guarded = ['id'];

    // Relasi ke Unit (PRM)
    public function unit()
    {
        // Parameter ke-2 'organization_unit_id' wajib ada karena nama fungsi 'unit' beda dengan nama kolom
        return $this->belongsTo(OrganizationUnit::class, 'organization_unit_id');
    }
}