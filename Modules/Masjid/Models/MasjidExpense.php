<?php

namespace Modules\Masjid\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class MasjidExpense extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $table = 'masjid_expenses';

    protected $fillable = [
        'company_id', 'mosque_id', 'category',
        'title', 'amount', 'expense_date',
        'paid_to', 'receipt_no', 'season_id',
        'attachment', 'notes', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    public const CATEGORIES = [
        'maintenance'  => 'Maintenance',
        'electricity'  => 'Electricity',
        'water'        => 'Water',
        'salary'       => 'Salary',
        'renovation'   => 'Renovation',
        'supplies'     => 'Supplies',
        'event'        => 'Event',
        'other'        => 'Other',
    ];

    public const CATEGORY_ICONS = [
        'maintenance'  => 'bi-tools',
        'electricity'  => 'bi-lightning-charge',
        'water'        => 'bi-droplet',
        'salary'       => 'bi-person-badge',
        'renovation'   => 'bi-building-gear',
        'supplies'     => 'bi-box-seam',
        'event'        => 'bi-calendar-event',
        'other'        => 'bi-three-dots',
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

    public function getCategoryLabelAttribute(): string
    {
        return __(self::CATEGORIES[$this->category] ?? ucfirst($this->category));
    }

    public function getCategoryIconAttribute(): string
    {
        return self::CATEGORY_ICONS[$this->category] ?? 'bi-three-dots';
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment ? asset('storage/' . $this->attachment) : null;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForSeason(Builder $q, int $seasonId): Builder
    {
        return $q->where('season_id', $seasonId);
    }

    public function scopeForCategory(Builder $q, string $category): Builder
    {
        return $q->where('category', $category);
    }

    public function scopeDateFrom(Builder $q, string $date): Builder
    {
        return $q->whereDate('expense_date', '>=', $date);
    }

    public function scopeDateTo(Builder $q, string $date): Builder
    {
        return $q->whereDate('expense_date', '<=', $date);
    }
}