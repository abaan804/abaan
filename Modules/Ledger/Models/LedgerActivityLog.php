<?php

namespace Modules\Ledger\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerActivityLog extends Model
{
    use HasFactory, BelongsToCompany;

    public $timestamps = false;
    const UPDATED_AT = null;

    protected $table = 'ledger_activity_logs';

    protected $fillable = [
        'company_id', 'user_id', 'action', 'subject_type', 'subject_id',
        'properties', 'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];
}