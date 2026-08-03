<?php

namespace Modules\Masjid\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('masjid.manage-settings');
    }

    public function rules(): array
    {
        return [
            'currency_symbol' => 'required|string|max:10',
            'currency_code' => 'required|string|max:3',
            'currency_position' => 'required|in:before,after',
            'receipt_prefix' => 'required|string|max:20',
            'default_reminder_days' => 'required|integer|min:1|max:30',
            'notification_whatsapp' => 'boolean',
            'notification_sms' => 'boolean',
            'notification_email' => 'boolean',
            'default_language' => 'required|in:en,ur,ar',
        ];
    }
}