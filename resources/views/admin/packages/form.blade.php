@extends('layouts.admin')
@section('title', isset($package) ? __('Edit Package') : __('Create Package'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ isset($package) ? __('Edit Package') : __('Create Package') }}</h4>
        <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>

    <form method="POST"
          action="{{ isset($package) ? route('admin.packages.update', $package) : route('admin.packages.store') }}">
        @csrf
        @if (isset($package)) @method('PUT') @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            {{-- Package Details --}}
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius:14px;">
                    <div class="card-header bg-white border-0">
                        <strong>{{ __('Package Details') }}</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">{{ __('Package Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $package->name ?? '') }}"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="{{ __('e.g. Basic, Professional, Enterprise') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('Monthly Price') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ setting('currency_symbol', 'Rs.') }}</span>
                                    <input type="number" name="monthly_price" step="0.01" min="0"
                                           value="{{ old('monthly_price', $package->monthly_price ?? 0) }}"
                                           class="form-control @error('monthly_price') is-invalid @enderror" required>
                                </div>
                                @error('monthly_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                <div class="form-text">{{ __('Price charged per month after trial ends.') }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('Trial Days') }}</label>
                                <div class="input-group">
                                    <input type="number" name="trial_days" min="0" max="365"
                                           value="{{ old('trial_days', $package->trial_days ?? 0) }}"
                                           class="form-control @error('trial_days') is-invalid @enderror">
                                    <span class="input-group-text">{{ __('days') }}</span>
                                </div>
                                @error('trial_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                <div class="form-text">{{ __('Set to 0 for no trial. Companies can only use trial once.') }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('Max Users') }}</label>
                                <input type="number" name="max_users" min="1"
                                       value="{{ old('max_users', $package->max_users ?? '') }}"
                                       class="form-control"
                                       placeholder="{{ __('Leave blank for unlimited') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('Sort Order') }}</label>
                                <input type="number" name="sort_order" min="0"
                                       value="{{ old('sort_order', $package->sort_order ?? 0) }}"
                                       class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" rows="3" class="form-control"
                                          placeholder="{{ __('Brief description shown to companies') }}">{{ old('description', $package->description ?? '') }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           id="is_active" value="1"
                                           {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        {{ __('Package is active (visible for new subscriptions)') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Module Selection --}}
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                    <div class="card-header bg-white border-0">
                        <strong>{{ __('Included Modules') }}</strong>
                        <div class="text-muted small mt-1">
                            {{ __('Select which modules companies on this package can access.') }}
                        </div>
                    </div>
                    <div class="card-body">
                        @forelse ($modules as $module)
                            <div class="form-check d-flex align-items-center gap-2 py-2 border-bottom">
                                <input class="form-check-input" type="checkbox"
                                       name="module_ids[]"
                                       id="module_{{ $module->id }}"
                                       value="{{ $module->id }}"
                                       {{ in_array($module->id, old('module_ids', $selectedModuleIds ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex align-items-center gap-2 w-100"
                                       for="module_{{ $module->id }}">
                                    <i class="bi {{ $module->icon ?? 'bi-puzzle' }} fs-5"
                                       style="color:#1a5276;"></i>
                                    <div>
                                        <div class="fw-semibold">{{ $module->name_en }}</div>
                                        @if ($module->description_en)
                                            <div class="text-muted small">{{ $module->description_en }}</div>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small text-center py-3">{{ __('No modules defined yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg"></i>
                {{ isset($package) ? __('Update Package') : __('Create Package') }}
            </button>
            <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary ms-2">
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection