<?php

namespace Modules\VideoDownloader\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VdFormat extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'vd_formats';

    protected $fillable = [
        'company_id',
        'url_hash',
        'original_url',
        'platform',
        'video_title',
        'thumbnail_url',
        'duration',
        'uploader_name',
        'upload_date',
        'formats',
        'fetched_at',
        'expires_at',
    ];

    protected $casts = [
        'formats'    => 'array',
        'upload_date'=> 'date',
        'fetched_at' => 'datetime',
        'expires_at' => 'datetime',
        'duration'   => 'integer',
    ];

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Whether this cache entry is still valid.
     */
    public function getIsValidAttribute(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    /**
     * Whether this cache entry has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return ! $this->is_valid;
    }

    /**
     * How many minutes until this cache entry expires.
     * Returns 0 if already expired.
     */
    public function getMinutesUntilExpiryAttribute(): int
    {
        if ($this->is_expired) return 0;
        return (int) now()->diffInMinutes($this->expires_at, false);
    }

    /**
     * Human-readable duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (! $this->duration) return '—';

        $seconds = $this->duration;
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return $h > 0
            ? sprintf('%d:%02d:%02d', $h, $m, $s)
            : sprintf('%d:%02d', $m, $s);
    }

    /**
     * Video-only formats (have video stream, may lack audio).
     */
    public function getVideoFormatsAttribute(): Collection
    {
        return collect($this->formats ?? [])
            ->filter(fn ($f) => ! empty($f['vcodec']) && ($f['vcodec'] ?? 'none') !== 'none')
            ->values();
    }

    /**
     * Audio-only formats.
     */
    public function getAudioFormatsAttribute(): Collection
    {
        return collect($this->formats ?? [])
            ->filter(fn ($f) =>
                (($f['vcodec'] ?? 'none') === 'none' || empty($f['vcodec']))
                && ! empty($f['acodec']) && ($f['acodec'] ?? 'none') !== 'none'
            )
            ->values();
    }

    /**
     * Best combined formats — pre-merged video+audio streams.
     * These are always safe to download without ffmpeg.
     */
    public function getCombinedFormatsAttribute(): Collection
    {
        return collect($this->formats ?? [])
            ->filter(fn ($f) =>
                (($f['vcodec'] ?? 'none') !== 'none')
                && (($f['acodec'] ?? 'none') !== 'none')
            )
            ->sortByDesc(fn ($f) => $f['height'] ?? 0)
            ->values();
    }

    /**
     * Generate a SHA-256 hash of a normalized URL.
     * Used for cache key lookups.
     */
    public static function hashUrl(string $url): string
    {
        // Normalize: lowercase, strip trailing slash, strip UTM params
        $normalized = strtolower(rtrim($url, '/'));
        $normalized = preg_replace('/[?&](utm_[^&]+)/', '', $normalized);
        return hash('sha256', $normalized);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeValid(Builder $q): Builder
    {
        return $q->where('expires_at', '>', now());
    }

    public function scopeExpired(Builder $q): Builder
    {
        return $q->where('expires_at', '<=', now());
    }

    public function scopeForUrl(Builder $q, string $urlHash): Builder
    {
        return $q->where('url_hash', $urlHash);
    }
}