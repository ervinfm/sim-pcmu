<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'published_at' => 'datetime',
        'event_date_start' => 'datetime', // Casting DateTime
        'event_date_end' => 'datetime',
    ];

    // Relasi User & Unit
    public function author() { return $this->belongsTo(User::class, 'user_id'); }
    public function organizationUnit() { return $this->belongsTo(OrganizationUnit::class); }

    // Relasi Kategori (Ke PostCategory)
    public function category() { return $this->belongsTo(PostCategory::class, 'category_id'); }

    // Relasi Lampiran
    public function attachments() { return $this->hasMany(PostAttachment::class); }

    // Relasi Galeri Foto
    public function galleries() { return $this->hasMany(PostGallery::class); }
}