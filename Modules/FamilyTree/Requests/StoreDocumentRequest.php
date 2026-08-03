<?php

namespace Modules\FamilyTree\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('familytree.manage-documents');
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:ft_members,id',
            'document_type' => 'required|in:cnic,birth_certificate,marriage_certificate,educational,passport,photo,other',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xlsx',
            'notes' => 'nullable|string',
        ];
    }
}