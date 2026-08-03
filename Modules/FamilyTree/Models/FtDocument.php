<?php

namespace Modules\FamilyTree\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FtDocument extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $table = 'ft_documents';

    protected $fillable = [
        'company_id',
        'member_id',
        'document_type',
        'title',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public const TYPE_LABELS = [
        'cnic' => 'CNIC',
        'birth_certificate' => 'Birth Certificate',
        'marriage_certificate' => 'Marriage Certificate',
        'educational' => 'Educational Certificate',
        'passport' => 'Passport',
        'photo' => 'Photo',
        'other' => 'Other',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function member(): BelongsTo
    {
        return $this->belongsTo(FtMember::class, 'member_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getTypeDisplayAttribute(): string
    {
        return __(self::TYPE_LABELS[$this->document_type] ?? ucfirst($this->document_type));
    }

    public function getIsPreviewableAttribute(): bool
    {
        return in_array(
            strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf']
        );
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

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('document_type', $type);
    }
}