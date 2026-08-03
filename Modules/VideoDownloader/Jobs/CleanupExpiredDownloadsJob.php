<?php

namespace Modules\VideoDownloader\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\VideoDownloader\Models\VdActivityLog;
use Modules\VideoDownloader\Models\VdDownload;
use Modules\VideoDownloader\Services\DownloadSettingService;
use Modules\VideoDownloader\Services\DownloadStorageService;

class CleanupExpiredDownloadsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 300; // 5 minutes

    public function __construct(public ?int $companyId = null)
    {
    }

    public function handle(
        DownloadStorageService $storageService,
        DownloadSettingService $settingService
    ): void {
        $totalDeleted = 0;
        $totalFreed   = 0;

        // Query downloads with physical files that are in terminal states
        $query = VdDownload::whereIn('status', [
            VdDownload::STATUS_COMPLETED,
            VdDownload::STATUS_FAILED,
            VdDownload::STATUS_CANCELLED,
        ])->whereNotNull('file_path');

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        $downloads = $query->get();

        foreach ($downloads as $download) {
            $setting       = $settingService->forCompany($download->company_id);
            $retentionDays = $setting->retention_days;
            $cutoff        = now()->subDays($retentionDays);

            // Skip if file is within retention window
            $referenceDate = $download->completed_at ?? $download->updated_at;
            if ($referenceDate && $referenceDate->isAfter($cutoff)) {
                continue;
            }

            $fileSize = $download->file_size ?? 0;

            // Delete the physical file
            $deleted = $storageService->deleteFile($download);

            if ($deleted) {
                $download->update([
                    'file_path' => null,
                    'file_size' => null,
                ]);

                VdActivityLog::log($download, VdActivityLog::ACTION_FILE_DELETED, [
                    'reason'    => 'retention_expired',
                    'file_size' => $fileSize,
                    'days_kept' => $retentionDays,
                ]);

                $totalDeleted++;
                $totalFreed += $fileSize;
            }
        }

        Log::info("CleanupExpiredDownloadsJob completed", [
            'deleted' => $totalDeleted,
            'freed'   => $totalFreed,
            'company' => $this->companyId ?? 'all',
        ]);
    }
}
