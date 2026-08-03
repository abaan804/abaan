<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_enabled', 'duration_days', 'applies_to_package_id',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'applies_to_package_id');
    }

    /**
     * Resolve the effective trial setting for a given package:
     * package-specific override takes priority over the global row.
     */
    public static function resolveFor(?int $packageId = null): ?self
    {
        if ($packageId) {
            $override = static::where('applies_to_package_id', $packageId)->first();
            if ($override) {
                return $override;
            }
        }

        return static::whereNull('applies_to_package_id')->first();
    }
}