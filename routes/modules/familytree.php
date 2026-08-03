<?php

use Illuminate\Support\Facades\Route;
use Modules\FamilyTree\Controllers\DashboardController;
use Modules\FamilyTree\Controllers\DocumentController;
use Modules\FamilyTree\Controllers\EventController;
use Modules\FamilyTree\Controllers\FamilyController;
use Modules\FamilyTree\Controllers\MarriageController;
use Modules\FamilyTree\Controllers\MemberController;
use Modules\FamilyTree\Controllers\NotificationController;
use Modules\FamilyTree\Controllers\RelationshipController;
use Modules\FamilyTree\Controllers\ReportController;
use Modules\FamilyTree\Controllers\SearchController;
use Modules\FamilyTree\Controllers\TreeController;

Route::prefix('app/family-tree')
    ->middleware(['auth', 'verified', 'company.selected', 'subscription.check', 'module.enabled:familytree.index'])
    ->name('familytree.')
    ->group(function () {

        // ── Dashboard ─────────────────────────────────────────────────────────
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // ── Families ──────────────────────────────────────────────────────────
        Route::get('/families', [FamilyController::class, 'index'])->name('families.index');
        Route::get('/families/table', [FamilyController::class, 'table'])->name('families.table');
        Route::get('/families/{family}/json', [FamilyController::class, 'json'])->name('families.json');
        Route::post('/families', [FamilyController::class, 'store'])->name('families.store');
        Route::put('/families/{family}', [FamilyController::class, 'update'])->name('families.update');
        Route::delete('/families/{family}', [FamilyController::class, 'destroy'])->name('families.destroy');

        // ── Family-scoped routes ──────────────────────────────────────────────
        Route::prefix('{family}')->name('family.')->group(function () {

            // ── Members ───────────────────────────────────────────────────────
            Route::get('/members', [MemberController::class, 'index'])->name('members.index');
            Route::get('/members/table', [MemberController::class, 'table'])->name('members.table');
            Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
            Route::get('/members/{member}/json', [MemberController::class, 'json'])->name('members.json');
            Route::post('/members', [MemberController::class, 'store'])->name('members.store');
            Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
            Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
            Route::post('/members/{member}/link-father', [MemberController::class, 'linkFather'])->name('members.link-father');
            Route::post('/members/{member}/link-mother', [MemberController::class, 'linkMother'])->name('members.link-mother');
            Route::post('/members/{member}/unlink-parent', [MemberController::class, 'unlinkParent'])->name('members.unlink-parent');

            // ── Marriages ─────────────────────────────────────────────────────
            Route::get('/marriages', [MarriageController::class, 'index'])->name('marriages.index');
            Route::get('/marriages/table', [MarriageController::class, 'table'])->name('marriages.table');
            Route::get('/marriages/{marriage}/json', [MarriageController::class, 'json'])->name('marriages.json');
            Route::post('/marriages', [MarriageController::class, 'store'])->name('marriages.store');
            Route::put('/marriages/{marriage}', [MarriageController::class, 'update'])->name('marriages.update');
            Route::delete('/marriages/{marriage}', [MarriageController::class, 'destroy'])->name('marriages.destroy');

            // ── Events ────────────────────────────────────────────────────────
            Route::get('/events', [EventController::class, 'index'])->name('events.index');
            Route::get('/events/table', [EventController::class, 'table'])->name('events.table');
            Route::get('/events/{event}/json', [EventController::class, 'json'])->name('events.json');
            Route::post('/events', [EventController::class, 'store'])->name('events.store');
            Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
            Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
            Route::delete('/event-media/{media}', [EventController::class, 'destroyMedia'])->name('events.media.destroy');

            // ── Documents ─────────────────────────────────────────────────────
            Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
            Route::get('/documents/table', [DocumentController::class, 'table'])->name('documents.table');
            Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
            Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
            Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

            // ── Relationships ─────────────────────────────────────────────────
            Route::get('/members/{member}/relationships', [RelationshipController::class, 'summary'])->name('members.relationships');
            Route::post('/members/{member}/relationships', [RelationshipController::class, 'store'])->name('members.relationships.store');
            Route::delete('/relationships/{relationship}', [RelationshipController::class, 'destroy'])->name('relationships.destroy');
            Route::get('/relationships/path', [RelationshipController::class, 'findPath'])->name('relationships.path');

            // ── Tree Visualization ────────────────────────────────────────────
            Route::get('/tree', [TreeController::class, 'index'])->name('tree.index');
            Route::get('/tree/data/full', [TreeController::class, 'fullTree'])->name('tree.data.full');
            Route::get('/tree/data/descendant/{member}', [TreeController::class, 'descendantTree'])->name('tree.data.descendant');
            Route::get('/tree/data/ancestor/{member}', [TreeController::class, 'ancestorTree'])->name('tree.data.ancestor');
            Route::get('/tree/member/{member}/card', [TreeController::class, 'memberCard'])->name('tree.member.card');

            // ── Reports ───────────────────────────────────────────────────────
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/members', [ReportController::class, 'members'])->name('reports.members');
            Route::get('/reports/members/table', [ReportController::class, 'membersTable'])->name('reports.members.table');
            Route::get('/reports/members/export/{format}', [ReportController::class, 'membersExport'])->name('reports.members.export');
            Route::get('/reports/members/pdf', [ReportController::class, 'membersPdf'])->name('reports.members.pdf');
            Route::get('/reports/births', [ReportController::class, 'births'])->name('reports.births');
            Route::get('/reports/births/table', [ReportController::class, 'birthsTable'])->name('reports.births.table');
            Route::get('/reports/deaths', [ReportController::class, 'deaths'])->name('reports.deaths');
            Route::get('/reports/deaths/table', [ReportController::class, 'deathsTable'])->name('reports.deaths.table');
            Route::get('/reports/marriages', [ReportController::class, 'marriages'])->name('reports.marriages');
            Route::get('/reports/marriages/table', [ReportController::class, 'marriagesTable'])->name('reports.marriages.table');
            Route::get('/reports/events', [ReportController::class, 'events'])->name('reports.events');
            Route::get('/reports/events/table', [ReportController::class, 'eventsTable'])->name('reports.events.table');
            Route::get('/reports/missing', [ReportController::class, 'missing'])->name('reports.missing');
            Route::get('/reports/missing/table', [ReportController::class, 'missingTable'])->name('reports.missing.table');

            // ── Notifications ─────────────────────────────────────────────────
            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::post('/notifications/send-birthday', [NotificationController::class, 'sendBirthday'])->name('notifications.send-birthday');
            Route::post('/notifications/send-all-birthdays', [NotificationController::class, 'sendAllBirthdays'])->name('notifications.send-all-birthdays');

            // ── Global Search within family ───────────────────────────────────
            Route::get('/search', [SearchController::class, 'search'])->name('search');

            Route::get('/reports/births/export/{format}',    [ReportController::class, 'birthsExport'])->name('reports.births.export');
            Route::get('/reports/deaths/export/{format}',    [ReportController::class, 'deathsExport'])->name('reports.deaths.export');
            Route::get('/reports/marriages/export/{format}', [ReportController::class, 'marriagesExport'])->name('reports.marriages.export');
            Route::get('/reports/events/export/{format}',    [ReportController::class, 'eventsExport'])->name('reports.events.export');
        });

        // ── Global search across all families ────────────────────────────────
        Route::get('/global-search', [SearchController::class, 'globalSearch'])->name('global-search');
    });