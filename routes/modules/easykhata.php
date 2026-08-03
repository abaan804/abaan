<?php

use Illuminate\Support\Facades\Route;
use Modules\Ledger\Controllers\CategoryController;
use Modules\Ledger\Controllers\CustomerController;
use Modules\Ledger\Controllers\DashboardController;
use Modules\Ledger\Controllers\PaymentMethodController;
use Modules\Ledger\Controllers\ReminderController;
use Modules\Ledger\Controllers\SupplierController;
use Modules\Ledger\Controllers\TransactionController;
use Modules\Ledger\Controllers\ReportController;
use Modules\Ledger\Controllers\SearchController;



Route::prefix('app/ledger')
    ->middleware(['auth', 'verified', 'company.selected', 'subscription.check', 'module.enabled:ledger.dashboard'])
    ->name('ledger.')
    ->group(function () {
        Route::get('/search', [SearchController::class, 'search'])->name('search');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/table', [CustomerController::class, 'table'])->name('customers.table');
        Route::get('/customers/{customer}/json', [CustomerController::class, 'json'])->name('customers.json');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/table', [SupplierController::class, 'table'])->name('suppliers.table');
        Route::get('/suppliers/{supplier}/json', [SupplierController::class, 'json'])->name('suppliers.json');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/table', [CategoryController::class, 'table'])->name('categories.table');
        Route::get('/categories/{category}/json', [CategoryController::class, 'json'])->name('categories.json');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
        Route::get('/payment-methods/table', [PaymentMethodController::class, 'table'])->name('payment-methods.table');
        Route::get('/payment-methods/{paymentMethod}/json', [PaymentMethodController::class, 'json'])->name('payment-methods.json');
        Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
        Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
        Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');

        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/table', [TransactionController::class, 'table'])->name('transactions.table');
        Route::get('/transactions/{transaction}/json', [TransactionController::class, 'json'])->name('transactions.json');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
        Route::delete('/transaction-attachments/{attachment}', [TransactionController::class, 'deleteAttachment'])->name('transactions.attachments.destroy');

        Route::get('/reminders', [ReminderController::class, 'index'])->name('reminders.index');
        Route::get('/reminders/table', [ReminderController::class, 'table'])->name('reminders.table');
        Route::post('/reminders', [ReminderController::class, 'store'])->name('reminders.store');
        Route::patch('/reminders/{reminder}/dismiss', [ReminderController::class, 'dismiss'])->name('reminders.dismiss');
        Route::delete('/reminders/{reminder}', [ReminderController::class, 'destroy'])->name('reminders.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        Route::get('/reports/cash-book', [ReportController::class, 'cashBook'])->name('reports.cash-book');
        Route::get('/reports/cash-book/table', [ReportController::class, 'cashBookTable'])->name('reports.cash-book.table');

        Route::get('/reports/income-expense', [ReportController::class, 'incomeExpense'])->name('reports.income-expense');
        Route::get('/reports/income-expense/table', [ReportController::class, 'incomeExpenseTable'])->name('reports.income-expense.table');

        Route::get('/reports/cash-book/pdf', [ReportController::class, 'cashBookPdf'])->name('reports.cash-book.pdf');
        Route::get('/reports/income-expense/pdf', [ReportController::class, 'incomeExpensePdf'])->name('reports.income-expense.pdf');

        Route::get('/reports/outstanding', [ReportController::class, 'outstanding'])->name('reports.outstanding');
        Route::get('/reports/outstanding/table', [ReportController::class, 'outstandingTable'])->name('reports.outstanding.table');
        Route::get('/reports/outstanding/pdf', [ReportController::class, 'outstandingPdf'])->name('reports.outstanding.pdf');

        Route::get('/reports/period-summary', [ReportController::class, 'periodSummary'])->name('reports.period-summary');
        Route::get('/reports/period-summary/table', [ReportController::class, 'periodSummaryTable'])->name('reports.period-summary.table');

        Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');

        Route::get('/customers/export/{format}', [CustomerController::class, 'export'])->name('customers.export');
        Route::get('/suppliers/export/{format}', [SupplierController::class, 'export'])->name('suppliers.export');
        Route::get('/transactions/export/{format}', [TransactionController::class, 'export'])->name('transactions.export');

        Route::get('/customers/import/template', [CustomerController::class, 'importTemplate'])->name('customers.import.template');
        Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import');

        Route::get('/suppliers/import/template', [SupplierController::class, 'importTemplate'])->name('suppliers.import.template');
        Route::post('/suppliers/import', [SupplierController::class, 'import'])->name('suppliers.import');

        Route::get('/transactions/import/template', [TransactionController::class, 'importTemplate'])->name('transactions.import.template');
        Route::post('/transactions/import', [TransactionController::class, 'import'])->name('transactions.import');

        Route::get('/customers/{customer}/statement/pdf', [CustomerController::class, 'statementPdf'])->name('customers.statement.pdf');
        Route::get('/suppliers/{supplier}/statement/pdf', [SupplierController::class, 'statementPdf'])->name('suppliers.statement.pdf');
    });