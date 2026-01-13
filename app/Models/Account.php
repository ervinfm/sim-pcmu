<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke Unit Pemilik
    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    // Relasi ke Transaksi (Akun ini punya banyak transaksi)
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}