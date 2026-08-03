<?php

namespace Modules\Ledger\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('easykhata.manage-transactions');
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:credit,debit,income,expense,transfer,opening_balance,adjustment',
            'customer_id' => 'nullable|exists:ledger_customers,id',
            'supplier_id' => 'nullable|exists:ledger_suppliers,id',
            'category_id' => 'nullable|exists:ledger_categories,id',
            'payment_method_id' => 'nullable|exists:ledger_payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}