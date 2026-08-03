<?php

namespace Modules\Ledger\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Ledger\Models\LedgerCustomer;
use Modules\Ledger\Models\LedgerReminder;
use Modules\Ledger\Models\LedgerSupplier;
use Modules\Ledger\Models\LedgerTransaction;
use Modules\Ledger\Services\LedgerBalanceService;
use Modules\Ledger\Services\LedgerReportService;

class DashboardController extends Controller
{
    public function __construct(
        protected LedgerBalanceService $balanceService,
        protected LedgerReportService $reportService
    ) {
    }

    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $today = now()->toDateString();

        $todayQuery = LedgerTransaction::where('company_id', $companyId)->whereDate('transaction_date', $today);

        $stats = [
            'today_transactions' => (clone $todayQuery)->count(),
            'today_income' => (clone $todayQuery)->where('type', 'income')->sum('amount'),
            'today_expense' => (clone $todayQuery)->where('type', 'expense')->sum('amount'),
            'current_balance' => $this->balanceService->companyCashBalance($companyId),
            'total_customers' => LedgerCustomer::where('company_id', $companyId)->where('status', 'active')->count(),
            'total_suppliers' => LedgerSupplier::where('company_id', $companyId)->where('status', 'active')->count(),
            'pending_receivables' => $this->balanceService->totalReceivables($companyId),
            'pending_payables' => $this->balanceService->totalPayables($companyId),
        ];

        $recentTransactions = LedgerTransaction::where('company_id', $companyId)
            ->with(['customer', 'supplier', 'category', 'paymentMethod'])
            ->latest('transaction_date')
            ->latest('id')
            ->take(8)
            ->get();

        $upcomingReminders = LedgerReminder::where('company_id', $companyId)
            ->where('status', 'pending')
            ->where('due_date', '>=', $today)
            ->orderBy('due_date')
            ->take(5)
            ->get();

        $monthlyTotals  = $this->reportService->dailyTotals(
            $companyId,
            now()->startOfMonth()->toDateString(),
            now()->endOfMonth()->toDateString()
        );

        $topCustomers = LedgerCustomer::where('company_id', $companyId)
            ->withSum('transactions as total_debit', 'amount')
            ->orderByDesc('total_debit')
            ->take(5)
            ->get();

        $topSuppliers = LedgerSupplier::where('company_id', $companyId)
            ->withSum('transactions as total_credit', 'amount')
            ->orderByDesc('total_credit')
            ->take(5)
            ->get();
         
        return view('ledger::dashboard.index', compact(
            'stats', 'recentTransactions', 'upcomingReminders', 'monthlyTotals', 'topCustomers', 'topSuppliers'
        ));
    }
}