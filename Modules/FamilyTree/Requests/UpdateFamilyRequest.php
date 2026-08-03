<?php

namespace Modules\FamilyTree\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFamilyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('familytree.manage-families');
    }

    public function rules(): array
    {
        return (new StoreFamilyRequest())->rules();
    }
}