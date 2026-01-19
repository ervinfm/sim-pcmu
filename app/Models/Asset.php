<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assets';

    protected $guarded = ['id'];

    protected $casts = [
        'specifications' => 'array', // JSON to Array
        'acquisition_date' => 'date',
        'acquisition_value' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    // ==========================================
    // RELASI
    // ==========================================

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    public function unit()
    {
        return $this->belongsTo(AssetUnit::class, 'asset_unit_id');
    }

    public function images()
    {
        return $this->hasMany(AssetImage::class);
    }

    public function documents()
    {
        return $this->hasMany(AssetDocument::class);
    }

    // [BARU] Relasi ke Riwayat Peminjaman
    public function loans()
    {
        return $this->hasMany(AssetLoan::class)->latest('loan_date');
    }

    // [BARU] Ambil Peminjaman Aktif Terakhir (Yang statusnya masih DIPINJAM)
    public function activeLoan()
    {
        return $this->hasOne(AssetLoan::class)
            ->whereIn('status', ['BORROWED', 'OVERDUE'])
            ->latestOfMany();
    }

    // ==========================================
    // HELPER
    // ==========================================

    public function getMainImageAttribute()
    {
        $primary = $this->images->where('is_primary', true)->first();
        if (!$primary) $primary = $this->images->first();
        return $primary ? asset('storage/' . $primary->image_path) : asset('images/asset-placeholder.png');
    }

    // Cek Ketersediaan untuk dipinjam
    public function getIsAvailableAttribute()
    {
        // 1. Cek Kondisi Fisik (Harus Baik)
        if ($this->condition !== 'GOOD') return false;

        // 2. Cek Status Admin (Harus Active)
        if ($this->status !== 'ACTIVE') return false;

        // 3. Cek apakah sedang dipinjam orang lain?
        // Jika ada activeLoan, berarti TIDAK Available
        if ($this->activeLoan()->exists()) return false;

        return true;
    }
}