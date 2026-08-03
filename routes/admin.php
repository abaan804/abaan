<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CompanyModuleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\ModuleDefinitionController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TrialSettingController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ModuleRequestController;
use App\Http\Controllers\Admin\NotificationSettingController;
use App\Http\Controllers\Admin\RenewalRequestController;

use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['auth', 'super.admin'])
    ->name('admin.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Companies
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
        Route::post('/companies/{company}/suspend', [CompanyController::class, 'suspend'])->name('companies.suspend');
        Route::post('/companies/{company}/activate', [CompanyController::class, 'activate'])->name('companies.activate');
        Route::get('/companies/{company}/modules', [CompanyModuleController::class, 'edit'])->name('companies.modules.edit');
        Route::put('/companies/{company}/modules', [CompanyModuleController::class, 'update'])->name('companies.modules.update');

        // Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::post('/users/super-admin', [AdminUserController::class, 'createSuperAdmin'])->name('users.store-super-admin');

        // Packages
        Route::resource('packages', PackageController::class)->except(['show']);

        // Subscriptions
        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::patch('/subscriptions/{subscription}/status', [SubscriptionController::class, 'updateStatus'])->name('subscriptions.update-status');

        // Trial Settings
        Route::get('/trial-settings', [TrialSettingController::class, 'index'])->name('trial-settings.index');
        Route::put('/trial-settings/global', [TrialSettingController::class, 'updateGlobal'])->name('trial-settings.update-global');
        Route::put('/trial-settings/{package}', [TrialSettingController::class, 'updatePackageOverride'])->name('trial-settings.update-package');

        // Modules
        Route::resource('modules', ModuleDefinitionController::class)->except(['show']);

        // Roles & Permissions
        Route::resource('roles', RoleController::class)->except(['show']);

        // Website Content
        Route::get('/content', [PageContentController::class, 'index'])->name('content.index');
        Route::put('/content/{content}', [PageContentController::class, 'update'])->name('content.update');

        // Blog
        Route::resource('blogs', BlogController::class)->except(['show']);

        // FAQ
        Route::resource('faqs', FaqController::class)->except(['show']);

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    
        //Contact Message
        Route::get('/contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('/contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::post('/contact-messages/{contactMessage}/mark-unread', [ContactMessageController::class, 'markUnread'])->name('contact-messages.mark-unread');
        Route::delete('/contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');    
    
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/upload', [BackupController::class, 'upload'])->name('backups.upload');
        Route::post('/backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');

        Route::get('/module-requests', [ModuleRequestController::class, 'index'])->name('module-requests.index');
        Route::post('/module-requests/{moduleRequest}/approve', [ModuleRequestController::class, 'approve'])->name('module-requests.approve');
        Route::post('/module-requests/{moduleRequest}/decline', [ModuleRequestController::class, 'decline'])->name('module-requests.decline');

        Route::get('/settings/notifications', [NotificationSettingController::class, 'index'])->name('settings.notifications.index');
        Route::put('/settings/notifications', [NotificationSettingController::class, 'update'])->name('settings.notifications.update');
        Route::post('/settings/notifications/test', [NotificationSettingController::class, 'test'])->name('settings.notifications.test');

        // Packages
        Route::resource('packages', PackageController::class);
        Route::get('packages/{package}/json', [PackageController::class, 'json'])->name('packages.json');

        // Subscriptions
        Route::resource('subscriptions', SubscriptionController::class)->only(['index', 'create', 'store']);
        Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('admin.subscriptions.renew');
        Route::get('subscriptions/package/{package}/info', [SubscriptionController::class, 'packageInfo'])->name('admin.subscriptions.package-info');

    });

    Route::prefix('renewal-requests')->name('admin.renewal-requests.')->group(function () {
        Route::get('/',                                     [RenewalRequestController::class, 'index'])->name('index');
        Route::get('/{renewalRequest}',                     [RenewalRequestController::class, 'show'])->name('show');
        Route::get('/{renewalRequest}/screenshot',          [RenewalRequestController::class, 'screenshot'])->name('screenshot');
        Route::post('/{renewalRequest}/approve',            [RenewalRequestController::class, 'approve'])->name('approve');
        Route::post('/{renewalRequest}/reject',             [RenewalRequestController::class, 'reject'])->name('reject');
    });