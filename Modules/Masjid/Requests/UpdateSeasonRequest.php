<?php

namespace Modules\Masjid\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('masjid.manage-seasons');
    }

    public function rules(): array
    {
        return (new StoreSeasonRequest())->rules();
    }
}