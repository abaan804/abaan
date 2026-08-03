<?php

namespace Modules\Masjid\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasjidSetting extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'masjid_settings';

    protected $fillable = [
        'company_id', 'mosque_id', 'currency_symbol', 'currency_code',
        'currency_position', 'receipt_prefix', 'default_reminder_days',
        'notification_whatsapp', 'notification_sms', 'notification_email',
        'default_language',
    ];

    protected $casts = [
        'notification_whatsapp' => 'boolean',
        'notification_sms' => 'boolean',
        'notification_email' => 'boolean',
        'default_reminder_days' => 'integer',
    ];

    public function mosque(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidMosque::class, 'mosque_id');
    }
}