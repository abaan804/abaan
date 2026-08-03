<?php

namespace Modules\Ledger\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerCustomer extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $table = 'ledger_customers';

    protected $fillable = [
        'company_id', 'name', 'photo', 'mobile', 'email', 'address', 'city',
        'opening_balance', 'notes', 'status', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
    ];

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LedgerTransaction::class, 'customer_id');
    }

    public function reminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LedgerReminder::class, 'customer_id');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
    
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}