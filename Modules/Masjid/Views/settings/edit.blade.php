@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Mosque Settings'))
@section('masjid-content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('masjid.mosque.settings.update', $mosque) }}">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
                <div class="card-header bg-white border-0"><strong><i class="bi bi-cash"></i> {{ __('Currency') }}</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">{{ __('Currency Symbol') }}</label>
                            <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $setting->currency_symbol) }}" placeholder="Rs">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Currency Code') }}</label>
                            <input type="text" name="currency_code" class="form-control" value="{{ old('currency_code', $setting->currency_code) }}" maxlength="3" placeholder="PKR">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Symbol Position') }}</label>
                            <select name="currency_position" class="form-select">
                                <option value="before" {{ $setting->currency_position === 'before' ? 'selected' : '' }}>{{ __('Before amount') }} (Rs 500)</option>
                                <option value="after" {{ $setting->currency_position === 'after' ? 'selected' : '' }}>{{ __('After amount') }} (500 Rs)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0" style="border-radius:14px;">
                <div class="card-header bg-white border-0"><strong><i class="bi bi-receipt"></i> {{ __('Receipt') }}</strong></div>
                <div class="card-body">
                    <label class="form-label">{{ __('Receipt Prefix') }}</label>
                    <input type="text" name="receipt_prefix" class="form-control" value="{{ old('receipt_prefix', $setting->receipt_prefix) }}" placeholder="MCM-">
                    <div class="form-text">{{ __('Example: MCM-2026-00001') }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
                <div class="card-header bg-white border-0"><strong><i class="bi bi-bell"></i> {{ __('Notifications') }}</strong></div>
                <div class="card-body">
                    <p class="text-muted small">{{ __('Enable notification channels for this mosque. Credentials are configured by the platform admin.') }}</p>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="notification_email" value="0">
                        <input type="checkbox" name="notification_email" value="1" class="form-check-input" {{ $setting->notification_email ? 'checked' : '' }}>
                        <label class="form-check-label"><i class="bi bi-envelope"></i> {{ __('Email Notifications') }}</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="notification_sms" value="0">
                        <input type="checkbox" name="notification_sms" value="1" class="form-check-input" {{ $setting->notification_sms ? 'checked' : '' }}>
                        <label class="form-check-label"><i class="bi bi-chat-dots"></i> {{ __('SMS Notifications') }}</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="notification_whatsapp" value="0">
                        <input type="checkbox" name="notification_whatsapp" value="1" class="form-check-input" {{ $setting->notification_whatsapp ? 'checked' : '' }}>
                        <label class="form-check-label"><i class="bi bi-whatsapp"></i> {{ __('WhatsApp Notifications') }}</label>
                    </div>

                    <hr>
                    <label class="form-label">{{ __('Default Reminder Days Before Due Date') }}</label>
                    <input type="number" name="default_reminder_days" class="form-control" min="1" max="30" value="{{ old('default_reminder_days', $setting->default_reminder_days) }}">
                </div>
            </div>

            <div class="card shadow-sm border-0" style="border-radius:14px;">
                <div class="card-header bg-white border-0"><strong><i class="bi bi-translate"></i> {{ __('Language') }}</strong></div>
                <div class="card-body">
                    <label class="form-label">{{ __('Default Language') }}</label>
                    <select name="default_language" class="form-select">
                        <option value="en" {{ $setting->default_language === 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                        <option value="ur" {{ $setting->default_language === 'ur' ? 'selected' : '' }}>{{ __('Urdu') }}</option>
                        <option value="ar" {{ $setting->default_language === 'ar' ? 'selected' : '' }}>{{ __('Arabic') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> {{ __('Save Settings') }}
        </button>
    </div>
</form>

@endsection