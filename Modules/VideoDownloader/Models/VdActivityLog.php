<?php

namespace Modules\VideoDownloader\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VdActivityLog extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'vd_activity_logs';

    // Immutable audit record — no updated_at
    public $timestamps  = false;
    const UPDATED_AT    = null;

    // ── Action Constants ──────────────────────────────────────────────────────
    public const ACTION_SUBMITTED          = 'download.submitted';
    public const ACTION_METADATA_FETCHED   = 'metadata.fetched';
    public const ACTION_METADATA_FAILED    = 'metadata.failed';
    public const ACTION_DOWNLOAD_STARTED   = 'download.started';
    public const ACTION_DOWNLOAD_COMPLETED = 'download.completed';
    public const ACTION_DOWNLOAD_FAILED    = 'download.failed';
    public const ACTION_DOWNLOAD_CANCELLED = 'download.cancelled';
    public const ACTION_DOWNLOAD_RETRIED   = 'download.retried';
    public const ACTION_FILE_SERVED        = 'file.served';
    public const ACTION_FILE_DELETED       = 'file.deleted';
    public const ACTION_SETTINGS_UPDATED   = 'settings.updated';

    protected $fillable = [
        'company_id',
        'user_id',
        'download_id',
        'action',
        'properties',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function download(): BelongsTo
    {
        return $this->belongsTo(VdDownload::class, 'download_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Human-readable action label for display in the UI.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_SUBMITTED          => __('URL Submitted'),
            self::ACTION_METADATA_FETCHED   => __('Metadata Fetched'),
            self::ACTION_METADATA_FAILED    => __('Metadata Failed'),
            self::ACTION_DOWNLOAD_STARTED   => __('Download Started'),
            self::ACTION_DOWNLOAD_COMPLETED => __('Download Completed'),
            self::ACTION_DOWNLOAD_FAILED    => __('Download Failed'),
            self::ACTION_DOWNLOAD_CANCELLED => __('Cancelled'),
            self::ACTION_DOWNLOAD_RETRIED   => __('Retried'),
            self::ACTION_FILE_SERVED        => __('File Downloaded'),
            self::ACTION_FILE_DELETED       => __('File Deleted'),
            self::ACTION_SETTINGS_UPDATED   => __('Settings Updated'),
            default                         => ucfirst(str_replace('.', ' ', $this->action)),
        };
    }

    /**
     * Bootstrap icon for the action type.
     */
    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_SUBMITTED          => 'bi-link-45deg',
            self::ACTION_METADATA_FETCHED   => 'bi-info-circle',
            self::ACTION_METADATA_FAILED    => 'bi-exclamation-circle',
            self::ACTION_DOWNLOAD_STARTED   => 'bi-arrow-repeat',
            self::ACTION_DOWNLOAD_COMPLETED => 'bi-check-circle-fill',
            self::ACTION_DOWNLOAD_FAILED    => 'bi-x-circle-fill',
            self::ACTION_DOWNLOAD_CANCELLED => 'bi-slash-circle',
            self::ACTION_DOWNLOAD_RETRIED   => 'bi-arrow-clockwise',
            self::ACTION_FILE_SERVED        => 'bi-cloud-arrow-down',
            self::ACTION_FILE_DELETED       => 'bi-trash',
            self::ACTION_SETTINGS_UPDATED   => 'bi-gear',
            default                         => 'bi-activity',
        };
    }

    /**
     * Bootstrap color class for the action type.
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_DOWNLOAD_COMPLETED,
            self::ACTION_METADATA_FETCHED,
            self::ACTION_FILE_SERVED        => 'text-success',
            self::ACTION_DOWNLOAD_FAILED,
            self::ACTION_METADATA_FAILED    => 'text-danger',
            self::ACTION_DOWNLOAD_CANCELLED,
            self::ACTION_FILE_DELETED       => 'text-secondary',
            self::ACTION_DOWNLOAD_STARTED,
            self::ACTION_DOWNLOAD_RETRIED   => 'text-warning',
            default                         => 'text-primary',
        };
    }

    // ── Static factory helper ─────────────────────────────────────────────────

    /**
     * Create a log entry with one call — used throughout services and jobs.
     *
     * Usage:
     *   VdActivityLog::log($download, VdActivityLog::ACTION_DOWNLOAD_COMPLETED, [
     *       'file_size' => 1024,
     *       'duration'  => 5.3,
     *   ]);
     */
    public static function log(
        VdDownload $download,
        string $action,
        array $properties = []
    ): static {
        return static::create([
            'company_id'  => $download->company_id,
            'user_id'     => $download->user_id,
            'download_id' => $download->id,
            'action'      => $action,
            'properties'  => $properties ?: null,
            'created_at'  => now(),
        ]);
    }
}