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
use Illuminate\Support\Collection;

class FtMember extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $table = 'ft_members';

    protected $fillable = [
        'company_id',
        'family_id',
        'full_name',
        'gender',
        'date_of_birth',
        'place_of_birth',
        'date_of_death',
        'burial_place',
        'life_status',
        'father_id',
        'mother_id',
        'father_name_text',
        'mother_name_text',
        'cnic',
        'passport_number',
        'contact_number',
        'whatsapp_number',
        'email',
        'current_address',
        'permanent_address',
        'occupation',
        'education',
        'blood_group',
        'religion',
        'nationality',
        'marital_status',
        'profile_photo',
        'other_details',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_death' => 'date',
    ];

    // ─── Core Tree Relationships ──────────────────────────────────────────────

    /**
     * Biological father — direct parent link.
     */
    public function father(): BelongsTo
    {
        return $this->belongsTo(FtMember::class, 'father_id');
    }

    /**
     * Biological mother — direct parent link.
     */
    public function mother(): BelongsTo
    {
        return $this->belongsTo(FtMember::class, 'mother_id');
    }

    /**
     * All children of this member — anyone whose father_id OR mother_id is this member.
     * Returns a merged unique collection.
     * Note: Two separate relations are defined (childrenAsFather, childrenAsMother)
     * and merged in the children() accessor to avoid a complex OR query on the relation level.
     */
    public function childrenAsFather(): HasMany
    {
        return $this->hasMany(FtMember::class, 'father_id');
    }

    public function childrenAsMother(): HasMany
    {
        return $this->hasMany(FtMember::class, 'mother_id');
    }

    /**
     * Sons only (children with gender = male).
     */
    public function sons(): HasMany
    {
        return $this->hasMany(FtMember::class, 'father_id')
            ->where('gender', 'male');
    }

    /**
     * Daughters only (children with gender = female).
     */
    public function daughters(): HasMany
    {
        return $this->hasMany(FtMember::class, 'father_id')
            ->where('gender', 'female');
    }

    // ─── Marriage Relationships ───────────────────────────────────────────────

    /**
     * Marriages where this member is the husband.
     */
    public function husbandMarriages(): HasMany
    {
        return $this->hasMany(FtMarriage::class, 'husband_id');
    }

    /**
     * Marriages where this member is the wife.
     */
    public function wifeMarriages(): HasMany
    {
        return $this->hasMany(FtMarriage::class, 'wife_id');
    }

    /**
     * Active marriages only.
     */
    public function activeHusbandMarriages(): HasMany
    {
        return $this->hasMany(FtMarriage::class, 'husband_id')
            ->where('status', 'active');
    }

    public function activeWifeMarriages(): HasMany
    {
        return $this->hasMany(FtMarriage::class, 'wife_id')
            ->where('status', 'active');
    }

    // ─── Explicit Relationships ───────────────────────────────────────────────

    public function relationships(): HasMany
    {
        return $this->hasMany(FtRelationship::class, 'member_id');
    }

    public function relatedToMe(): HasMany
    {
        return $this->hasMany(FtRelationship::class, 'related_member_id');
    }

    // ─── Events & Documents ───────────────────────────────────────────────────

    public function events(): HasMany
    {
        return $this->hasMany(FtEvent::class, 'member_id')
            ->orderBy('event_date');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(FtDocument::class, 'member_id');
    }

    // ─── Family ───────────────────────────────────────────────────────────────

    public function family(): BelongsTo
    {
        return $this->belongsTo(FtFamily::class, 'family_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Computed Relationship Helpers ────────────────────────────────────────

    /**
     * All children regardless of which parent role this member plays.
     * Merges childrenAsFather + childrenAsMother into one unique collection.
     */
    public function children(): Collection
    {
        return $this->childrenAsFather
            ->merge($this->childrenAsMother)
            ->unique('id')
            ->values();
    }

    /**
     * All spouses — loads from both marriage tables and returns the
     * linked spouse member for each, regardless of husband/wife role.
     */
    public function spouses(): Collection
    {
        $asHusband = $this->husbandMarriages->map(fn ($m) => $m->wife)->filter();
        $asWife = $this->wifeMarriages->map(fn ($m) => $m->husband)->filter();

        return $asHusband->merge($asWife)->unique('id')->values();
    }

    /**
     * Current/active spouses only.
     */
    public function activeSpouses(): Collection
    {
        $asHusband = $this->activeHusbandMarriages->map(fn ($m) => $m->wife)->filter();
        $asWife = $this->activeWifeMarriages->map(fn ($m) => $m->husband)->filter();

        return $asHusband->merge($asWife)->unique('id')->values();
    }

    /**
     * Siblings — members sharing at least one parent with this member.
     * Does NOT include half-siblings who share only the other parent.
     * Full and half siblings are both returned — filtered in service if needed.
     */
    public function siblings(): Collection
    {
        $siblings = collect();

        if ($this->father_id) {
            $siblings = $siblings->merge(
                static::where('father_id', $this->father_id)
                    ->where('id', '!=', $this->id)
                    ->get()
            );
        }

        if ($this->mother_id) {
            $siblings = $siblings->merge(
                static::where('mother_id', $this->mother_id)
                    ->where('id', '!=', $this->id)
                    ->get()
            );
        }

        return $siblings->unique('id')->values();
    }

    /**
     * Brothers only.
     */
    public function brothers(): Collection
    {
        return $this->siblings()->filter(fn ($s) => $s->gender === 'male')->values();
    }

    /**
     * Sisters only.
     */
    public function sisters(): Collection
    {
        return $this->siblings()->filter(fn ($s) => $s->gender === 'female')->values();
    }

    /**
     * Display name for father — linked record name takes priority over text fallback.
     */
    public function getFatherDisplayNameAttribute(): string
    {
        return $this->father?->full_name ?? $this->father_name_text ?? '—';
    }

    /**
     * Display name for mother — linked record name takes priority over text fallback.
     */
    public function getMotherDisplayNameAttribute(): string
    {
        return $this->mother?->full_name ?? $this->mother_name_text ?? '—';
    }

    /**
     * Computed age from date_of_birth.
     * Returns null if DOB not set.
     * Uses date_of_death as end point for deceased members.
     */
    public function getAgeAttribute(): ?int
    {
        if (! $this->date_of_birth) return null;

        $end = ($this->life_status === 'deceased' && $this->date_of_death)
            ? $this->date_of_death
            : now();

        return $this->date_of_birth->diffInYears($end);
    }

    /**
     * Whether this member is a root (has no linked parents in the system).
     */
    public function getIsRootAttribute(): bool
    {
        return is_null($this->father_id) && is_null($this->mother_id);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeLiving(Builder $query): Builder
    {
        return $query->where('life_status', 'living');
    }

    public function scopeDeceased(Builder $query): Builder
    {
        return $query->where('life_status', 'deceased');
    }

    public function scopeMale(Builder $query): Builder
    {
        return $query->where('gender', 'male');
    }

    public function scopeFemale(Builder $query): Builder
    {
        return $query->where('gender', 'female');
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('father_id')->whereNull('mother_id');
    }

    public function scopeForFamily(Builder $query, int $familyId): Builder
    {
        return $query->where('family_id', $familyId);
    }

    public function scopeUpcomingBirthdays(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('date_of_birth')
            ->where('life_status', 'living')
            ->whereRaw(
                'DATE_FORMAT(date_of_birth, "%m-%d") BETWEEN DATE_FORMAT(NOW(), "%m-%d") AND DATE_FORMAT(DATE_ADD(NOW(), INTERVAL ? DAY), "%m-%d")',
                [$days]
            );
    }
}