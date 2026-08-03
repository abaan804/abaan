<?php

namespace Modules\FamilyTree;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class FamilyTreeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'familytree');

        View::share('familyTreeLayout', request('standalone')
            ? 'familytree::layouts.standalone'
            : 'familytree::layouts.app');

        Route::middleware('web')
            ->group(base_path('routes/modules/familytree.php'));
    }
}