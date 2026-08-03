<?php

namespace Modules\Masjid;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MasjidServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'masjid');

        View::share('masjidLayout', request('standalone')
            ? 'masjid::layouts.standalone'
            : 'masjid::layouts.app');

        Route::middleware('web')
            ->group(base_path('routes/modules/masjid.php'));
    }
}