<?php

namespace Modules\Ledger\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerTransaction extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $table = 'ledger_transactions';

    protected $fillable = [
        'company_id', 'type', 'customer_id', 'supplier_id', 'category_id',
        'payment_method_id', 'amount', 'transaction_date', 'reference_no',
        'notes', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Transaction types that increase the customer/supplier's balance owed to the company.
     */
    public const TYPES_INCREASING = ['debit', 'opening_balance'];

    /**
     * Transaction types that decrease the customer/supplier's balance owed to the company.
     */
    public const TYPES_DECREASING = ['credit'];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LedgerCustomer::class, 'customer_id');
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LedgerSupplier::class, 'supplier_id');
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LedgerCategory::class, 'category_id');
    }

    public function paymentMethod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LedgerPaymentMethod::class, 'payment_method_id');
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LedgerTransactionAttachment::class, 'ledger_transaction_id');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}