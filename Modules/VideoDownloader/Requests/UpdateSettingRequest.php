<?php

namespace Modules\VideoDownloader\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('videodownloader.manage-settings');
    }

    public function rules(): array
    {
        return [
            'max_file_size_mb'         => 'required|integer|min:1|max:10240',
            'max_concurrent_downloads' => 'required|integer|min:1|max:20',
            'retention_days'           => 'required|integer|min:1|max:365',
            'allow_audio_only'         => 'nullable|boolean',
            'storage_limit_gb'         => 'nullable|numeric|min:0.1|max:1000',
            'allowed_platforms'        => 'nullable|array',
            'allowed_platforms.*'      => 'string|in:youtube,twitter,instagram,tiktok,facebook,vimeo,dailymotion,generic',
            'notify_on_complete'       => 'nullable|boolean',
            'notify_on_failure'        => 'nullable|boolean',
        ];
    }
}