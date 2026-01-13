<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;
    
    // Kita matikan timestamps karena setting jarang berubah dan tidak butuh created_at
    public $timestamps = false; 

    protected $fillable = ['key', 'value', 'type', 'label'];
}