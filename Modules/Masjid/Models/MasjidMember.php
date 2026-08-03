<?php

namespace Modules\Masjid\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\BelongsToMosque;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class MasjidMember extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, BelongsToMosque;

    protected $table = 'masjid_members';

    protected $fillable = [
        'company_id', 'mosque_id', 'name', 'father_name', 'cnic',
        'mobile', 'whatsapp', 'email', 'address', 'occupation',
        'joining_date', 'status', 'notes', 'photo',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'joining_date' => 'date',
    ];

    public function mosque(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidMosque::class, 'mosque_id');
    }

    public function seasonMembers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidSeasonMember::class, 'member_id');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidPayment::class, 'member_id');
    }

    public function notificationLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidNotificationLog::class, 'member_id');
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}