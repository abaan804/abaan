<?php

namespace Modules\Masjid\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\BelongsToMosque;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class MasjidSeason extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, BelongsToMosque;

    protected $table = 'masjid_seasons';

    protected $fillable = [
        'company_id', 'mosque_id', 'name', 'start_date', 'end_date',
        'contribution_amount', 'description', 'frequency', 'status',
        'auto_assign', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contribution_amount' => 'decimal:2',
        'auto_assign' => 'boolean',
    ];

    public function mosque(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidMosque::class, 'mosque_id');
    }

    public function seasonMembers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidSeasonMember::class, 'season_id');
    }

    public function members(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(MasjidMember::class, 'masjid_season_members', 'season_id', 'member_id')
            ->withPivot(['amount_due', 'amount_paid', 'status'])
            ->withTimestamps();
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidPayment::class, 'season_id');
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Total collected for this season across all payments.
     */
    public function totalCollected(): float
    {
        return round((float) $this->payments()->sum('amount_paid'), 2);
    }

    /**
     * Total due across all assigned members.
     */
    public function totalDue(): float
    {
        return round((float) $this->seasonMembers()->sum('amount_due'), 2);
    }

    /**
     * Total outstanding (due - collected).
     */
    public function totalOutstanding(): float
    {
        return round($this->totalDue() - $this->totalCollected(), 2);
    }
}