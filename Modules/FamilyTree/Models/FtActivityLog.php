<?php

namespace Modules\FamilyTree\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FtActivityLog extends Model
{
    use HasFactory, BelongsToCompany;

    public $timestamps = false;
    const UPDATED_AT = null;

    protected $table = 'ft_activity_logs';

    protected $fillable = [
        'company_id',
        'family_id',
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'properties',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function family(): BelongsTo
    {
        return $this->belongsTo(FtFamily::class, 'family_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}