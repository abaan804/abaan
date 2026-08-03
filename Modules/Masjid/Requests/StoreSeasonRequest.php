<?php

namespace Modules\Masjid\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('masjid.manage-seasons');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'contribution_amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'frequency' => 'required|in:monthly,quarterly,seasonal,yearly,custom',
            'status' => 'required|in:active,inactive,completed',
            'auto_assign' => 'boolean',
        ];
    }
}