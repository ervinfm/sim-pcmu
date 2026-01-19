<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLocation extends Model
{
    use HasFactory;

    protected $table = 'asset_locations';

    protected $fillable = ['organization_unit_id', 'name', 'description'];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'asset_location_id');
    }
}