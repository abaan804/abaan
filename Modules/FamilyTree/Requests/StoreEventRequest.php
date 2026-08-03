<?php

namespace Modules\FamilyTree\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('familytree.manage-events');
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:ft_members,id',
            'event_type' => 'required|in:birth,bismillah,school_admission,graduation,hifz,marriage,job_started,business_started,migration,house_purchased,award,retirement,death,custom',
            'event_title' => 'nullable|required_if:event_type,custom|string|max:255',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'media.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx',
        ];
    }
}