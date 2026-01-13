<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username', // Baru
        'email',
        'password',
        'role', // Baru
        'organization_unit_id', // Baru
        'is_active', // Baru
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean', // Casting boolean agar enak di Vue
        ];
    }

    // --- RELASI ---
    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function member()
    {
        // User memiliki satu biodata Member
        return $this->hasOne(Member::class);
    }
}