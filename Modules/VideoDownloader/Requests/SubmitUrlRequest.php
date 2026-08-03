<?php

namespace Modules\VideoDownloader\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('videodownloader.create-download');
    }

    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'string',
                'max:2048',
                'url',
                // Must be http or https
                function ($attribute, $value, $fail) {
                    $scheme = parse_url($value, PHP_URL_SCHEME);
                    if (! in_array(strtolower($scheme ?? ''), ['http', 'https'])) {
                        $fail(__('Only HTTP and HTTPS URLs are supported.'));
                    }
                },
                // Must have a valid host
                function ($attribute, $value, $fail) {
                    $host = parse_url($value, PHP_URL_HOST);
                    if (empty($host)) {
                        $fail(__('The URL must contain a valid domain.'));
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => __('Please enter a video URL.'),
            'url.url'      => __('Please enter a valid URL including http:// or https://'),
            'url.max'      => __('The URL is too long.'),
        ];
    }
}