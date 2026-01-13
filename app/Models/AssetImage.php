<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetImage extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    
    // Nanti saat upload, kita simpan di 'storage/app/public/assets'
}