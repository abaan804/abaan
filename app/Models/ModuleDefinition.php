<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'name_en', 'name_ur', 'name_ar',
        'description_en', 'description_ur', 'description_ar',
        'icon', 'status', 'sort_order',
    ];

    public function companyModules(): HasMany
    {
        return $this->hasMany(CompanyModule::class);
    }

    public function translated(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"{$field}_{$locale}"} ?? $this->{"{$field}_en"};
    }
}