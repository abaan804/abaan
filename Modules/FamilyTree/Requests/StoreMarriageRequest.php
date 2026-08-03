<?php

namespace Modules\FamilyTree\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarriageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('familytree.manage-relationships');
    }

    public function rules(): array
    {
        return [
            'husband_id' => 'required|exists:ft_members,id',
            'wife_id' => 'required|exists:ft_members,id|different:husband_id',
            'marriage_date' => 'nullable|date',
            'marriage_place' => 'nullable|string|max:255',
            'marriage_type' => 'required|in:nikah,civil,other',
            'status' => 'required|in:active,divorced,widowed',
            'divorce_date' => 'nullable|date|after_or_equal:marriage_date',
            'notes' => 'nullable|string',
        ];
    }
}