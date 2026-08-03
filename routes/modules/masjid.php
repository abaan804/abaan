<?php

use Illuminate\Support\Facades\Route;
use Modules\Masjid\Controllers\DashboardController;
use Modules\Masjid\Controllers\MemberController;
use Modules\Masjid\Controllers\MosqueController;
use Modules\Masjid\Controllers\NotificationController;
use Modules\Masjid\Controllers\PaymentController;
use Modules\Masjid\Controllers\ReportController;
use Modules\Masjid\Controllers\SearchController;
use Modules\Masjid\Controllers\SeasonController;
use Modules\Masjid\Controllers\SettingController;
use Modules\Masjid\Controllers\BackupController;
use Modules\Masjid\Controllers\DonationController;
use Modules\Masjid\Controllers\ExpenseController;
use Modules\Masjid\Controllers\NoteController;
use Modules\Masjid\Controllers\FinancialSummaryController;
use Modules\Masjid\Controllers\DonationReportController;
use Modules\Masjid\Controllers\SeasonCollectionListController;

Route::prefix('app/masjid')
    ->middleware(['auth', 'verified', 'company.selected', 'subscription.check', 'module.enabled:masjid.index'])
    ->name('masjid.')
    ->group(function () {

        // Dashboard — mosque selection landing
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // Mosque-specific routes — everything scoped under {mosque}
        Route::prefix('{mosque}')->name('mosque.')->group(function () {

            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

            // Mosque profile
            Route::get('/profile', [MosqueController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [MosqueController::class, 'update'])->name('profile.update');

            // Members
            Route::get('/members', [MemberController::class, 'index'])->name('members.index');
            Route::get('/members/table', [MemberController::class, 'table'])->name('members.table');
            Route::get('/members/{member}/json', [MemberController::class, 'json'])->name('members.json');
            Route::post('/members', [MemberController::class, 'store'])->name('members.store');
            Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
            Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
            Route::get('/members/{member}/statement', [MemberController::class, 'statement'])->name('members.statement');
            Route::get('/members/{member}/statement/pdf', [MemberController::class, 'statementPdf'])->name('members.statement.pdf');

            // Seasons
            Route::get('/seasons', [SeasonController::class, 'index'])->name('seasons.index');
            Route::get('/seasons/table', [SeasonController::class, 'table'])->name('seasons.table');
            Route::get('/seasons/{season}/json', [SeasonController::class, 'json'])->name('seasons.json');
            Route::post('/seasons', [SeasonController::class, 'store'])->name('seasons.store');
            Route::put('/seasons/{season}', [SeasonController::class, 'update'])->name('seasons.update');
            Route::delete('/seasons/{season}', [SeasonController::class, 'destroy'])->name('seasons.destroy');
            Route::get('/seasons/{season}/members', [SeasonController::class, 'members'])->name('seasons.members');
            Route::get('/seasons/{season}/members/table', [SeasonController::class, 'membersTable'])->name('seasons.members.table');
            Route::post('/seasons/{season}/assign/{member}', [SeasonController::class, 'assignMember'])->name('seasons.assign');
            Route::delete('/seasons/{season}/unassign/{seasonMember}', [SeasonController::class, 'unassignMember'])->name('seasons.unassign');
            Route::post('/seasons/{season}/assign-all', [SeasonController::class, 'assignAll'])->name('seasons.assign-all');
            Route::post('/seasons/{season}/sync-amount', [SeasonController::class, 'syncAmount'])->name('seasons.sync-amount');
            Route::get('/seasons/{season}/blank-list',[SeasonCollectionListController::class, 'download'])->name('seasons.blank-list');

            // Payments
            Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/table', [PaymentController::class, 'table'])->name('payments.table');
            Route::get('/payments/{payment}/json', [PaymentController::class, 'json'])->name('payments.json');
            Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
            Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
            Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
            Route::delete('/payment-attachments/{attachment}', [PaymentController::class, 'deleteAttachment'])->name('payments.attachments.destroy');
            Route::get('/payments/season-member-info', [PaymentController::class, 'seasonMemberInfo'])->name('payments.season-member-info');

            // Reports
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/collection', [ReportController::class, 'collection'])->name('reports.collection');
            Route::get('/reports/collection/table', [ReportController::class, 'collectionTable'])->name('reports.collection.table');
            Route::get('/reports/collection/pdf', [ReportController::class, 'collectionPdf'])->name('reports.collection.pdf');
            Route::get('/reports/outstanding', [ReportController::class, 'outstanding'])->name('reports.outstanding');
            Route::get('/reports/outstanding/table', [ReportController::class, 'outstandingTable'])->name('reports.outstanding.table');
            Route::get('/reports/outstanding/pdf', [ReportController::class, 'outstandingPdf'])->name('reports.outstanding.pdf');
            Route::get('/reports/season/{season}', [ReportController::class, 'season'])->name('reports.season');
            Route::get('/reports/season/{season}/pdf', [ReportController::class, 'seasonPdf'])->name('reports.season.pdf');

            // Settings
            Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

            // Notifications
            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::post('/notifications/send-reminder', [NotificationController::class, 'sendReminder'])->name('notifications.send-reminder');
            Route::post('/notifications/send-all-reminders', [NotificationController::class, 'sendAllReminders'])->name('notifications.send-all-reminders');

            // Search
            Route::get('/search', [SearchController::class, 'search'])->name('search');

                // Member export
            Route::get('/members/export/{format}', [MemberController::class, 'export'])->name('members.export');

            // Payment export
            Route::get('/payments/export/{format}', [PaymentController::class, 'export'])->name('payments.export');

            // Report exports
            Route::get('/reports/collection/export/{format}', [ReportController::class, 'collectionExport'])->name('reports.collection.export');
            Route::get('/reports/outstanding/export/{format}', [ReportController::class, 'outstandingExport'])->name('reports.outstanding.export');
            Route::get('/reports/season/{season}/export/{format}', [ReportController::class, 'seasonExport'])->name('reports.season.export');

            // Backups
            Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
            Route::post('/backups/mosque', [BackupController::class, 'createMosqueBackup'])->name('backups.create.mosque');
            Route::post('/backups/company', [BackupController::class, 'createCompanyBackup'])->name('backups.create.company');
            Route::get('/backups/download/{filename}', [BackupController::class, 'download'])->name('backups.download');
            Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');
            Route::post('/backups/upload', [BackupController::class, 'upload'])->name('backups.upload');
            Route::post('/backups/restore/{filename}', [BackupController::class, 'restoreStored'])->name('backups.restore');

            // Donations
            Route::get('/donations', [DonationController::class, 'index'])->name('donations.index');
            Route::get('/donations/table', [DonationController::class, 'table'])->name('donations.table');
            Route::get('/donations/{donation}/json', [DonationController::class, 'json'])->name('donations.json');
            Route::post('/donations', [DonationController::class, 'store'])->name('donations.store');
            Route::put('/donations/{donation}', [DonationController::class, 'update'])->name('donations.update');
            Route::delete('/donations/{donation}', [DonationController::class, 'destroy'])->name('donations.destroy');

            // Expenses
            Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
            Route::get('/expenses/table', [ExpenseController::class, 'table'])->name('expenses.table');
            Route::get('/expenses/{expense}/json', [ExpenseController::class, 'json'])->name('expenses.json');
            Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
            Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
            Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

            // Notes
            Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
            Route::get('/notes/{note}/json', [NoteController::class, 'json'])->name('notes.json');
            Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
            Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
            Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
            Route::post('/notes/{note}/toggle-pin', [NoteController::class, 'togglePin'])->name('notes.toggle-pin');

            // Financial Summary
            Route::get('/financial', [FinancialSummaryController::class, 'index'])->name('financial.index');

            // Donation Reports
            Route::get('/donations/report',              [DonationReportController::class, 'index'])->name('donations.report');
            Route::get('/donations/report/table',        [DonationReportController::class, 'table'])->name('donations.report.table');
            Route::get('/donations/report/export/{format}', [DonationReportController::class, 'export'])->name('donations.report.export');
            Route::get('/donations/report/pdf',          [DonationReportController::class, 'pdf'])->name('donations.report.pdf');

            // Individual Donation Slip
            Route::get('/donations/{donation}/slip',     [DonationReportController::class, 'slip'])->name('donations.slip');
            Route::get('/donations/{donation}/slip/pdf', [DonationReportController::class, 'slipPdf'])->name('donations.slip.pdf');
        });

        // Mosque management (create, list — outside mosque scope)
        Route::get('/mosques', [MosqueController::class, 'index'])->name('mosques.index');
        Route::get('/mosques/table', [MosqueController::class, 'table'])->name('mosques.table');
        Route::get('/mosques/{mosque}/json', [MosqueController::class, 'json'])->name('mosques.json');
        Route::post('/mosques', [MosqueController::class, 'store'])->name('mosques.store');
        Route::delete('/mosques/{mosque}', [MosqueController::class, 'destroy'])->name('mosques.destroy');
    });