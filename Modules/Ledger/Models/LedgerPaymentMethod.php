<?php

namespace Modules\Ledger\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerPaymentMethod extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'ledger_payment_methods';

    protected $fillable = [
        'company_id', 'name', 'is_default', 'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LedgerTransaction::class, 'payment_method_id');
    }
}