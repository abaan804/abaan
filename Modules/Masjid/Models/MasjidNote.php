<?php

namespace Modules\Masjid\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class MasjidNote extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $table = 'masjid_notes';

    protected $fillable = [
        'company_id', 'mosque_id', 'type',
        'season_id', 'title', 'content',
        'color', 'is_pinned',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public const COLORS = [
        'default' => 'light',
        'warning' => 'warning',
        'danger'  => 'danger',
        'success' => 'success',
        'info'    => 'info',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function mosque(): BelongsTo
    {
        return $this->belongsTo(MasjidMosque::class, 'mosque_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(MasjidSeason::class, 'season_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getBadgeClassAttribute(): string
    {
        return 'bg-' . (self::COLORS[$this->color] ?? 'light');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePinned(Builder $q): Builder
    {
        return $q->where('is_pinned', true);
    }

    public function scopeGeneral(Builder $q): Builder
    {
        return $q->where('type', 'general');
    }

    public function scopeSeason(Builder $q): Builder
    {
        return $q->where('type', 'season');
    }
}