<?php

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerNotificationLog extends Model
{
    use HasFactory;

    protected $table = 'ledger_notification_logs';

    protected $fillable = [
        'ledger_reminder_id', 'channel', 'status', 'payload', 'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function reminder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LedgerReminder::class, 'ledger_reminder_id');
    }
}