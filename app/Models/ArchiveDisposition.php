<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchiveDisposition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'archive_id',
        'sender_member_id',
        'receiver_member_id',
        'receiver_unit_id',
        'instruction',
        'note',
        'due_date',
        'read_at',
        'completed_at',
        'completion_note',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'due_date'     => 'date',
        'read_at'      => 'datetime',
        'completed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Arsip yang didisposisikan.
     */
    public function archive(): BelongsTo
    {
        return $this->belongsTo(Archive::class);
    }

    /**
     * Anggota/Pejabat yang MENGIRIM disposisi (Atasan).
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'sender_member_id');
    }

    /**
     * Anggota/Pejabat yang MENERIMA disposisi (Bawahan/Staff).
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'receiver_member_id');
    }

    /**
     * Jika disposisi ditujukan ke UNIT lain, bukan orang spesifik.
     */
    public function receiverUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class, 'receiver_unit_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Cek apakah disposisi sudah selesai.
     */
    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }

    /**
     * Cek apakah disposisi sudah dibaca.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }
}