<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Archive extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'organization_unit_id',
        'user_id',
        'title',
        'slug',
        'description',
        'category',
        'type',
        'file_path',
        'file_extension',
        'file_size',
        'reference_number',
        'classification_code',
        'document_date',
        'received_date',
        'sender',
        'receiver',
        'confidentiality',
        'status',
        'download_count',
        'published_at',
        'qr_code_token',
        'meta_data',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'document_date' => 'date',
        'received_date' => 'date',
        'published_at'  => 'datetime',
        'meta_data'     => 'array', // Otomatis convert JSON ke Array
        'file_size'     => 'integer',
        'download_count' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Arsip ini milik Unit Organisasi mana.
     */
    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    /**
     * User yang mengupload arsip.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Riwayat disposisi surat/arsip ini.
     */
    public function dispositions(): HasMany
    {
        return $this->hasMany(ArchiveDisposition::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Query Helper)
    |--------------------------------------------------------------------------
    */

    /**
     * Filter hanya arsip yang statusnya 'published'.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Filter berdasarkan kategori tertentu.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}