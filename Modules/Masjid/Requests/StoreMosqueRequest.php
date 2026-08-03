<?php

namespace Modules\Masjid\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMosqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('masjid.manage-mosque-profile');
    }

    public function rules(): array
    {
        return [
            'village_name' => 'required|string|max:255',
            'mosque_name' => 'required|string|max:255',
            'scholar_name' => 'nullable|string|max:255',
            'scholar_contact' => 'nullable|string|max:30',
            'scholar_email' => 'nullable|email|max:255',
            'committee_name' => 'nullable|string|max:255',
            'mosque_contact' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'map_link' => 'nullable|url|max:500',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
        ];
    }
}