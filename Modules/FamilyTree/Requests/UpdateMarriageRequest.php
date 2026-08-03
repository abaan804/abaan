<?php

namespace Modules\FamilyTree\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMarriageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('familytree.manage-relationships');
    }

    public function rules(): array
    {
        return [
            'marriage_date' => 'nullable|date',
            'marriage_place' => 'nullable|string|max:255',
            'marriage_type' => 'required|in:nikah,civil,other',
            'status' => 'required|in:active,divorced,widowed',
            'divorce_date' => 'nullable|date|after_or_equal:marriage_date',
            'notes' => 'nullable|string',
        ];
    }
}