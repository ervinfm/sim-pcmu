<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const UPDATED_AT = null;

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Helper untuk Icon (Bisa dipakai di API resource nanti)
    public function getIconAttribute()
    {
        return match($this->action) {
            'LOGIN' => 'pi pi-sign-in',
            'LOGOUT' => 'pi pi-sign-out',
            'UPDATE_ACCOUNT' => 'pi pi-user-edit',
            'UPDATE_PASSWORD' => 'pi pi-lock',
            default => 'pi pi-info-circle'
        };
    }
}