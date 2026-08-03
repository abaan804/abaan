<?php

namespace Modules\Masjid\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('masjid.manage-payments');
    }

    public function rules(): array
    {
        return [
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,online,cheque',
            'reference_no' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}