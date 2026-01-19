<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetUnit extends Model
{
    use HasFactory;

    protected $table = 'asset_units';

    protected $fillable = ['code', 'name'];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'asset_unit_id');
    }
}