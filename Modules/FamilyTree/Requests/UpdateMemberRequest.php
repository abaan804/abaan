<?php

namespace Modules\FamilyTree\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('familytree.manage-members');
    }

    public function rules(): array
    {
        $familyId = $this->route('family')?->id ?? $this->route('family');
        $memberId = $this->route('member')?->id ?? $this->route('member');
       
        $rules = (new StoreMemberRequest())->rules($familyId );
       
        // Prevent member from being their own parent
        $rules['father_id'][] = function ($attribute, $value, $fail) use ($memberId) {
            if ($value && $value == $memberId) {
                $fail(__('A member cannot be their own father.'));
            }
        };
        $rules['mother_id'][] = function ($attribute, $value, $fail) use ($memberId) {
            if ($value && $value == $memberId) {
                $fail(__('A member cannot be their own mother.'));
            }
        };

        return $rules;
    }
}