<?php

use Illuminate\Support\Facades\Route;
use Modules\VideoDownloader\Controllers\DashboardController;
use Modules\VideoDownloader\Controllers\DownloadController;
use Modules\VideoDownloader\Controllers\HistoryController;
use Modules\VideoDownloader\Controllers\ReportController;
use Modules\VideoDownloader\Controllers\SettingController;

Route::prefix('app/video-downloader')
    ->middleware(['auth', 'verified', 'company.selected', 'subscription.check', 'module.enabled:videodownloader.index'])
    ->name('videodownloader.')
    ->group(function () {

        // ── Dashboard ─────────────────────────────────────────────────────────
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // ── New Download ──────────────────────────────────────────────────────
        // Step 1: Submit URL → returns metadata + format list
        Route::get('/new',            [DownloadController::class, 'create'])->name('download.create');
        Route::post('/fetch-metadata',[DownloadController::class, 'fetchMetadata'])->name('download.fetch-metadata');

        // Step 2: User selects format → start the download job
        Route::post('/start',         [DownloadController::class, 'start'])->name('download.start');

        // ── Download management ───────────────────────────────────────────────
        Route::get('/downloads/{download}',        [DownloadController::class, 'show'])->name('download.show');
        Route::get('/downloads/{download}/status', [DownloadController::class, 'status'])->name('download.status');
        Route::get('/downloads/{download}/serve',  [DownloadController::class, 'serve'])->name('download.serve');
        Route::post('/downloads/{download}/retry', [DownloadController::class, 'retry'])->name('download.retry');
        Route::post('/downloads/{download}/cancel',[DownloadController::class, 'cancel'])->name('download.cancel');
        Route::delete('/downloads/{download}',     [DownloadController::class, 'destroy'])->name('download.destroy');

        // ── History ───────────────────────────────────────────────────────────
        Route::get('/history',       [HistoryController::class, 'index'])->name('history.index');
        Route::get('/history/table', [HistoryController::class, 'table'])->name('history.table');

        // ── Reports ───────────────────────────────────────────────────────────
        Route::get('/reports',              [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/table',        [ReportController::class, 'table'])->name('reports.table');
        Route::get('/reports/export/{format}', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/pdf',          [ReportController::class, 'pdf'])->name('reports.pdf');

        // ── Settings ──────────────────────────────────────────────────────────
        Route::get('/settings',  [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });