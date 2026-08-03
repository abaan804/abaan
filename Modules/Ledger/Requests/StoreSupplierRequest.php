<?php

namespace Modules\Ledger\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('easykhata.manage-suppliers');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:active,inactive',
        ];
    }
}