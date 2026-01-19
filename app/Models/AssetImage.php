<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetImage extends Model
{
    use HasFactory;

    protected $table = 'asset_images';
    
    protected $guarded = ['id'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    // Helper untuk mendapatkan URL lengkap gambar
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }
}