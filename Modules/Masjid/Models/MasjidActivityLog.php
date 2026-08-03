<?php

namespace Modules\Masjid\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasjidActivityLog extends Model
{
    use HasFactory, BelongsToCompany;

    public $timestamps = false;
    const UPDATED_AT = null;

    protected $table = 'masjid_activity_logs';

    protected $fillable = [
        'company_id', 'mosque_id', 'user_id', 'action',
        'subject_type', 'subject_id', 'properties', 'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];
}