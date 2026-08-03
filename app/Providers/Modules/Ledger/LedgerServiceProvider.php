<?php
namespace App\Providers\Modules\Ledger;

use Illuminate\Support\ServiceProvider;

class LedgerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            base_path('Modules/Ledger/Views'),
            'ledger'
        );
    }
}