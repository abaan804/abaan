<?php

namespace Modules\Masjid\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class MasjidDonation extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $table = 'masjid_donations';

    protected $fillable = [
        'company_id', 'mosque_id', 'type',
        'donor_name', 'donor_mobile', 'donor_address',
        'amount', 'donation_date', 'day_description',
        'purpose', 'season_id', 'receipt_no', 'notes',
        'received_by', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'donation_date' => 'date',
    ];

    public const TYPE_NAMED     = 'named';
    public const TYPE_ANONYMOUS = 'anonymous';

    public const TYPES = [
        self::TYPE_NAMED     => 'Named Donor',
        self::TYPE_ANONYMOUS => 'Anonymous',
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

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getDonorDisplayNameAttribute(): string
    {
        if ($this->type === self::TYPE_ANONYMOUS) {
            return __('Anonymous') . ($this->day_description ? ' (' . $this->day_description . ')' : '');
        }
        return $this->donor_name ?? __('Unknown');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeNamed(Builder $q): Builder
    {
        return $q->where('type', self::TYPE_NAMED);
    }

    public function scopeAnonymous(Builder $q): Builder
    {
        return $q->where('type', self::TYPE_ANONYMOUS);
    }

    public function scopeForSeason(Builder $q, int $seasonId): Builder
    {
        return $q->where('season_id', $seasonId);
    }

    public function scopeDateFrom(Builder $q, string $date): Builder
    {
        return $q->whereDate('donation_date', '>=', $date);
    }

    public function scopeDateTo(Builder $q, string $date): Builder
    {
        return $q->whereDate('donation_date', '<=', $date);
    }
}