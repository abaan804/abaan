<?php

namespace Modules\VideoDownloader\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartDownloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('videodownloader.create-download');
    }

    public function rules(): array
    {
        return [
            'url'          => 'required|string|url|max:2048',
            'format_id'    => 'required|string|max:100',
            'quality'      => 'nullable|string|max:50',
            'format_ext'   => 'nullable|string|max:10',
            'is_audio_only'=> 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'url.required'       => __('Video URL is required.'),
            'format_id.required' => __('Please select a download format.'),
        ];
    }
}