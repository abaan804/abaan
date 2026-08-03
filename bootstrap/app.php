<?php
use App\Http\Middleware\CheckSubscriptionStatus;
use App\Http\Middleware\EnsureCompanySelected;
use App\Http\Middleware\EnsureEmailIsVerifiedIfEnabled;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Console\Commands\SendMasjidReminders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            'company.selected' => EnsureCompanySelected::class,
            'subscription.check' => CheckSubscriptionStatus::class,
            'verified' => EnsureEmailIsVerifiedIfEnabled::class,
            'super.admin' => EnsureSuperAdmin::class,
            'module.enabled' => \App\Http\Middleware\EnsureModuleEnabled::class,
        ]);
    })
    ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule) {

         $schedule->command(SendMasjidReminders::class)->dailyAt('09:00');
         $schedule->command('subscriptions:expire')->hourly();

    })
    ->withCommands([
        App\Console\Commands\SendMasjidReminders::class,
        App\Console\Commands\VideoDownloader\CheckYtDlpCommand::class,
        App\Console\Commands\VideoDownloader\CleanupDownloadsCommand::class,
        App\Console\Commands\VideoDownloader\RetryFailedDownloadsCommand::class,
        App\Console\Commands\VideoDownloader\CleanupFormatsCommand::class,
        App\Console\Commands\ExpireSubscriptions::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
