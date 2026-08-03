<?php

namespace Modules\Ledger\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Modules\Ledger\Models\LedgerTransaction;

class LedgerReportService
{
    /**
     * Base query for transactions within a date range, with common filters applied.
     * Used as the shared starting point for every report in Phase 7+ (Cash Book,
     * Income/Expense, Customer/Supplier Ledger, etc.) so filter logic isn't duplicated
     * across multiple report controllers.
     */
    public function filteredQuery(int $companyId, array $filters = []): Builder
    {
        $query = LedgerTransaction::where('company_id', $companyId)
            ->with(['customer', 'supplier', 'category', 'paymentMethod']);

        if (! empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', Carbon::parse($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', Carbon::parse($filters['date_to']));
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['payment_method_id'])) {
            $query->where('payment_method_id', $filters['payment_method_id']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        if (! empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('reference_no', 'like', "%{$filters['search']}%")
                  ->orWhere('notes', 'like', "%{$filters['search']}%");
            });
        }

        return $query;
    }

    /**
     * Income vs Expense totals for a date range — powers Income Report, Expense Report,
     * and the Profit/Loss report.
     */
    public function incomeExpenseTotals(int $companyId, array $filters = []): array
    {
        $query = $this->filteredQuery($companyId, $filters);

        $income = (clone $query)->where('type', 'income')->sum('amount');
        $expense = (clone $query)->where('type', 'expense')->sum('amount');

        return [
            'income' => round((float) $income, 2),
            'expense' => round((float) $expense, 2),
            'profit' => round((float) ($income - $expense), 2),
        ];
    }

    /**
     * Daily totals for a date range — powers the Dashboard's monthly graph and
     * Daily/Weekly/Monthly reports.
     */
    public function dailyTotals(int $companyId, string $dateFrom, string $dateTo): \Illuminate\Support\Collection
    {
        return LedgerTransaction::where('company_id', $companyId)
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->selectRaw("
                transaction_date,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            ")
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get();
    }

    /**
     * Outstanding receivables: every active customer with a positive balance.
     */
    public function outstandingReceivables(int $companyId): \Illuminate\Support\Collection
    {
        $balanceService = app(\Modules\Ledger\Services\LedgerBalanceService::class);

        return \Modules\Ledger\Models\LedgerCustomer::where('company_id', $companyId)
            ->where('status', 'active')
            ->get()
            ->map(fn ($customer) => [
                'party' => $customer,
                'balance' => $balanceService->customerBalance($customer),
            ])
            ->filter(fn ($row) => $row['balance'] > 0)
            ->sortByDesc('balance')
            ->values();
    }

    /**
     * Outstanding payables: every active supplier with a positive balance.
     */
    public function outstandingPayables(int $companyId): \Illuminate\Support\Collection
    {
        $balanceService = app(\Modules\Ledger\Services\LedgerBalanceService::class);

        return \Modules\Ledger\Models\LedgerSupplier::where('company_id', $companyId)
            ->where('status', 'active')
            ->get()
            ->map(fn ($supplier) => [
                'party' => $supplier,
                'balance' => $balanceService->supplierBalance($supplier),
            ])
            ->filter(fn ($row) => $row['balance'] > 0)
            ->sortByDesc('balance')
            ->values();
    }

    /**
     * Period summary (daily/weekly/monthly/yearly) — groups income/expense totals
     * by the requested grain between two dates.
     */
    public function periodSummary(int $companyId, string $dateFrom, string $dateTo, string $grain = 'daily'): \Illuminate\Support\Collection
    {
        $format = match ($grain) {
            'weekly' => '%x-W%v',   // ISO year-week
            'monthly' => '%Y-%m',
            'yearly' => '%Y',
            default => '%Y-%m-%d', // daily
        };

        return LedgerTransaction::where('company_id', $companyId)
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->selectRaw("
                DATE_FORMAT(transaction_date, ?) as period,
                MIN(transaction_date) as period_start,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense,
                SUM(CASE WHEN type IN ('debit','opening_balance') THEN amount ELSE 0 END) as debit,
                SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as credit
            ", [$this->mysqlDateFormat($grain)])
            ->groupBy('period')
            ->orderBy('period_start')
            ->get();
    }

    protected function mysqlDateFormat(string $grain): string
    {
        return match ($grain) {
            'weekly' => '%x-W%v',
            'monthly' => '%Y-%m',
            'yearly' => '%Y',
            default => '%Y-%m-%d',
        };
    }

    /**
     * Very basic Balance Sheet: total receivables (asset) vs total payables (liability)
     * plus company cash balance — a simplified snapshot, not a full accounting statement.
     */
    public function basicBalanceSheet(int $companyId): array
    {
        $balanceService = app(\Modules\Ledger\Services\LedgerBalanceService::class);

        $cash = $balanceService->companyCashBalance($companyId);
        $receivables = $balanceService->totalReceivables($companyId);
        $payables = $balanceService->totalPayables($companyId);

        return [
            'cash' => $cash,
            'receivables' => $receivables,
            'total_assets' => $cash + $receivables,
            'payables' => $payables,
            'total_liabilities' => $payables,
            'net_position' => ($cash + $receivables) - $payables,
        ];
    }
}
 