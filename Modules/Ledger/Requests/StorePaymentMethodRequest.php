<?php

namespace Modules\Ledger\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('easykhata.manage-categories'); // payment methods share the categories permission — simple shared settings area
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ];
    }
}