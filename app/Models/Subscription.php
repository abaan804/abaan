<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    public const STATUS_TRIAL     = 'trial';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_id',
        'package_id',
        'status',
        'trial_started_at',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'price_paid',
        'billing_months',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'trial_started_at' => 'datetime',
        'trial_ends_at'    => 'datetime',
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
        'price_paid'       => 'decimal:2',
        'billing_months'   => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Status Checks ─────────────────────────────────────────────────────────

    public function isTrialActive(): bool
    {
        return $this->status === self::STATUS_TRIAL
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isTrialExpired(): bool
    {
        return $this->status === self::STATUS_TRIAL
            && $this->trial_ends_at
            && $this->trial_ends_at->isPast();
    }

    public function isPaidActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->ends_at
            && $this->ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->status === self::STATUS_ACTIVE
                && $this->ends_at
                && $this->ends_at->isPast());
    }

    /**
     * Whether the company can currently access the system.
     */
    public function isAccessAllowed(): bool
    {
        return $this->isTrialActive() || $this->isPaidActive();
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getTrialDaysRemainingAttribute(): int
    {
        if (! $this->isTrialActive()) return 0;
        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->isPaidActive()) {
            return max(0, (int) now()->diffInDays($this->ends_at, false));
        }
        if ($this->isTrialActive()) {
            return $this->trial_days_remaining;
        }
        return 0;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_TRIAL     => 'bg-info',
            self::STATUS_ACTIVE    => 'bg-success',
            self::STATUS_EXPIRED   => 'bg-danger',
            self::STATUS_CANCELLED => 'bg-secondary',
            default                => 'bg-secondary',
        };
    }
}