<?php

namespace Modules\Ledger\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Ledger\Models\LedgerCategory;
use Modules\Ledger\Models\LedgerCustomer;
use Modules\Ledger\Models\LedgerPaymentMethod;
use Modules\Ledger\Models\LedgerSupplier;
use Modules\Ledger\Services\LedgerReportService;
use Modules\Ledger\Services\LedgerPdfService;

class ReportController extends Controller
{
    public function __construct(
    protected LedgerReportService $reportService,
    protected LedgerPdfService $pdfService
    )
    {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        return view('ledger::reports.index');
    }

    public function cashBook(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        return view('ledger::reports.cash-book', [
            'paymentMethods' => LedgerPaymentMethod::where('status', 'active')->get(),
        ]);
    }

    public function cashBookTable(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        $filters = $request->only(['date_from', 'date_to', 'payment_method_id']);
        $companyId = $request->user()->company_id;

        $transactions = $this->reportService
            ->filteredQuery($companyId, $filters)
            ->orderBy('transaction_date')->orderBy('id')
            ->get();

        $openingBalance = 0; // running balance starts at 0 for the selected period view
        $running = $openingBalance;
        $rows = $transactions->map(function ($tx) use (&$running) {
            $inflow = in_array($tx->type, ['income', 'debit', 'opening_balance']) ? $tx->amount : 0;
            $outflow = in_array($tx->type, ['expense', 'credit']) ? $tx->amount : 0;
            $running += $inflow - $outflow;
            return [
                'transaction' => $tx,
                'inflow' => $inflow,
                'outflow' => $outflow,
                'running_balance' => $running,
            ];
        });

        $totals = [
            'inflow' => $rows->sum('inflow'),
            'outflow' => $rows->sum('outflow'),
            'closing' => $running,
        ];

        return view('ledger::reports.cash-book-table', compact('rows', 'totals', 'openingBalance'));
    }

    public function incomeExpense(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        return view('ledger::reports.income-expense', [
            'categories' => LedgerCategory::where('status', 'active')->get(),
        ]);
    }

    public function incomeExpenseTable(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        $filters = $request->only(['date_from', 'date_to', 'category_id']);
        $companyId = $request->user()->company_id;

        $totals = $this->reportService->incomeExpenseTotals($companyId, $filters);

        $byCategory = $this->reportService
            ->filteredQuery($companyId, array_merge($filters, ['type' => null]))
            ->whereIn('type', ['income', 'expense'])
            ->get()
            ->groupBy(fn ($tx) => $tx->category?->name ?? __('Uncategorized'))
            ->map(function ($group) {
                return [
                    'income' => $group->where('type', 'income')->sum('amount'),
                    'expense' => $group->where('type', 'expense')->sum('amount'),
                ];
            });

        return view('ledger::reports.income-expense-table', compact('totals', 'byCategory'));
    }

    public function cashBookPdf(Request $request)
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        $company = $request->user()->company;
        $filters = $request->only(['date_from', 'date_to', 'payment_method_id']);
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? now()->toDateString();

        $transactions = $this->reportService
            ->filteredQuery($company->id, $filters)
            ->orderBy('transaction_date')->orderBy('id')
            ->get();

        $running = 0;
        $rows = $transactions->map(function ($tx) use (&$running) {
            $inflow = in_array($tx->type, ['income', 'debit', 'opening_balance']) ? $tx->amount : 0;
            $outflow = in_array($tx->type, ['expense', 'credit']) ? $tx->amount : 0;
            $running += $inflow - $outflow;
            return ['transaction' => $tx, 'inflow' => $inflow, 'outflow' => $outflow, 'running_balance' => $running];
        });

        $totals = ['inflow' => $rows->sum('inflow'), 'outflow' => $rows->sum('outflow'), 'closing' => $running];

        return $this->pdfService->download('ledger::reports.pdf.cash-book', [
            'letterhead' => $this->pdfService->companyLetterhead($company),
            'rows' => $rows,
            'totals' => $totals,
            'dateFrom' => formatDate($dateFrom),
            'dateTo' => formatDate($dateTo),
        ], 'cash-book-' . now()->format('Y-m-d') . '.pdf');
    }

    public function incomeExpensePdf(Request $request)
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        $company = $request->user()->company;
        $filters = $request->only(['date_from', 'date_to', 'category_id']);
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? now()->toDateString();

        $totals = $this->reportService->incomeExpenseTotals($company->id, $filters);

        $byCategory = $this->reportService
            ->filteredQuery($company->id, array_merge($filters, ['type' => null]))
            ->whereIn('type', ['income', 'expense'])
            ->get()
            ->groupBy(fn ($tx) => $tx->category?->name ?? __('Uncategorized'))
            ->map(fn ($group) => [
                'income' => $group->where('type', 'income')->sum('amount'),
                'expense' => $group->where('type', 'expense')->sum('amount'),
            ]);

        return $this->pdfService->download('ledger::reports.pdf.income-expense', [
            'letterhead' => $this->pdfService->companyLetterhead($company),
            'byCategory' => $byCategory,
            'totals' => $totals,
            'dateFrom' => formatDate($dateFrom),
            'dateTo' => formatDate($dateTo),
        ], 'income-expense-' . now()->format('Y-m-d') . '.pdf');
    }

    public function outstanding(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        return view('ledger::reports.outstanding');
    }

    public function outstandingTable(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        $companyId = $request->user()->company_id;
        $type = $request->get('type', 'receivables');

        $rows = $type === 'payables'
            ? $this->reportService->outstandingPayables($companyId)
            : $this->reportService->outstandingReceivables($companyId);

        return view('ledger::reports.outstanding-table', compact('rows', 'type'));
    }

    public function outstandingPdf(Request $request)
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        $company = $request->user()->company;
        $type = $request->get('type', 'receivables');

        $rows = $type === 'payables'
            ? $this->reportService->outstandingPayables($company->id)
            : $this->reportService->outstandingReceivables($company->id);

        return $this->pdfService->download('ledger::reports.pdf.outstanding', [
            'letterhead' => $this->pdfService->companyLetterhead($company),
            'rows' => $rows,
            'type' => $type,
        ], ($type === 'payables' ? 'outstanding-payables-' : 'outstanding-receivables-') . now()->format('Y-m-d') . '.pdf');
    }

    public function periodSummary(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        return view('ledger::reports.period-summary');
    }

    public function periodSummaryTable(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        $companyId = $request->user()->company_id;
        $grain = $request->get('grain', 'monthly');
        $dateFrom = $request->get('date_from', now()->subMonths(6)->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $rows = $this->reportService->periodSummary($companyId, $dateFrom, $dateTo, $grain);

        return view('ledger::reports.period-summary-table', compact('rows', 'grain'));
    }

    public function balanceSheet(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.view-reports'), 403);

        $sheet = $this->reportService->basicBalanceSheet($request->user()->company_id);

        return view('ledger::reports.balance-sheet', compact('sheet'));
    }
}