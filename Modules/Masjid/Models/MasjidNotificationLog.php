<?php

namespace Modules\Masjid\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\BelongsToMosque;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasjidNotificationLog extends Model
{
    use HasFactory, BelongsToCompany, BelongsToMosque;

    protected $table = 'masjid_notification_logs';

    protected $fillable = [
        'company_id', 'mosque_id', 'member_id', 'channel', 'type',
        'status', 'payload', 'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidMember::class, 'member_id');
    }

    public function mosque(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidMosque::class, 'mosque_id');
    }
}