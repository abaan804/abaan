<?php

namespace Modules\Ledger\Services;

use Illuminate\Support\Facades\DB;
use Modules\Ledger\Models\LedgerCustomer;
use Modules\Ledger\Models\LedgerSupplier;
use Modules\Ledger\Models\LedgerTransaction;

class LedgerBalanceService
{
    /**
     * Calculate a customer's current balance.
     *
     * Convention: positive balance = customer OWES the company money (receivable).
     * Negative balance = company owes the customer (e.g., overpayment/credit).
     *
     * debit / opening_balance => increases what the customer owes (+)
     * credit                  => decreases what the customer owes (-)
     */
    public function customerBalance(LedgerCustomer $customer): float
    {
        $sums = $this->sumByType($customer->transactions());

        $balance = $customer->opening_balance;
        $balance += $sums['credit'] + $sums['opening_balance'] + $sums['adjustment'];
        $balance -= $sums['debit'];
         
        return round((float) $balance, 2);
    }

    /**
     * Calculate a supplier's current balance.
     *
     * Convention: positive balance = company OWES the supplier (payable).
     * Negative balance = supplier owes the company (e.g., return/refund pending).
     *
     * credit / opening_balance => increases what the company owes the supplier (+)
     * debit                    => decreases what the company owes the supplier (-)
     */
    public function supplierBalance(LedgerSupplier $supplier): float
    {
        $sums = $this->sumByType($supplier->transactions());

        $balance = $supplier->opening_balance;
        $balance += $sums['debit'] + $sums['opening_balance'] + $sums['adjustment'];
        $balance -= $sums['credit'];
   
        return round((float) $balance, 2);
    }

    /**
     * Company-wide cash/bank balance across all payment methods — sum of all
     * income & debit-type inflows minus expense & credit-type outflows.
     * This powers the Dashboard's "Current Balance" card.
     */
    public function companyCashBalance(int $companyId): float
    {
        $row = LedgerTransaction::where('company_id', $companyId)
            ->selectRaw("
                SUM(CASE WHEN type IN ('income', 'debit', 'opening_balance') THEN amount ELSE 0 END) as inflow,
                SUM(CASE WHEN type IN ('expense', 'credit') THEN amount ELSE 0 END) as outflow
            ")
            ->first();

        return round((float) (($row->inflow ?? 0) - ($row->outflow ?? 0)), 2);
    }

    /**
     * Total outstanding receivables across all customers (sum of positive balances only).
     */
    public function totalReceivables(int $companyId): float
    {
        return $this->totalOutstanding(LedgerCustomer::class, $companyId, fn ($c) => $this->customerBalance($c));
    }

    /**
     * Total outstanding payables across all suppliers (sum of positive balances only).
     */
    public function totalPayables(int $companyId): float
    {
        return $this->totalOutstanding(LedgerSupplier::class, $companyId, fn ($s) => $this->supplierBalance($s));
    }

    /**
     * Sum transaction amounts grouped by type for a given relation query.
     * Returns an array with every known type defaulted to 0 (avoids undefined-key bugs).
     */
    protected function sumByType($relationQuery): array
    {
        $defaults = [
            'credit' => 0, 'debit' => 0, 'income' => 0, 'expense' => 0,
            'transfer' => 0, 'opening_balance' => 0, 'adjustment' => 0,
        ];

        $rows = $relationQuery
            ->select('type')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        foreach ($rows as $type => $total) {
            $defaults[$type] = (float) $total;
        }

        return $defaults;
    }

    /**
     * Sum only the positive balances across all active records of a given model —
     * used for "Total Outstanding Receivables/Payables" dashboard stats.
     *
     * Note: iterates in PHP rather than pure SQL since balance includes opening_balance
     * plus a derived sum — acceptable for Phase 1 at typical SMB record counts; if a
     * company's customer/supplier list grows very large, this can be optimized with a
     * single aggregate SQL query later without changing the public method signature.
     */
    protected function totalOutstanding(string $modelClass, int $companyId, callable $balanceFn): float
    {
        $records = $modelClass::where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        $total = 0;
        foreach ($records as $record) {
            $balance = $balanceFn($record);
            if ($balance > 0) {
                $total += $balance;
            }
        }

        return round($total, 2);
    }
}