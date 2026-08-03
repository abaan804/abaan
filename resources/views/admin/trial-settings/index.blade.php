<x-admin-layout>
    @section('title', 'Trial Settings')

    <h3 class="mb-4">{{ __('Trial Settings') }}</h3>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><strong>{{ __('Global Default') }}</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.trial-settings.update-global') }}" class="row g-3 align-items-end">
                @csrf
                @method('PUT')
                <div class="col-12 col-md-3">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_enabled" value="0">
                        <input type="checkbox" name="is_enabled" value="1" class="form-check-input" id="global-enabled"
                               {{ $globalSetting?->is_enabled ? 'checked' : '' }}>
                        <label class="form-check-label" for="global-enabled">{{ __('Trials Enabled') }}</label>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">{{ __('Duration (days)') }}</label>
                    <input type="number" name="duration_days" min="1" max="365" class="form-control"
                           value="{{ $globalSetting?->duration_days ?? 14 }}">
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Save Global Settings') }}</button>
                </div>
            </form>
            <p class="text-muted small mt-3 mb-0">
                {{ __('This applies to every package unless a specific override is set below.') }}
            </p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>{{ __('Per-Package Overrides') }}</strong></div>
        <div class="card-body">
            @foreach ($packages as $package)
                @php $override = $package->trialSettingOverride->first(); @endphp
                <form method="POST" action="{{ route('admin.trial-settings.update-package', $package) }}" class="row g-3 align-items-end border-bottom pb-3 mb-3">
                    @csrf
                    @method('PUT')
                    <div class="col-12 col-md-3">
                        <strong>{{ $package->translated('name') }}</strong>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="form-check form-switch">
                            <input type="hidden" name="override_enabled" value="0">
                            <input type="checkbox" name="override_enabled" value="1" class="form-check-input override-toggle"
                                   {{ $override ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('Override') }}</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" value="1" class="form-check-input"
                                   {{ $override?->is_enabled ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('Enabled') }}</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <input type="number" name="duration_days" min="1" max="365" class="form-control form-control-sm"
                               placeholder="{{ __('Days') }}" value="{{ $override?->duration_days }}">
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">{{ __('Save') }}</button>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</x-admin-layout>