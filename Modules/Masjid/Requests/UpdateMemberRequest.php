<?php

namespace Modules\Masjid\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('masjid.manage-members');
    }

    public function rules(): array
    {
        $mosque = $this->route('mosque');
        $mosqueId = $mosque instanceof \Modules\Masjid\Models\MasjidMosque ? $mosque->id : $mosque;
        $memberId = $this->route('member')?->id ?? $this->route('member');

        return [
            'name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'cnic' => [
                'nullable', 'string', 'max:20',
                "unique:masjid_members,cnic,{$memberId},id,mosque_id,{$mosqueId},deleted_at,NULL",
            ],
            'mobile' => 'required|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'occupation' => 'nullable|string|max:100',
            'joining_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|max:2048',
        ];
    }
}