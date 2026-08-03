<x-admin-layout>
    @section('title', 'System Settings')

    <h3 class="mb-4">{{ __('System Settings') }}</h3>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><strong>{{ __('General') }}</strong></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Site Name') }}</label>
                            <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name']) }}"
                                   class="form-control @error('site_name') is-invalid @enderror" required>
                            @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">{{ __('Default Locale') }}</label>
                            <select name="default_locale" class="form-select">
                                @foreach (config('abaan.supported_locales') as $code => $info)
                                    <option value="{{ $code }}" {{ old('default_locale', $settings['default_locale']) === $code ? 'selected' : '' }}>
                                        {{ $info['flag'] }} {{ $info['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Used for guests who have not selected a language.') }}</small>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>{{ __('Security') }}</strong></div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input type="hidden" name="email_verification_enabled" value="0">
                            <input type="checkbox" name="email_verification_enabled" value="1" class="form-check-input" id="email-verify-toggle"
                                   {{ old('email_verification_enabled', $settings['email_verification_enabled']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="email-verify-toggle">{{ __('Require Email Verification') }}</label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            {{ __('When enabled, new users must verify their email before accessing the dashboard.') }}
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><strong>{{ __('Currency Format') }}</strong></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">{{ __('Symbol') }}</label>
                                <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol']) }}"
                                       class="form-control @error('currency_symbol') is-invalid @enderror" placeholder="$, Rs, PKR, ₨">
                                @error('currency_symbol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Currency Code') }}</label>
                                <input type="text" name="currency_code" value="{{ old('currency_code', $settings['currency_code']) }}"
                                       class="form-control @error('currency_code') is-invalid @enderror" placeholder="USD, PKR, SAR" maxlength="3">
                                @error('currency_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('Symbol Position') }}</label>
                                <select name="currency_position" class="form-select">
                                    <option value="before" {{ old('currency_position', $settings['currency_position']) === 'before' ? 'selected' : '' }}>
                                        {{ __('Before amount') }} ({{ $settings['currency_symbol'] }}100.00)
                                    </option>
                                    <option value="after" {{ old('currency_position', $settings['currency_position']) === 'after' ? 'selected' : '' }}>
                                        {{ __('After amount') }} (100.00 {{ $settings['currency_symbol'] }})
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>{{ __('Date Format') }}</strong></div>
                    <div class="card-body">
                        <select name="date_format" class="form-select">
                            @foreach ($dateFormatOptions as $format => $preview)
                                <option value="{{ $format }}" {{ old('date_format', $settings['date_format']) === $format ? 'selected' : '' }}>
                                    {{ $format }} — {{ $preview }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('Applies to all dates shown across the platform.') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">{{ __('Save Settings') }}</button>
    </form>
</x-admin-layout>