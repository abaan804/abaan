<?php

namespace Modules\FamilyTree\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('familytree.manage-members');
    }

    public function rules($familyId = Null): array
    {
      
        $familyId = $this->route('family')?->id
            ?? $familyId; 
        return [
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'place_of_birth' => 'nullable|string|max:255',
            'date_of_death' => 'nullable|date|after_or_equal:date_of_birth',
            'burial_place' => 'nullable|string|max:255',
            'life_status' => 'required|in:living,deceased',
            'father_id' => [
                'nullable',
                'exists:ft_members,id',
                // Father must belong to the same family
                function ($attribute, $value, $fail) use ($familyId) {
                    if ($value && !\Modules\FamilyTree\Models\FtMember::where('id', $value)->where('family_id', $familyId)->exists()) {
                        $fail(__('The selected father does not belong to this family.'));
                    }
                },
            ],
            'mother_id' => [
                'nullable',
                'exists:ft_members,id',
                function ($attribute, $value, $fail) use ($familyId) {
                    if ($value && !\Modules\FamilyTree\Models\FtMember::where('id', $value)->where('family_id', $familyId)->exists()) {
                        $fail(__('The selected mother does not belong to this family.'));
                    }
                },
            ],
            'father_name_text' => 'nullable|string|max:255',
            'mother_name_text' => 'nullable|string|max:255',
            'cnic' => 'nullable|string|max:20',
            'passport_number' => 'nullable|string|max:30',
            'contact_number' => 'nullable|string|max:30',
            'whatsapp_number' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'current_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'occupation' => 'nullable|string|max:100',
            'education' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:10',
            'religion' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'marital_status' => 'required|in:married,unmarried,divorced,widowed',
            'profile_photo' => 'nullable|image|max:2048',
            'other_details' => 'nullable|string',
        ];
    }
}