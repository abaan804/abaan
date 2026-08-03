<?php

namespace Modules\Ledger;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class LedgerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'ledger');

        View::share('ledgerLayout', request('standalone') ? 'ledger::layouts.standalone' : 'ledger::layouts.app');

        Route::middleware('web')->group(base_path('routes/modules/easykhata.php'));
    }
}