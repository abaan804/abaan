<x-admin-layout>
    @section('title', 'Notification Settings')

    <h3 class="mb-4">{{ __('Notification Settings') }}</h3>

    <form method="POST" action="{{ route('admin.settings.notifications.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-envelope"></i> {{ __('Email') }}</strong>
                        <div class="form-check form-switch m-0">
                            <input type="hidden" name="email_notifications_enabled" value="0">
                            <input type="checkbox" name="email_notifications_enabled" value="1" class="form-check-input"
                                   {{ $settings['email_notifications_enabled'] ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-0">
                            {{ __('Email uses the platform\'s existing mail configuration — no additional setup needed. Just toggle it on/off here.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-chat-dots"></i> {{ __('SMS (Twilio)') }}</strong>
                        <div class="form-check form-switch m-0">
                            <input type="hidden" name="sms_enabled" value="0">
                            <input type="checkbox" name="sms_enabled" value="1" class="form-check-input"
                                   {{ $settings['sms_enabled'] ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label small">{{ __('Account SID') }}</label>
                            <input type="text" name="sms_twilio_sid" value="{{ $settings['sms_twilio_sid'] }}" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">{{ __('Auth Token') }}</label>
                            <input type="password" name="sms_twilio_token" value="{{ $settings['sms_twilio_token'] }}" class="form-control form-control-sm">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">{{ __('From Number') }}</label>
                            <input type="text" name="sms_twilio_from" value="{{ $settings['sms_twilio_from'] }}" class="form-control form-control-sm" placeholder="+15017122661">
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-whatsapp"></i> {{ __('WhatsApp (Twilio)') }}</strong>
                        <div class="form-check form-switch m-0">
                            <input type="hidden" name="whatsapp_enabled" value="0">
                            <input type="checkbox" name="whatsapp_enabled" value="1" class="form-check-input"
                                   {{ $settings['whatsapp_enabled'] ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label small">{{ __('Account SID') }}</label>
                            <input type="text" name="whatsapp_twilio_sid" value="{{ $settings['whatsapp_twilio_sid'] }}" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">{{ __('Auth Token') }}</label>
                            <input type="password" name="whatsapp_twilio_token" value="{{ $settings['whatsapp_twilio_token'] }}" class="form-control form-control-sm">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">{{ __('From (WhatsApp-enabled number)') }}</label>
                            <input type="text" name="whatsapp_twilio_from" value="{{ $settings['whatsapp_twilio_from'] }}" class="form-control form-control-sm" placeholder="whatsapp:+14155238886">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">{{ __('Save Notification Settings') }}</button>
    </form>

    {{-- Moved OUTSIDE the settings form — this is now a fully independent form, no nesting --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white"><strong>{{ __('Send Test Notification') }}</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.notifications.test') }}" class="row g-2">
                @csrf
                <div class="col-12 col-md-4">
                    <select name="channel" class="form-select">
                        <option value="email">{{ __('Email') }}</option>
                        <option value="sms">{{ __('SMS') }}</option>
                        <option value="whatsapp">{{ __('WhatsApp') }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-5">
                    <input type="text" name="test_to" class="form-control" placeholder="{{ __('Email or phone number') }}" required>
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100">{{ __('Send Test') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>