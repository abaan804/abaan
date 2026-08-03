<?php

namespace Modules\VideoDownloader;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\VideoDownloader\Models\VdDownload;
use Modules\VideoDownloader\Policies\VdDownloadPolicy;
use Modules\VideoDownloader\Services\Contracts\VideoDownloadServiceInterface;

class VideoDownloaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../../config/videodownloader.php', 'videodownloader');

        // Bind the download driver via config
        $this->app->bind(VideoDownloadServiceInterface::class, function ($app) {
            $driver  = config('videodownloader.driver', 'ytdlp');
            $drivers = config('videodownloader.drivers', []);

            if (! isset($drivers[$driver])) {
                throw new \InvalidArgumentException(
                    "Video download driver [{$driver}] is not configured."
                );
            }

            return $app->make($drivers[$driver]);
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'videodownloader');

        // Policy
        Gate::policy(VdDownload::class, VdDownloadPolicy::class);

        // Routes
        Route::middleware('web')
            ->group(base_path('routes/modules/videodownloader.php'));

        // Scheduled commands
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('videodownloader:cleanup')
                ->dailyAt('02:00')
                ->withoutOverlapping();

            $schedule->command('videodownloader:cleanup-formats')
                ->hourly()
                ->withoutOverlapping();

            $schedule->command('videodownloader:retry-failed')
                ->everyThirtyMinutes()
                ->withoutOverlapping();
        }); 

        \Illuminate\Support\Facades\View::share(
            'vdLayout',
            request('standalone')
                ? 'videodownloader::layouts.standalone'
                : 'videodownloader::layouts.app'
        );
    }
}