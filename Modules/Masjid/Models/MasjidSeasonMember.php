<?php

namespace Modules\Masjid\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\BelongsToMosque;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasjidSeasonMember extends Model
{
    use HasFactory, BelongsToCompany, BelongsToMosque;

    protected $table = 'masjid_season_members';

    protected $fillable = [
        'company_id', 'mosque_id', 'season_id', 'member_id',
        'amount_due', 'amount_paid', 'status',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    /**
     * Status constants for use across services and views.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERPAID = 'overpaid';

    /**
     * Recalculate and persist status + amount_paid from raw payments.
     * Called by MasjidPaymentService after every payment create/update/delete.
     */
    public function recalculate(): void
    {
        $totalPaid = $this->payments()->sum('amount_paid');
        $amountDue = (float) $this->amount_due;

        $status = match (true) {
            $totalPaid <= 0 => self::STATUS_PENDING,
            $totalPaid >= $amountDue => $totalPaid > $amountDue ? self::STATUS_OVERPAID : self::STATUS_PAID,
            default => self::STATUS_PARTIAL,
        };

        $this->update(['amount_paid' => $totalPaid, 'status' => $status]);
    }

    /**
     * Shortcut helpers used by views and reports.
     */
    public function balance(): float
    {
        return round((float) $this->amount_due - (float) $this->amount_paid, 2);
    }

    public function isOverpaid(): bool
    {
        return (float) $this->amount_paid > (float) $this->amount_due;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID || $this->status === self::STATUS_OVERPAID;
    }

    public function season(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidSeason::class, 'season_id');
    }

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidMember::class, 'member_id');
    }

    public function mosque(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidMosque::class, 'mosque_id');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidPayment::class, 'season_member_id');
    }
}