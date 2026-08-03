<?php

use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\CompanyProfileController;
use App\Http\Controllers\Tenant\TeamController;
use App\Http\Controllers\Tenant\BillingController;
use App\Http\Controllers\Tenant\ModuleController;

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'company.selected', 'subscription.check'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/company', [CompanyProfileController::class, 'edit'])->name('company.edit');
        Route::put('/company', [CompanyProfileController::class, 'update'])->name('company.update');

        Route::get('/team', [TeamController::class, 'index'])->name('team.index');
        Route::post('/team', [TeamController::class, 'store'])->name('team.store');
        Route::patch('/team/{user}/toggle-status', [TeamController::class, 'toggleStatus'])->name('team.toggle-status');
        Route::delete('/team/{user}', [TeamController::class, 'destroy'])->name('team.destroy');
        Route::patch('/team/{user}/reset-password', [TeamController::class, 'resetPassword'])->name('team.reset-password');
        
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/billing/change-plan', [BillingController::class, 'changePlan'])->name('billing.change-plan');

        Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::post('/modules/{module}/request', [ModuleController::class, 'request'])->name('modules.request');
        
        Route::get('/team/{user}/permissions', [TeamController::class, 'editPermissions'])->name('team.permissions.edit');
        Route::put('/team/{user}/permissions', [TeamController::class, 'updatePermissions'])->name('team.permissions.update');

$sitePlaceholders = [
            
            // 'billing.index' => 'Billing',  
            // 'modules.index' => 'Modules'

        ];

        foreach ($sitePlaceholders as $slug => $label) {
            Route::get("/{$slug}", function () use ($label) {
                return view('web.placeholder', ['label' => $label]);
            })->name($slug);
        }
    });

     // Temporary placeholders — replaced page-by-page in Step 8 sub-steps
