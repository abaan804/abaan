<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageModule extends Model
{
    protected $table = 'package_modules';

    protected $fillable = ['package_id', 'module_definition_id'];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function moduleDefinition(): BelongsTo
    {
        return $this->belongsTo(ModuleDefinition::class);
    }
}