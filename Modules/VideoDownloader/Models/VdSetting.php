<?php

namespace Modules\VideoDownloader\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VdSetting extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'vd_settings';

    protected $fillable = [
        'company_id',
        'max_file_size_mb',
        'max_concurrent_downloads',
        'retention_days',
        'allowed_platforms',
        'allow_audio_only',
        'storage_limit_gb',
        'notify_on_complete',
        'notify_on_failure',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'allowed_platforms'        => 'array',
        'allow_audio_only'         => 'boolean',
        'notify_on_complete'       => 'boolean',
        'notify_on_failure'        => 'boolean',
        'max_file_size_mb'         => 'integer',
        'max_concurrent_downloads' => 'integer',
        'retention_days'           => 'integer',
        'storage_limit_gb'         => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Max file size in bytes — used for validation comparisons.
     */
    public function getMaxFileSizeBytesAttribute(): int
    {
        return $this->max_file_size_mb * 1024 * 1024;
    }

    /**
     * Storage limit in bytes — null if unlimited.
     */
    public function getStorageLimitBytesAttribute(): ?int
    {
        if ($this->storage_limit_gb === null) return null;
        return (int) ($this->storage_limit_gb * 1073741824);
    }

    /**
     * Whether all platforms are allowed (no restriction).
     */
    public function getAllPlatformsAllowedAttribute(): bool
    {
        return empty($this->allowed_platforms);
    }

    /**
     * Check if a specific platform key is allowed for this company.
     */
    public function isPlatformAllowed(string $platform): bool
    {
        if ($this->all_platforms_allowed) return true;
        return in_array($platform, $this->allowed_platforms ?? []);
    }

    /**
     * Human-readable storage limit.
     */
    public function getStorageLimitLabelAttribute(): string
    {
        return $this->storage_limit_gb
            ? $this->storage_limit_gb . ' GB'
            : __('Unlimited');
    }
}