<?php

namespace Modules\FamilyTree\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRelationshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('familytree.manage-relationships');
    }

    public function rules(): array
    {
        return [
            'related_member_id' => 'required|exists:ft_members,id',
            'relationship_type' => 'required|in:adoptive_father,adoptive_mother,step_father,step_mother,guardian,foster_child,custom',
            'label' => 'nullable|required_if:relationship_type,custom|string|max:100',
            'notes' => 'nullable|string',
        ];
    }
}