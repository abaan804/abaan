@extends($vdLayout ?? 'videodownloader::layouts.app')
@section('heading', __('Settings'))
@section('vd-content')

<div class="card border-0 shadow-sm" style="border-radius:14px;max-width:720px;">
    <div class="card-header bg-white border-0">
        <strong>{{ __('Video Downloader Settings') }}</strong>
    </div>
    <div class="card-body">
        <div id="settings-errors" class="alert alert-danger d-none"></div>

        <div class="mb-4">
            <h6 class="small text-muted fw-bold mb-3">{{ __('DOWNLOAD LIMITS') }}</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Max File Size (MB)') }}</label>
                    <input type="number" id="s-max-size" name="max_file_size_mb"
                           class="form-control" value="{{ $setting->max_file_size_mb }}"
                           min="1" max="10240">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Max Concurrent Downloads') }}</label>
                    <input type="number" id="s-max-concurrent" name="max_concurrent_downloads"
                           class="form-control" value="{{ $setting->max_concurrent_downloads }}"
                           min="1" max="20">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('File Retention (days)') }}</label>
                    <input type="number" id="s-retention" name="retention_days"
                           class="form-control" value="{{ $setting->retention_days }}"
                           min="1" max="365">
                    <div class="form-text">{{ __('Files older than this will be auto-deleted.') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        {{ __('Storage Limit (GB)') }}
                        <span class="text-muted small">({{ __('leave blank for unlimited') }})</span>
                    </label>
                    <input type="number" id="s-storage-limit" name="storage_limit_gb"
                           class="form-control" value="{{ $setting->storage_limit_gb ?? '' }}"
                           min="0.1" max="1000" step="0.1">
                </div>
            </div>
        </div>

        <hr>

        <div class="mb-4">
            <h6 class="small text-muted fw-bold mb-3">{{ __('PLATFORM RESTRICTIONS') }}</h6>
            <div class="form-text mb-2">
                {{ __('Check the platforms you want to allow. Leave all unchecked to allow all.') }}
            </div>
            <div class="row g-2">
                @foreach ($platforms as $platform)
                    <div class="col-6 col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="allowed_platforms[]"
                                   id="plat-{{ $platform }}"
                                   value="{{ $platform }}"
                                   {{ in_array($platform, $setting->allowed_platforms ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="plat-{{ $platform }}">
                                {{ ucfirst($platform) }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <hr>

        <div class="mb-4">
            <h6 class="small text-muted fw-bold mb-3">{{ __('FORMAT OPTIONS') }}</h6>
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" role="switch"
                       id="s-audio-only" name="allow_audio_only"
                       {{ $setting->allow_audio_only ? 'checked' : '' }}>
                <label class="form-check-label" for="s-audio-only">
                    {{ __('Allow Audio-Only Downloads (MP3, M4A)') }}
                </label>
            </div>
        </div>

        <hr>

        <div class="mb-4">
            <h6 class="small text-muted fw-bold mb-3">{{ __('NOTIFICATIONS') }}</h6>
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" role="switch"
                       id="s-notify-complete" name="notify_on_complete"
                       {{ $setting->notify_on_complete ? 'checked' : '' }}>
                <label class="form-check-label" for="s-notify-complete">
                    {{ __('Notify when download completes') }}
                </label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch"
                       id="s-notify-fail" name="notify_on_failure"
                       {{ $setting->notify_on_failure ? 'checked' : '' }}>
                <label class="form-check-label" for="s-notify-fail">
                    {{ __('Notify when download fails') }}
                </label>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" id="s-save-btn">
                <span class="spinner-border spinner-border-sm d-none" id="s-spinner"></span>
                {{ __('Save Settings') }}
            </button>
        </div>

        {{-- Storage info --}}
        <div class="alert alert-light border mt-4 small">
            <i class="bi bi-hdd"></i>
            {{ __('Current storage used:') }}
            @php
                $fmt = $storageUsed < 1048576
                    ? round($storageUsed / 1024, 1) . ' KB'
                    : ($storageUsed < 1073741824 ? round($storageUsed / 1048576, 1) . ' MB' : round($storageUsed / 1073741824, 2) . ' GB');
            @endphp
            <strong>{{ $fmt }}</strong>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const saveUrl   = '{{ route("videodownloader.settings.update") }}';

    document.getElementById('s-save-btn').addEventListener('click', function () {
        const eb  = document.getElementById('settings-errors');
        const btn = this;
        const sp  = document.getElementById('s-spinner');
        eb.classList.add('d-none');
        btn.disabled = true; sp.classList.remove('d-none');

        // Collect allowed platforms
        const platforms = Array.from(
            document.querySelectorAll('input[name="allowed_platforms[]"]:checked')
        ).map(el => el.value);

        const payload = {
            max_file_size_mb:         parseInt(document.getElementById('s-max-size').value) || 500,
            max_concurrent_downloads: parseInt(document.getElementById('s-max-concurrent').value) || 3,
            retention_days:           parseInt(document.getElementById('s-retention').value) || 30,
            storage_limit_gb:         parseFloat(document.getElementById('s-storage-limit').value) || null,
            allow_audio_only:         document.getElementById('s-audio-only').checked,
            notify_on_complete:       document.getElementById('s-notify-complete').checked,
            notify_on_failure:        document.getElementById('s-notify-fail').checked,
            allowed_platforms:        platforms.length ? platforms : [],
        };

        fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json().then(b => ({ status: r.status, b })))
        .then(({ status, b }) => {
            btn.disabled = false; sp.classList.add('d-none');
            if (status === 422) {
                eb.textContent = Object.values(b.errors ?? {}).flat().join(' ');
                eb.classList.remove('d-none');
                return;
            }
            if (b.success) VdToast.success(b.message);
            else VdToast.error(b.message ?? 'Save failed.');
        })
        .catch(() => { btn.disabled = false; sp.classList.add('d-none'); });
    });
})();
</script>
@endpush

@endsection