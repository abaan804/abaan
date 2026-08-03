<?php

namespace Modules\VideoDownloader\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VdDownload extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $table = 'vd_downloads';

    // ── Status Constants ──────────────────────────────────────────────────────
    // Used by DownloadStatusService to enforce the state machine.
    // Never reference raw strings outside of this class.
    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_FAILED     = 'failed';
    public const STATUS_CANCELLED  = 'cancelled';

    /**
     * Valid state machine transitions.
     * Key = current status, Value = allowed next statuses.
     */
    public const TRANSITIONS = [
        self::STATUS_PENDING    => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELLED],
        self::STATUS_FAILED     => [self::STATUS_PENDING, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED  => [], // terminal — no further transitions
        self::STATUS_CANCELLED  => [], // terminal
    ];

    // ── Max retry attempts before giving up ───────────────────────────────────
    public const MAX_ATTEMPTS = 3;

    protected $fillable = [
        'company_id',
        'user_id',
        'original_url',
        'platform',
        'video_title',
        'video_thumbnail',
        'video_duration',
        'uploader_name',
        'upload_date',
        'selected_format_id',
        'selected_quality',
        'selected_format_ext',
        'is_audio_only',
        'file_path',
        'file_size',
        'file_name',
        'status',
        'error_message',
        'attempts',
        'last_attempted_at',
        'completed_at',
        'job_id',
        'metadata_fetched_at',
        'download_started_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_audio_only'       => 'boolean',
        'file_size'           => 'integer',
        'video_duration'      => 'integer',
        'attempts'            => 'integer',
        'upload_date'         => 'date',
        'last_attempted_at'   => 'datetime',
        'completed_at'        => 'datetime',
        'metadata_fetched_at' => 'datetime',
        'download_started_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(VdActivityLog::class, 'download_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Human-readable file size — e.g. "142.3 MB"
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (! $this->file_size) return '—';
        if ($this->file_size < 1024)       return $this->file_size . ' B';
        if ($this->file_size < 1048576)    return round($this->file_size / 1024, 1) . ' KB';
        if ($this->file_size < 1073741824) return round($this->file_size / 1048576, 1) . ' MB';
        return round($this->file_size / 1073741824, 2) . ' GB';
    }

    /**
     * Human-readable duration — e.g. "3:47" or "1:02:15"
     */
    public function getFormattedDurationAttribute(): string
    {
        if (! $this->video_duration) return '—';

        $seconds = $this->video_duration;
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return $h > 0
            ? sprintf('%d:%02d:%02d', $h, $m, $s)
            : sprintf('%d:%02d', $m, $s);
    }

    /**
     * Bootstrap badge color class for the current status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'bg-secondary',
            self::STATUS_PROCESSING => 'bg-warning text-dark',
            self::STATUS_COMPLETED  => 'bg-success',
            self::STATUS_FAILED     => 'bg-danger',
            self::STATUS_CANCELLED  => 'bg-dark',
            default                 => 'bg-secondary',
        };
    }

    /**
     * Bootstrap icon class for the current status.
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'bi-clock',
            self::STATUS_PROCESSING => 'bi-arrow-repeat',
            self::STATUS_COMPLETED  => 'bi-check-circle-fill',
            self::STATUS_FAILED     => 'bi-x-circle-fill',
            self::STATUS_CANCELLED  => 'bi-slash-circle',
            default                 => 'bi-question-circle',
        };
    }

    /**
     * Platform icon class — Bootstrap Icons.
     */
    public function getPlatformIconAttribute(): string
    {
        return match ($this->platform) {
            'youtube'     => 'bi-youtube',
            'twitter'     => 'bi-twitter-x',
            'instagram'   => 'bi-instagram',
            'tiktok'      => 'bi-tiktok',
            'facebook'    => 'bi-facebook',
            'vimeo'       => 'bi-vimeo',
            default       => 'bi-globe',
        };
    }

    /**
     * Whether this download can be retried.
     */
    public function getIsRetryableAttribute(): bool
    {
        return $this->status === self::STATUS_FAILED
            && $this->attempts < self::MAX_ATTEMPTS;
    }

    /**
     * Whether the downloaded file is available for serving.
     */
    public function getIsServableAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED
            && ! empty($this->file_path);
    }

    /**
     * Whether a status transition is valid.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::TRANSITIONS[$this->status] ?? []);
    }

    /**
     * Thumbnail URL — returns external URL or local asset path.
     * Falls back to a platform-specific placeholder.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if (! $this->video_thumbnail) {
            return asset('images/videodownloader/placeholder-' . ($this->platform ?? 'generic') . '.png');
        }

        // If it starts with http it's an external URL
        if (str_starts_with($this->video_thumbnail, 'http')) {
            return $this->video_thumbnail;
        }

        // Otherwise it's a local storage path
        return asset('storage/' . $this->video_thumbnail);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_FAILED);
    }

    public function scopeCancelled(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_CANCELLED);
    }

    public function scopeForUser(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    public function scopeForPlatform(Builder $q, string $platform): Builder
    {
        return $q->where('platform', $platform);
    }

    public function scopeAudioOnly(Builder $q): Builder
    {
        return $q->where('is_audio_only', true);
    }

    public function scopeHasFile(Builder $q): Builder
    {
        return $q->whereNotNull('file_path')->where('status', self::STATUS_COMPLETED);
    }

    public function scopeRetryable(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_FAILED)
            ->where('attempts', '<', self::MAX_ATTEMPTS);
    }

    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(fn ($sub) => $sub
            ->where('video_title', 'like', "%{$term}%")
            ->orWhere('original_url', 'like', "%{$term}%")
            ->orWhere('uploader_name', 'like', "%{$term}%")
            ->orWhere('selected_quality', 'like', "%{$term}%")
        );
    }

    public function scopeDateFrom(Builder $q, string $date): Builder
    {
        return $q->whereDate('created_at', '>=', $date);
    }

    public function scopeDateTo(Builder $q, string $date): Builder
    {
        return $q->whereDate('created_at', '<=', $date);
    }
}