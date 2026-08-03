<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_en',
        'monthly_price',
        'trial_days',
        'description',
        'is_active',
        'max_users',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'trial_days'    => 'integer',
        'is_active'     => 'boolean',
        'max_users'     => 'integer',
        'sort_order'    => 'integer',
    ];

    public function features(): HasMany
    {
        return $this->hasMany(PackageFeature::class);
    }


    public function trialSettingOverride(): HasMany
    {
        return $this->hasMany(TrialSetting::class, 'applies_to_package_id');
    }

    /**
     * Get a translated field dynamically, e.g. $package->translated('name')
     */
    public function translated(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->{"{$field}_{$locale}"} ?? $this->{"{$field}_en"};
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function moduleDefinitions(): BelongsToMany
    {
        return $this->belongsToMany(
            ModuleDefinition::class,
            'package_modules',
            'package_id',
            'module_definition_id'
        );
    }

    public function packageModules(): HasMany
    {
        return $this->hasMany(PackageModule::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getHasTrialAttribute(): bool
    {
        return $this->trial_days > 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->monthly_price, 2);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}