<?php

namespace App\Console\Commands\VideoDownloader;

use Illuminate\Console\Command;
use Modules\VideoDownloader\Jobs\ProcessDownloadJob;
use Modules\VideoDownloader\Models\VdDownload;

class RetryFailedDownloadsCommand extends Command
{
    protected $signature = 'videodownloader:retry-failed
                            {--company=  : Restrict to a specific company ID}
                            {--max-attempts=3 : Only retry downloads below this attempt count}
                            {--dry-run   : Preview what would be retried}';

    protected $description = 'Re-queue failed downloads that have not exceeded the max attempt limit';

    public function handle(): int
    {
        $dryRun     = $this->option('dry-run');
        $companyId  = $this->option('company');
        $maxAttempts = (int) $this->option('max-attempts');
        $queued     = 0;

        $query = VdDownload::where('status', 'failed')
            ->where('attempts', '<', $maxAttempts)
            ->whereNotNull('selected_format_id');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $downloads = $query->get();

        if ($downloads->isEmpty()) {
            $this->info('No failed downloads eligible for retry.');
            return Command::SUCCESS;
        }

        $this->info($dryRun
            ? "[DRY RUN] Found {$downloads->count()} download(s) eligible for retry."
            : "Retrying {$downloads->count()} failed download(s)...");

        foreach ($downloads as $download) {
            $this->line(sprintf('  %s #%d — %s (attempt %d/%d)',
                $dryRun ? '[WOULD RETRY]' : '[QUEUING]',
                $download->id,
                $download->video_title ?? $download->original_url,
                $download->attempts + 1,
                $maxAttempts
            ));

            if (! $dryRun) {
                $download->update(['status' => 'pending', 'error_message' => null]);
                ProcessDownloadJob::dispatch($download)
                    ->onQueue(config('videodownloader.queues.downloads'));
                $queued++;
            }
        }

        $this->newLine();
        if (! $dryRun) {
            $this->info("{$queued} download(s) queued for retry.");
        }

        return Command::SUCCESS;
    }
}