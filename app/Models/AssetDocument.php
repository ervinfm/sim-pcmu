<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AssetDocument extends Model
{
    use HasFactory;

    protected $table = 'asset_documents';

    protected $guarded = ['id'];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    // Helper: Cek apakah dokumen sudah kadaluarsa (misal Pajak STNK)
    public function getIsExpiredAttribute()
    {
        if (!$this->expiry_date) return false;
        return Carbon::now()->gt($this->expiry_date);
    }

    // Helper: Cek apakah akan kadaluarsa dalam 30 hari (untuk Notifikasi)
    public function getIsExpiringSoonAttribute()
    {
        if (!$this->expiry_date) return false;
        return Carbon::now()->diffInDays($this->expiry_date, false) <= 30 && Carbon::now()->lte($this->expiry_date);
    }

    // Helper: URL Download
    public function getDownloadUrlAttribute()
    {
        // Gunakan route khusus jika file diproteksi, atau direct link jika public
        return asset('storage/' . $this->file_path);
    }
}