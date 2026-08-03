@csrf

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Name (Multi-language)') }}</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('English') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name_en" value="{{ old('name_en', $package->name_en) }}"
                           class="form-control @error('name_en') is-invalid @enderror" required>
                    @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Urdu') }}</label>
                    <input type="text" name="name_ur" value="{{ old('name_ur', $package->name_ur) }}"
                           class="form-control" dir="rtl" style="font-family: 'NotoNastaliqUrdu', serif;">
                </div>
                <div class="mb-0">
                    <label class="form-label">{{ __('Arabic') }}</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $package->name_ar) }}"
                           class="form-control" dir="rtl">
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Description (Multi-language)') }}</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('English') }}</label>
                    <textarea name="description_en" rows="3" class="form-control">{{ old('description_en', $package->description_en) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Urdu') }}</label>
                    <textarea name="description_ur" rows="3" class="form-control" dir="rtl" style="font-family: 'NotoNastaliqUrdu', serif;">{{ old('description_ur', $package->description_ur) }}</textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label">{{ __('Arabic') }}</label>
                    <textarea name="description_ar" rows="3" class="form-control" dir="rtl">{{ old('description_ar', $package->description_ar) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>{{ __('Features') }}</strong>
                <button type="button" id="add-feature-btn" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg"></i> {{ __('Add Feature') }}
                </button>
            </div>
            <div class="card-body">
                <div id="features-container">
                    @forelse ($package->features ?? [] as $i => $feature)
                        <div class="feature-row border rounded p-3 mb-3">
                            <input type="hidden" name="features[{{ $i }}][id]" value="{{ $feature->id }}">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <input type="text" name="features[{{ $i }}][feature_key]" value="{{ $feature->feature_key }}"
                                           class="form-control form-control-sm" placeholder="{{ __('Key (e.g. max_users)') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="features[{{ $i }}][feature_label_en]" value="{{ $feature->feature_label_en }}"
                                           class="form-control form-control-sm" placeholder="{{ __('Label (EN)') }}" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="features[{{ $i }}][feature_label_ur]" value="{{ $feature->feature_label_ur }}"
                                           class="form-control form-control-sm" placeholder="{{ __('Label (UR)') }}" dir="rtl">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="features[{{ $i }}][value]" value="{{ $feature->value }}"
                                           class="form-control form-control-sm" placeholder="{{ __('Value') }}">
                                </div>
                                <div class="col-md-2 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-feature-btn">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Settings') }}</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Slug') }} <span class="text-danger">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $package->slug) }}"
                           class="form-control @error('slug') is-invalid @enderror" required>
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Monthly Price') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0" name="price_monthly" value="{{ old('price_monthly', $package->price_monthly) }}"
                               class="form-control @error('price_monthly') is-invalid @enderror" required>
                    </div>
                    @error('price_monthly') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Yearly Price') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0" name="price_yearly" value="{{ old('price_yearly', $package->price_yearly) }}"
                               class="form-control @error('price_yearly') is-invalid @enderror" required>
                    </div>
                    @error('price_yearly') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Sort Order') }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $package->sort_order ?? 0) }}" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $package->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ old('status', $package->status) === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>
                <div class="form-check">
                    <input type="hidden" name="is_trial_package" value="0">
                    <input type="checkbox" name="is_trial_package" value="1" id="is_trial_package" class="form-check-input"
                           {{ old('is_trial_package', $package->is_trial_package) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_trial_package">{{ __('Allow Free Trial') }}</label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            {{ $package->exists ? __('Update Package') : __('Create Package') }}
        </button>
    </div>
</div>

@push('scripts')
<script>
(function () {
    let featureIndex = {{ ($package->features ?? collect())->count() }};
    const container = document.getElementById('features-container');
    const addBtn = document.getElementById('add-feature-btn');

    addBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'feature-row border rounded p-3 mb-3';
        row.innerHTML = `
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="features[${featureIndex}][feature_key]" class="form-control form-control-sm" placeholder="{{ __('Key (e.g. max_users)') }}" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="features[${featureIndex}][feature_label_en]" class="form-control form-control-sm" placeholder="{{ __('Label (EN)') }}" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="features[${featureIndex}][feature_label_ur]" class="form-control form-control-sm" placeholder="{{ __('Label (UR)') }}" dir="rtl">
                </div>
                <div class="col-md-2">
                    <input type="text" name="features[${featureIndex}][value]" class="form-control form-control-sm" placeholder="{{ __('Value') }}">
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-feature-btn"><i class="bi bi-trash"></i></button>
                </div>
            </div>`;
        container.appendChild(row);
        featureIndex++;
    });

    container.addEventListener('click', function (e) {
        if (e.target.closest('.remove-feature-btn')) {
            e.target.closest('.feature-row').remove();
        }
    });
})();
</script>
@endpush