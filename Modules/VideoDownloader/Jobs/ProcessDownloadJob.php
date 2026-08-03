<?php

namespace Modules\VideoDownloader\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\VideoDownloader\Exceptions\StorageLimitExceededException;
use Modules\VideoDownloader\Models\VdActivityLog;
use Modules\VideoDownloader\Models\VdDownload;
use Modules\VideoDownloader\Services\Contracts\VideoDownloadServiceInterface;
use Modules\VideoDownloader\Services\DownloadSettingService;
use Modules\VideoDownloader\Services\DownloadStatusService;
use Modules\VideoDownloader\Services\DownloadStorageService;

class ProcessDownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times this job may be attempted.
     * Downloads are expensive — 3 tries maximum.
     */
    public int $tries = 3;

    /**
     * Seconds between retries — exponential backoff.
     * Attempt 1 fails → wait 30s → attempt 2 → wait 60s → attempt 3.
     */
    public array $backoff = [30, 60];

    /**
     * Maximum runtime before the job is killed.
     * Large files (500MB+) can take several minutes.
     */
    public int $timeout = 600; // 10 minutes

    public function __construct(public VdDownload $download)
    {
    }

    public function handle(
        VideoDownloadServiceInterface $downloader,
        DownloadStatusService         $statusService,
        DownloadStorageService        $storageService,
        DownloadSettingService        $settingService
    ): void {
        // Refresh to get latest status from DB
        $this->download->refresh();

        // ── Guard: skip if cancelled while queued ────────────────────────────
        if ($this->download->status === VdDownload::STATUS_CANCELLED) {
            Log::info("ProcessDownloadJob skipped — download #{$this->download->id} was cancelled.");
            return;
        }

        // ── Guard: must be pending to start ──────────────────────────────────
        if (! $this->download->canTransitionTo(VdDownload::STATUS_PROCESSING)) {
            Log::warning("ProcessDownloadJob skipped — invalid status [{$this->download->status}] for download #{$this->download->id}.");
            return;
        }

        // ── Check storage limit ───────────────────────────────────────────────
        $setting     = $settingService->forCompany($this->download->company_id);
        $limitBytes  = $setting->storage_limit_bytes;

        if ($limitBytes !== null) {
            $usedBytes = $storageService->companyStorageUsed($this->download->company_id);
            if ($usedBytes >= $limitBytes) {
                $statusService->markFailed(
                    $this->download,
                    "Storage limit of {$setting->storage_limit_gb} GB reached. Please delete old downloads to free space."
                );
                VdActivityLog::log($this->download, VdActivityLog::ACTION_DOWNLOAD_FAILED, [
                    'reason'     => 'storage_limit',
                    'limit_gb'   => $setting->storage_limit_gb,
                    'used_bytes' => $usedBytes,
                ]);
                return;
            }
        }

        // ── Transition to processing ──────────────────────────────────────────
        $statusService->markProcessing($this->download);

        VdActivityLog::log($this->download, VdActivityLog::ACTION_DOWNLOAD_STARTED, [
            'format_id' => $this->download->selected_format_id,
            'quality'   => $this->download->selected_quality,
            'ext'       => $this->download->selected_format_ext,
            'attempt'   => $this->download->attempts,
        ]);

        Log::info("ProcessDownloadJob started for download #{$this->download->id}", [
            'url'    => $this->download->original_url,
            'format' => $this->download->selected_format_id,
        ]);

        try {
            // ── Build temp output path ────────────────────────────────────────
            $tempPath = $storageService->buildTempPath($this->download);

            // ── Run the download engine ───────────────────────────────────────
            $result = $downloader->download(
                $this->download->original_url,
                $this->download->selected_format_id,
                $tempPath
            );

            // ── Handle failure result ─────────────────────────────────────────
            if ($result->failed()) {
                $this->handleFailedResult($statusService, $result->errorMessage ?? 'Download failed.');
                return;
            }

            // ── Validate file size against company limit ───────────────────────
            $maxBytes = $setting->max_file_size_mb * 1024 * 1024;
            if ($result->fileSize > $maxBytes) {
                // Clean up oversized temp file
                if (file_exists($result->filePath)) {
                    @unlink($result->filePath);
                }
                $this->handleFailedResult(
                    $statusService,
                    "File size ({$this->formatBytes($result->fileSize)}) exceeds the maximum allowed size ({$setting->max_file_size_mb} MB)."
                );
                return;
            }

            // ── Move to permanent storage ─────────────────────────────────────
            $relativePath = $storageService->moveToFinal($this->download, $result->filePath);
            $safeFileName = $storageService->sanitizeFilename($this->download->video_title ?? 'video')
                . '.' . pathinfo($result->filePath, PATHINFO_EXTENSION);

            // ── Mark completed ────────────────────────────────────────────────
            $statusService->markCompleted(
                $this->download,
                $relativePath,
                $safeFileName,
                $result->fileSize
            );

            VdActivityLog::log($this->download, VdActivityLog::ACTION_DOWNLOAD_COMPLETED, [
                'file_size'  => $result->fileSize,
                'file_path'  => $relativePath,
                'quality'    => $this->download->selected_quality,
            ]);

            Log::info("ProcessDownloadJob completed for download #{$this->download->id}", [
                'path'      => $relativePath,
                'file_size' => $result->fileSize,
            ]);

        } catch (\Throwable $e) {
            Log::error("ProcessDownloadJob exception for download #{$this->download->id}", [
                'error'   => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Transition back to pending so it can retry
            if ($this->attempts() < $this->tries) {
                // Reset to pending so the state machine allows re-processing
                $this->download->update(['status' => VdDownload::STATUS_PENDING]);
                throw $e; // Rethrow — Laravel will re-queue after backoff
            }

            // Final attempt — mark permanently failed
            $this->handleFailedResult($statusService, $e->getMessage());
        }
    }

    /**
     * Called by Laravel when all retry attempts are exhausted.
     */
    public function failed(\Throwable $e): void
    {
        Log::error("ProcessDownloadJob permanently failed for download #{$this->download->id}", [
            'error' => $e->getMessage(),
        ]);

        $this->download->refresh();

        // Only mark failed if not already in a terminal state
        if (! in_array($this->download->status, [
            VdDownload::STATUS_COMPLETED,
            VdDownload::STATUS_CANCELLED,
        ])) {
            $this->download->update([
                'status'        => VdDownload::STATUS_FAILED,
                'error_message' => 'Download failed after all retry attempts. ' . $e->getMessage(),
            ]);

            VdActivityLog::log($this->download, VdActivityLog::ACTION_DOWNLOAD_FAILED, [
                'error' => $e->getMessage(),
                'final' => true,
            ]);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function handleFailedResult(DownloadStatusService $statusService, string $message): void
    {
        $this->download->refresh();

        // Only transition if in a valid state to fail
        if ($this->download->canTransitionTo(VdDownload::STATUS_FAILED)) {
            $statusService->markFailed($this->download, $message);
        } else {
            // Direct update as fallback
            $this->download->update([
                'status'        => VdDownload::STATUS_FAILED,
                'error_message' => $message,
            ]);
        }

        VdActivityLog::log($this->download, VdActivityLog::ACTION_DOWNLOAD_FAILED, [
            'error'   => $message,
            'attempt' => $this->attempts(),
        ]);
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }
}