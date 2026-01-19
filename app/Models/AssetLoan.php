<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLoan extends Model
{
    use HasFactory;

    // Pastikan semua kolom bisa diisi (terutama member_id)
    protected $guarded = ['id'];

    // Relasi ke Aset
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    // Relasi ke Member (Peminjam Internal - INI YANG HILANG)
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    // Relasi ke Admin yang menyetujui (Approver tetap User sistem)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    // Relasi User (Legacy/Opsional jika masih ada kode lama yang pakai user)
    // Sebaiknya dihapus jika sudah full migrasi ke Member
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}