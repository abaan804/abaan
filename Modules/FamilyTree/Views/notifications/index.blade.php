@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Notifications') . ' — ' . $family->name)
@section('ft-content')

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong><i class="bi bi-balloon-heart"></i> {{ __('Upcoming Birthdays') }}</strong>
            </div>
            <div class="card-body">
                @if ($upcomingBirthdays->isNotEmpty())
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary btn-sm" id="btn-send-all-birthdays">
                            <i class="bi bi-send"></i> {{ __('Send All Birthday Reminders') }}
                        </button>
                    </div>
                @endif

                @forelse ($upcomingBirthdays as $member)
                    @php
                        $birthday = $member->date_of_birth->setYear(now()->year);
                        if ($birthday->isPast()) $birthday = $birthday->addYear();
                        $daysLeft = (int) now()->diffInDays($birthday, false);
                    @endphp
                    <div class="ft-birthday-card p-3 mb-2 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            @if ($member->profile_photo)
                                <img src="{{ asset('storage/' . $member->profile_photo) }}"
                                     class="ft-avatar" style="width:36px;height:36px;">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:36px;height:36px;background:rgba(212,172,13,.15);">
                                    <i class="bi bi-person" style="color:var(--ft-gold);"></i>
                                </div>
                            @endif
                            <div>
                                <div class="fw-semibold small">{{ $member->full_name }}</div>
                                <div class="text-muted small">
                                    {{ $member->date_of_birth->format('d M') }}
                                    @if ($member->age !== null)
                                        — {{ __('Turns') }} {{ $member->age + 1 }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $daysLeft === 0 ? 'bg-success' : 'bg-light text-dark border' }}">
                                {{ $daysLeft === 0 ? __('Today!') : __(':d days', ['d' => $daysLeft]) }}
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-send-birthday"
                                    data-id="{{ $member->id }}" data-name="{{ $member->full_name }}">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    @include('familytree::partials.empty-state', [
                        'icon'        => 'bi-balloon',
                        'title'       => __('No upcoming birthdays'),
                        'description' => __('No birthdays in the next 30 days.'),
                    ])
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong><i class="bi bi-info-circle"></i> {{ __('Notification Channels') }}</strong>
            </div>
            <div class="card-body">
                @php
                    $emailEnabled    = \App\Models\Setting::getValue('email_notifications_enabled', true);
                    $smsEnabled      = \App\Models\Setting::getValue('sms_enabled', false);
                    $whatsAppEnabled = \App\Models\Setting::getValue('whatsapp_enabled', false);
                @endphp
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span><i class="bi bi-envelope"></i> {{ __('Email') }}</span>
                    <span class="badge bg-{{ $emailEnabled ? 'success' : 'secondary' }}">
                        {{ $emailEnabled ? __('Enabled') : __('Disabled') }}
                    </span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span><i class="bi bi-chat-dots"></i> {{ __('SMS') }}</span>
                    <span class="badge bg-{{ $smsEnabled ? 'success' : 'secondary' }}">
                        {{ $smsEnabled ? __('Enabled') : __('Disabled') }}
                    </span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span><i class="bi bi-whatsapp"></i> {{ __('WhatsApp') }}</span>
                    <span class="badge bg-{{ $whatsAppEnabled ? 'success' : 'secondary' }}">
                        {{ $whatsAppEnabled ? __('Enabled') : __('Disabled') }}
                    </span>
                </div>
                <div class="alert alert-light border mt-3 small mb-0">
                    <i class="bi bi-info-circle"></i>
                    {{ __('Notification channels are configured by the platform administrator in Admin → Notification Settings.') }}
                </div>
                <div class="mt-3">
                    <p class="small text-muted">{{ __('Notifications are sent to members who have:') }}</p>
                    <ul class="small text-muted mb-0">
                        <li>{{ __('Email address (for Email channel)') }}</li>
                        <li>{{ __('Contact number (for SMS channel)') }}</li>
                        <li>{{ __('WhatsApp number or Contact number (for WhatsApp channel)') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';

    document.getElementById('btn-send-all-birthdays')?.addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> {{ __('Sending...') }}';

        fetch('{{ route("familytree.family.notifications.send-all-birthdays", $family) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken,
                       'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify({}),
        })
        .then(r => r.json())
        .then(b => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-send"></i> {{ __('Send All Birthday Reminders') }}';
            b.success ? FtToast.success(b.message) : FtToast.error(b.message);
        })
        .catch(() => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-send"></i> {{ __('Send All Birthday Reminders') }}';
        });
    });

    document.querySelectorAll('.btn-send-birthday').forEach(btn => {
        btn.addEventListener('click', function () {
            const memberId = this.dataset.id;
            const name     = this.dataset.name;
            this.disabled  = true;

            fetch('{{ route("familytree.family.notifications.send-birthday", $family) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken,
                           'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: JSON.stringify({ member_id: memberId }),
            })
            .then(r => r.json())
            .then(b => {
                this.disabled = false;
                b.success ? FtToast.success(b.message) : FtToast.error(b.message);
            })
            .catch(() => { this.disabled = false; });
        });
    });
})();
</script>
@endpush
@endsection