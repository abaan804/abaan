<?php

namespace Modules\FamilyTree\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FtMarriage extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $table = 'ft_marriages';

    protected $fillable = [
        'company_id',
        'husband_id',
        'wife_id',
        'marriage_date',
        'marriage_place',
        'marriage_type',
        'status',
        'divorce_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'marriage_date' => 'date',
        'divorce_date' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function husband(): BelongsTo
    {
        return $this->belongsTo(FtMember::class, 'husband_id');
    }

    public function wife(): BelongsTo
    {
        return $this->belongsTo(FtMember::class, 'wife_id');
    }

    /**
     * Children born from this specific marriage —
     * members whose father_id = husband_id AND mother_id = wife_id.
     */
    public function children(): HasMany
    {
        return $this->hasMany(FtMember::class, 'father_id', 'husband_id')
            ->where('mother_id', $this->wife_id);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * Return the "other" member relative to a given member.
     * Useful in tree rendering where you know one spouse and need the other.
     */
    public function getSpouseOf(int $memberId): ?FtMember
    {
        if ($this->husband_id === $memberId) return $this->wife;
        if ($this->wife_id === $memberId) return $this->husband;
        return null;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getDurationAttribute(): ?string
    {
        if (! $this->marriage_date) return null;
        $end = $this->divorce_date ?? ($this->status === 'widowed' ? now() : now());
        return $this->marriage_date->diffInYears($end) . ' years';
    }
}