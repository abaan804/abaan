<?php

namespace Modules\Masjid\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMosqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('masjid.manage-mosque-profile');
    }

    public function rules(): array
    {
        return (new StoreMosqueRequest())->rules();
    }
}