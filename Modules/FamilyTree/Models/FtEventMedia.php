<?php

namespace Modules\FamilyTree\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FtEventMedia extends Model
{
    use HasFactory;

    protected $table = 'ft_event_media';

    protected $fillable = [
        'event_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_type',
        'size',
        'caption',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function event(): BelongsTo
    {
        return $this->belongsTo(FtEvent::class, 'event_id');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getIsImageAttribute(): bool
    {
        return $this->file_type === 'image';
    }

    public function getIsDocumentAttribute(): bool
    {
        return $this->file_type === 'document';
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        if (! $this->size) return '—';
        if ($this->size < 1024) return "{$this->size} B";
        if ($this->size < 1048576) return round($this->size / 1024, 1) . ' KB';
        return round($this->size / 1048576, 1) . ' MB';
    }
}