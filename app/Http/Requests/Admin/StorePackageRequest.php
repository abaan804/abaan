<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255',
            'name_ur' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:packages,slug',

            'description_en' => 'nullable|string',
            'description_ur' => 'nullable|string',
            'description_ar' => 'nullable|string',

            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',

            'is_trial_package' => 'boolean',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',

            'features' => 'nullable|array',
            'features.*.feature_key' => 'required_with:features|string|max:255',
            'features.*.feature_label_en' => 'required_with:features|string|max:255',
            'features.*.feature_label_ur' => 'nullable|string|max:255',
            'features.*.feature_label_ar' => 'nullable|string|max:255',
            'features.*.value' => 'nullable|string|max:255',
        ];
    }
}