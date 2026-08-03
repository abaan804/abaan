@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Notifications'))
@section('masjid-content')

<div id="notif-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080;"></div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-header bg-white border-0"><strong>{{ __('Send Reminders') }}</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Select Season') }} <span class="text-danger">*</span></label>
                    <select id="notif-season-select" class="form-select">
                        <option value="">{{ __('— Choose Season —') }}</option>
                        @foreach ($seasons as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-grid">
                    <button type="button" class="btn btn-primary" id="btn-send-all-reminders">
                        <i class="bi bi-send"></i> {{ __('Send Reminders to All Pending Members') }}
                    </button>
                </div>
                <div class="form-text mt-2">{{ __('Sends balance reminder to all pending/partial members in the selected season via enabled channels (Email/SMS/WhatsApp).') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <strong>{{ __('Notification Log') }}</strong>
                <span class="badge bg-light text-dark border">{{ $logs->count() }} {{ __('recent') }}</span>
            </div>
            <div class="table-responsive" style="max-height:350px;overflow-y:auto;">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Member') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Channel') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('When') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->member?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border small">
                                        {{ ucfirst(str_replace('_', ' ', $log->type)) }}
                                    </span>
                                </td>
                                <td>
                                    <i class="bi {{ $log->channel === 'email' ? 'bi-envelope' : ($log->channel === 'sms' ? 'bi-chat-dots' : 'bi-whatsapp') }}"></i>
                                    {{ ucfirst($log->channel) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $log->created_at?->diffForHumans() ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3 small">{{ __('No notifications sent yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';

    function toast(msg, type = 'success') {
        const container = document.getElementById('notif-toast-container');
        const el = document.createElement('div');
        el.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        container.appendChild(el);
        new bootstrap.Toast(el, { delay: 5000 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    document.getElementById('btn-send-all-reminders').addEventListener('click', function () {
        const seasonId = document.getElementById('notif-season-select').value;
        if (!seasonId) { toast('{{ __('Please select a season first.') }}', 'error'); return; }

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> {{ __('Sending...') }}';

        fetch('{{ route("masjid.mosque.notifications.send-all-reminders", $mosque) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ season_id: seasonId }),
        })
            .then(r => r.json())
            .then(b => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-send"></i> {{ __('Send Reminders to All Pending Members') }}';
                toast(b.message, b.success ? 'success' : 'error');
                if (b.success) setTimeout(() => window.location.reload(), 2000);
            })
            .catch(() => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-send"></i> {{ __('Send Reminders to All Pending Members') }}';
                toast('{{ __('Request failed.') }}', 'error');
            });
    });
})();
</script>
@endpush
@endsection