<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleRequest extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'module_definition_id', 'requested_by', 'note', 'status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function moduleDefinition(): BelongsTo
    {
        return $this->belongsTo(ModuleDefinition::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}