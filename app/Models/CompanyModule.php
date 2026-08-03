<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyModule extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'module_definition_id', 'is_enabled', 'enabled_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'enabled_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function moduleDefinition(): BelongsTo
    {
        return $this->belongsTo(ModuleDefinition::class);
    }
}