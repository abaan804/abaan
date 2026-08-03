<?php

namespace Modules\Ledger\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerReminder extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'ledger_reminders';

    protected $fillable = [
        'company_id', 'customer_id', 'supplier_id', 'title', 'due_date',
        'amount', 'status', 'channel',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LedgerCustomer::class, 'customer_id');
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LedgerSupplier::class, 'supplier_id');
    }

    public function notificationLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LedgerNotificationLog::class, 'ledger_reminder_id');
    }
}