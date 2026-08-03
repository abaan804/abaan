<?php

namespace Modules\Masjid\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('masjid.manage-payments');
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:masjid_members,id',
            'season_id' => 'required|exists:masjid_seasons,id',
            'season_member_id' => 'required|exists:masjid_season_members,id',
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,online,cheque',
            'reference_no' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'received_by' => 'nullable|exists:users,id',
            'attachments.*' => 'nullable|file|max:5120',
        ];
    }
}