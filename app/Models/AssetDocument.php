<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDocument extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    // Nanti saat upload, PENTING untuk simpan di 'storage/app/private_docs'
    // Jangan di folder public agar tidak bisa diakses orang lain via URL.
}