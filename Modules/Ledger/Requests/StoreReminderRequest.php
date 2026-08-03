<?php

namespace Modules\Ledger\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('easykhata.manage-reminders');
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:ledger_customers,id',
            'supplier_id' => 'nullable|exists:ledger_suppliers,id',
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'amount' => 'nullable|numeric|min:0',
            'channel' => 'required|in:sms,whatsapp,email,in_app',
        ];
    }
}