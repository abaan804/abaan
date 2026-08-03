<?php

namespace Modules\Masjid\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\BelongsToMosque;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class MasjidPayment extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, BelongsToMosque;

    protected $table = 'masjid_payments';

    protected $fillable = [
        'company_id', 'mosque_id', 'member_id', 'season_id', 'season_member_id',
        'payment_date', 'amount_paid', 'payment_method', 'reference_no',
        'receipt_no', 'notes', 'received_by', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount_paid' => 'decimal:2',
    ];

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidMember::class, 'member_id');
    }

    public function season(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidSeason::class, 'season_id');
    }

    public function seasonMember(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidSeasonMember::class, 'season_member_id');
    }

    public function mosque(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidMosque::class, 'mosque_id');
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasjidPaymentAttachment::class, 'payment_id');
    }

    public function receivedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}