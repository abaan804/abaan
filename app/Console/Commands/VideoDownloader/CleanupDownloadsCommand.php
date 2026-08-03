<?php

namespace App\Console\Commands\VideoDownloader;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\VideoDownloader\Models\VdDownload;
use Modules\VideoDownloader\Services\DownloadStorageService;
use Modules\VideoDownloader\Services\DownloadSettingService;

class CleanupDownloadsCommand extends Command
{
    protected $signature = 'videodownloader:cleanup
                            {--company= : Restrict cleanup to a specific company ID}
                            {--dry-run  : Preview what would be deleted without deleting}
                            {--force    : Skip retention policy and delete all completed downloads}';

    protected $description = 'Delete expired download files based on per-company retention policy';

    public function __construct(
        protected DownloadStorageService $storageService,
        protected DownloadSettingService $settingService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun    = $this->option('dry-run');
        $force     = $this->option('force');
        $companyId = $this->option('company');
        $deleted   = 0;
        $freed     = 0;

        $this->info($dryRun ? '[DRY RUN] Scanning expired downloads...' : 'Scanning expired downloads...');

        $query = VdDownload::whereIn('status', ['completed', 'failed', 'cancelled'])
            ->whereNotNull('file_path');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $downloads = $query->with('company')->get();

        foreach ($downloads as $download) {
            $setting        = $this->settingService->forCompany($download->company_id);
            $retentionDays  = $force ? 0 : $setting->retention_days;
            $cutoff         = now()->subDays($retentionDays);

            if (! $force && $download->completed_at?->isAfter($cutoff)) {
                continue; // Not expired yet
            }

            $size = $download->file_size ?? 0;
            $freed += $size;
            $deleted++;

            $this->line(sprintf(
                '  %s %s — %s (%s)',
                $dryRun ? '[WOULD DELETE]' : '[DELETING]',
                $download->video_title ?? 'Unknown',
                $download->file_path,
                $this->formatBytes($size)
            ));

            if (! $dryRun) {
                $this->storageService->deleteFile($download);
                $download->update(['file_path' => null, 'file_size' => null]);
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("[DRY RUN] Would delete {$deleted} file(s), freeing " . $this->formatBytes($freed));
        } else {
            $this->info("Cleanup complete. Deleted {$deleted} file(s), freed " . $this->formatBytes($freed));
        }

        return Command::SUCCESS;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }
}