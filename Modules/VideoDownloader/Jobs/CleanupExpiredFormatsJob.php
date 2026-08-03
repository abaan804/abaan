<?php

namespace Modules\VideoDownloader\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\VideoDownloader\Repositories\FormatCacheRepository;

class CleanupExpiredFormatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 60;

    public function handle(FormatCacheRepository $cacheRepo): void
    {
        $deleted = $cacheRepo->deleteExpired();
        Log::info("CleanupExpiredFormatsJob: pruned {$deleted} expired format cache entries.");
    }
}