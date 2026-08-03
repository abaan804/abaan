<?php

namespace Modules\Ledger\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Ledger\Models\LedgerActivityLog;
use Modules\Ledger\Models\LedgerTransaction;

class LedgerTransactionService
{
    /**
     * Create a new ledger transaction and log the activity.
     */
    public function create(array $data): LedgerTransaction
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            $transaction = LedgerTransaction::create($data);

            $this->logActivity('transaction.created', $transaction, [
                'after' => $transaction->only(['type', 'amount', 'transaction_date', 'customer_id', 'supplier_id']),
            ]);

            return $transaction;
        });
    }

    /**
     * Update an existing transaction and log a before/after snapshot.
     */
    public function update(LedgerTransaction $transaction, array $data): LedgerTransaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $before = $transaction->only(['type', 'amount', 'transaction_date', 'customer_id', 'supplier_id', 'category_id', 'payment_method_id', 'notes']);

            $data['updated_by'] = Auth::id();
            $transaction->update($data);

            $this->logActivity('transaction.updated', $transaction, [
                'before' => $before,
                'after' => $transaction->only(['type', 'amount', 'transaction_date', 'customer_id', 'supplier_id', 'category_id', 'payment_method_id', 'notes']),
            ]);

            return $transaction;
        });
    }

    /**
     * Soft-delete a transaction and log the activity.
     */
    public function delete(LedgerTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $this->logActivity('transaction.deleted', $transaction, [
                'before' => $transaction->only(['type', 'amount', 'transaction_date', 'customer_id', 'supplier_id']),
            ]);

            $transaction->delete();
        });
    }

    protected function logActivity(string $action, LedgerTransaction $transaction, array $properties): void
    {
        LedgerActivityLog::create([
            'company_id' => $transaction->company_id,
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => LedgerTransaction::class,
            'subject_id' => $transaction->id,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}