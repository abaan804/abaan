<?php

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerTransactionAttachment extends Model
{
    use HasFactory;

    protected $table = 'ledger_transaction_attachments';

    protected $fillable = [
        'ledger_transaction_id', 'file_path', 'original_name', 'mime_type', 'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function transaction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LedgerTransaction::class, 'ledger_transaction_id');
    }
}