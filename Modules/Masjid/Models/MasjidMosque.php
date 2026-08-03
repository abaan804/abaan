<?php

namespace Modules\Masjid\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Company;
use App\Models\User;

class MasjidMosque extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $table = 'masjid_mosques';

    protected $fillable = [
        'company_id', 'village_name', 'mosque_name', 'scholar_name',
        'scholar_contact', 'scholar_email', 'committee_name', 'mosque_contact',
        'address', 'city', 'province', 'country', 'postal_code',
        'map_link', 'description', 'logo', 'status',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function members(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidMember::class, 'mosque_id');
    }

    public function seasons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidSeason::class, 'mosque_id');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidPayment::class, 'mosque_id');
    }

    public function setting(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MasjidSetting::class, 'mosque_id');
    }

    public function notificationLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidNotificationLog::class, 'mosque_id');
    }

    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidActivityLog::class, 'mosque_id');
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}