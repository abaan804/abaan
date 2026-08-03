<?php

namespace Modules\Ledger\Imports;

use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Ledger\Models\LedgerCategory;
use Modules\Ledger\Models\LedgerCustomer;
use Modules\Ledger\Models\LedgerPaymentMethod;
use Modules\Ledger\Models\LedgerSupplier;
use Modules\Ledger\Services\LedgerTransactionService;

class TransactionsImport implements ToCollection, WithHeadingRow
{
    public array $imported = [];
    public array $failed = [];

    protected array $validTypes = ['credit', 'debit', 'income', 'expense', 'transfer', 'opening_balance', 'adjustment'];

    public function __construct(
        protected int $companyId,
        protected ?int $userId = null,
        protected LedgerTransactionService $transactionService
    ) {
    }

    public function collection(\Illuminate\Support\Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $rowNumber = $i + 2;

            $type = strtolower(trim($row['type'] ?? ''));
            $amount = is_numeric($row['amount'] ?? null) ? $row['amount'] : null;
            $date = trim($row['date'] ?? '');
            $customerName = trim($row['customer'] ?? '');
            $supplierName = trim($row['supplier'] ?? '');
            $categoryName = trim($row['category'] ?? '');
            $methodName = trim($row['payment_method'] ?? '');
            $reference = trim($row['reference_no'] ?? '');
            $notes = trim($row['notes'] ?? '');

            $errors = [];

            if (! in_array($type, $this->validTypes)) {
                $errors[] = "Invalid type '{$type}'. Must be one of: " . implode(', ', $this->validTypes);
            }
            if (! $amount || $amount <= 0) {
                $errors[] = 'Amount must be a positive number.';
            }
            if (! $date || ! strtotime($date)) {
                $errors[] = 'Date is missing or invalid (use YYYY-MM-DD).';
            }

            $customerId = null;
            $supplierId = null;

            if ($customerName) {
                $customer = LedgerCustomer::where('company_id', $this->companyId)
                    ->where('name', $customerName)->first();
                if (! $customer) {
                    $errors[] = "Customer '{$customerName}' not found. Please add this customer first.";
                } else {
                    $customerId = $customer->id;
                }
            }

            if ($supplierName) {
                $supplier = LedgerSupplier::where('company_id', $this->companyId)
                    ->where('name', $supplierName)->first();
                if (! $supplier) {
                    $errors[] = "Supplier '{$supplierName}' not found. Please add this supplier first.";
                } else {
                    $supplierId = $supplier->id;
                }
            }

            if (in_array($type, ['credit', 'debit']) && ! $customerId && ! $supplierId) {
                $errors[] = 'Credit/Debit transactions require a Customer or Supplier name.';
            }

            if ($errors) {
                $this->failed[] = [
                    'row' => $rowNumber,
                    'summary' => $type . ' / ' . ($customerName ?: $supplierName ?: '—'),
                    'errors' => $errors,
                ];
                continue;
            }

            // Categories and payment methods are auto-created if missing — low-risk lookup data.
            $categoryId = null;
            if ($categoryName) {
                $category = LedgerCategory::firstOrCreate(
                    ['company_id' => $this->companyId, 'name' => $categoryName],
                    ['type' => in_array($type, ['income']) ? 'income' : 'expense', 'status' => 'active']
                );
                $categoryId = $category->id;
            }

            $methodId = null;
            if ($methodName) {
                $method = LedgerPaymentMethod::firstOrCreate(
                    ['company_id' => $this->companyId, 'name' => $methodName],
                    ['status' => 'active']
                );
                $methodId = $method->id;
            }

            $this->transactionService->create([
                'company_id' => $this->companyId,
                'type' => $type,
                'customer_id' => $customerId,
                'supplier_id' => $supplierId,
                'category_id' => $categoryId,
                'payment_method_id' => $methodId,
                'amount' => $amount,
                'transaction_date' => date('Y-m-d', strtotime($date)),
                'reference_no' => $reference ?: null,
                'notes' => $notes ?: null,
            ]);

            $this->imported[] = $type . ' / ' . formatCurrency($amount);
        }
    }
}