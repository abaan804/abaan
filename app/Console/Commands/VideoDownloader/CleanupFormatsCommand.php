<?php

namespace App\Console\Commands\VideoDownloader;

use Illuminate\Console\Command;
use Modules\VideoDownloader\Repositories\FormatCacheRepository;

class CleanupFormatsCommand extends Command
{
    protected $signature   = 'videodownloader:cleanup-formats';
    protected $description = 'Prune expired format cache entries from vd_formats table';

    public function handle(FormatCacheRepository $repo): int
    {
        $deleted = $repo->deleteExpired();
        $this->info("Pruned {$deleted} expired format cache entries.");
        return Command::SUCCESS;
    }
}