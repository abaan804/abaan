<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id', 'feature_key', 'feature_label_en',
        'feature_label_ur', 'feature_label_ar', 'value', 'sort_order',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}