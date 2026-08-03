@csrf

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Name (Multi-language)') }}</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('English') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name_en" value="{{ old('name_en', $module->name_en) }}"
                           class="form-control @error('name_en') is-invalid @enderror" required>
                    @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Urdu') }}</label>
                    <input type="text" name="name_ur" value="{{ old('name_ur', $module->name_ur) }}"
                           class="form-control" dir="rtl" style="font-family: 'NotoNastaliqUrdu', serif;">
                </div>
                <div class="mb-0">
                    <label class="form-label">{{ __('Arabic') }}</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $module->name_ar) }}" class="form-control" dir="rtl">
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>{{ __('Description (Multi-language)') }}</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('English') }}</label>
                    <textarea name="description_en" rows="3" class="form-control">{{ old('description_en', $module->description_en) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Urdu') }}</label>
                    <textarea name="description_ur" rows="3" class="form-control" dir="rtl" style="font-family: 'NotoNastaliqUrdu', serif;">{{ old('description_ur', $module->description_ur) }}</textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label">{{ __('Arabic') }}</label>
                    <textarea name="description_ar" rows="3" class="form-control" dir="rtl">{{ old('description_ar', $module->description_ar) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Settings') }}</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Key (unique identifier)') }} <span class="text-danger">*</span></label>
                    <input type="text" name="key" value="{{ old('key', $module->key) }}"
                           class="form-control @error('key') is-invalid @enderror" placeholder="e.g. ledger, pos"
                           {{ $module->exists ? 'readonly' : '' }} required>
                    @error('key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if ($module->exists)
                        <small class="text-muted">{{ __('Key cannot be changed after creation.') }}</small>
                    @endif
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Icon (Bootstrap Icon class)') }}</label>
                    <input type="text" name="icon" value="{{ old('icon', $module->icon) }}"
                           class="form-control" placeholder="bi-journal-bookmark">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Sort Order') }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $module->sort_order ?? 0) }}" class="form-control">
                </div>
                <div class="mb-0">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $module->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="coming_soon" {{ old('status', $module->status) === 'coming_soon' ? 'selected' : '' }}>{{ __('Coming Soon') }}</option>
                        <option value="disabled" {{ old('status', $module->status) === 'disabled' ? 'selected' : '' }}>{{ __('Disabled') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            {{ $module->exists ? __('Update Module') : __('Create Module') }}
        </button>
    </div>
</div>