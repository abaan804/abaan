<?php

namespace Modules\FamilyTree\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FtFamily extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $table = 'ft_families';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'village',
        'city',
        'district',
        'province',
        'country',
        'address',
        'photo',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function members(): HasMany
    {
        return $this->hasMany(FtMember::class, 'family_id');
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(FtMember::class, 'family_id')
            ->where('life_status', 'living');
    }

    public function events(): HasMany
    {
        return $this->hasMany(FtEvent::class, 'family_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(FtActivityLog::class, 'family_id');
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

    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }

    public function getLivingCountAttribute(): int
    {
        return $this->members()->where('life_status', 'living')->count();
    }

    public function getDeceasedCountAttribute(): int
    {
        return $this->members()->where('life_status', 'deceased')->count();
    }
}