<?php

namespace Modules\FamilyTree\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class FtEvent extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $table = 'ft_events';

    protected $fillable = [
        'company_id',
        'family_id',
        'member_id',
        'event_type',
        'event_title',
        'event_date',
        'location',
        'description',
        'status',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    /**
     * Human-readable event type labels.
     */
    public const TYPE_LABELS = [
        'birth' => 'Birth',
        'bismillah' => 'Bismillah Ceremony',
        'school_admission' => 'School Admission',
        'graduation' => 'Graduation',
        'hifz' => 'Hifz Completion',
        'marriage' => 'Marriage',
        'job_started' => 'Job Started',
        'business_started' => 'Business Started',
        'migration' => 'Migration',
        'house_purchased' => 'House Purchased',
        'award' => 'Award Received',
        'retirement' => 'Retirement',
        'death' => 'Death',
        'custom' => 'Custom Event',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function member(): BelongsTo
    {
        return $this->belongsTo(FtMember::class, 'member_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(FtFamily::class, 'family_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(FtEventMedia::class, 'event_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(FtEventMedia::class, 'event_id')
            ->where('file_type', 'image');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(FtEventMedia::class, 'event_id')
            ->where('file_type', 'document');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * Display title — uses event_title for custom type,
     * otherwise uses the standard label from TYPE_LABELS.
     */
    public function getDisplayTitleAttribute(): string
    {
        if ($this->event_type === 'custom' && $this->event_title) {
            return $this->event_title;
        }

        return __(self::TYPE_LABELS[$this->event_type] ?? ucfirst($this->event_type));
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }

    public function scopeRecent(Builder $query, int $limit = 10): Builder
    {
        return $query->orderByDesc('event_date')->limit($limit);
    }
}