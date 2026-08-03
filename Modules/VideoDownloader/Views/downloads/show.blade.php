@extends($vdLayout ?? 'videodownloader::layouts.app')
@section('heading', $download->video_title ?? __('Download Details'))
@section('vd-content')

<div class="row g-4">
    {{-- Left: Status Card --}}
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
            <div class="card-body">
                {{-- Thumbnail + Title --}}
                <div class="d-flex gap-3 mb-4 flex-wrap">
                    @if ($download->video_thumbnail)
                        <img src="{{ $download->thumbnail_url }}"
                             alt="" class="rounded"
                             style="width:120px;height:80px;object-fit:cover;"
                             onerror="this.style.display='none'">
                    @endif
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">{{ $download->video_title ?? $download->original_url }}</h5>
                        @if ($download->uploader_name)
                            <div class="text-muted small">
                                <i class="bi bi-person"></i> {{ $download->uploader_name }}
                            </div>
                        @endif
                        <div class="text-muted small mt-1">
                            <i class="bi {{ $download->platform_icon }}"></i>
                            {{ ucfirst($download->platform ?? 'unknown') }}
                            @if ($download->video_duration)
                                &nbsp;·&nbsp; {{ $download->formatted_duration }}
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                    <span class="vd-status-pill vd-status-{{ $download->status }}" id="vd-status-pill">
                        <i class="bi {{ $download->status_icon }}" id="vd-status-icon"></i>
                        <span id="vd-status-text">{{ ucfirst($download->status) }}</span>
                    </span>

                    @if ($download->selected_quality)
                        <span class="badge bg-light text-dark border">
                            {{ $download->selected_quality }}
                            @if ($download->selected_format_ext)
                                .{{ strtoupper($download->selected_format_ext) }}
                            @endif
                        </span>
                    @endif
                    @if ($download->is_audio_only)
                        <span class="badge bg-purple text-white" style="background:#7c3aed;">
                            <i class="bi bi-music-note"></i> {{ __('Audio Only') }}
                        </span>
                    @endif
                </div>

                {{-- Progress Spinner for active states --}}
                <div id="vd-processing-indicator"
                     class="{{ in_array($download->status, ['pending','processing']) ? '' : 'd-none' }}">
                    <div class="d-flex align-items-center gap-2 text-muted small mb-3">
                        <div class="spinner-border spinner-border-sm vd-progress-ring"></div>
                        <span id="vd-processing-text">{{ __('Processing your download...') }}</span>
                    </div>
                    <div class="progress mb-3" style="height:6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                             style="width:100%;"></div>
                    </div>
                </div>

                {{-- Error Message --}}
                @if ($download->error_message)
                    <div class="alert alert-danger border-0 small" id="vd-error-box">
                        <i class="bi bi-x-circle"></i> {{ $download->error_message }}
                    </div>
                @else
                    <div class="alert alert-danger border-0 small d-none" id="vd-error-box"></div>
                @endif

                {{-- Download Button --}}
                <div id="vd-serve-section" class="{{ $download->is_servable && $fileExists ? '' : 'd-none' }}">
                    <div class="d-flex gap-3 align-items-center flex-wrap">
                        <a href="{{ route('videodownloader.download.serve', $download) }}"
                           class="btn btn-success btn-lg px-5" id="vd-serve-btn">
                            <i class="bi bi-cloud-arrow-down"></i>
                            {{ __('Download File') }}
                            @if ($download->formatted_file_size !== '—')
                                <span class="badge bg-white text-success ms-1">
                                    {{ $download->formatted_file_size }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- Actions Row --}}
                <div class="d-flex gap-2 mt-3 flex-wrap">
                    @if ($download->is_retryable)
                        <button type="button" class="btn btn-outline-warning" id="vd-retry-btn">
                            <i class="bi bi-arrow-clockwise"></i> {{ __('Retry') }}
                        </button>
                    @endif
                    @if (in_array($download->status, ['pending','processing']))
                        <button type="button" class="btn btn-outline-secondary" id="vd-cancel-btn">
                            <i class="bi bi-slash-circle"></i> {{ __('Cancel') }}
                        </button>
                    @endif
                    @if (in_array($download->status, ['completed','failed','cancelled']))
                        <button type="button" class="btn btn-outline-danger" id="vd-delete-btn">
                            <i class="bi bi-trash"></i> {{ __('Delete') }}
                        </button>
                    @endif
                    <a href="{{ route('videodownloader.download.create') }}"
                       class="btn btn-outline-primary">
                        <i class="bi bi-plus-lg"></i> {{ __('New Download') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Activity Log --}}
        @if ($download->activityLogs->isNotEmpty())
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong>{{ __('Activity Log') }}</strong>
            </div>
            <div class="card-body">
                <div class="vd-timeline">
                    @foreach ($download->activityLogs->sortByDesc('created_at') as $log)
                        <div class="vd-timeline-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="{{ $log->action_color }} fw-semibold small">
                                        <i class="bi {{ $log->action_icon }}"></i>
                                        {{ $log->action_label }}
                                    </span>
                                    @if (!empty($log->properties['error']))
                                        <div class="text-danger small mt-1">
                                            {{ $log->properties['error'] }}
                                        </div>
                                    @endif
                                </div>
                                <span class="text-muted small text-nowrap ms-2">
                                    {{ $log->created_at?->format('d M, H:i') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Right: Metadata --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong>{{ __('Download Details') }}</strong>
            </div>
            <div class="card-body">
                @php
                    $info = [
                        __('Platform')    => ucfirst($download->platform ?? '—'),
                        __('Quality')     => $download->selected_quality ?? '—',
                        __('Format')      => $download->selected_format_ext ? strtoupper($download->selected_format_ext) : '—',
                        __('Type')        => $download->is_audio_only ? __('Audio Only') : __('Video'),
                        __('File Size')   => $download->formatted_file_size,
                        __('Duration')    => $download->formatted_duration,
                        __('Submitted')   => $download->created_at->format('d M Y, H:i'),
                        __('Completed')   => $download->completed_at?->format('d M Y, H:i') ?? '—',
                        __('Attempts')    => $download->attempts,
                    ];
                @endphp
                @foreach ($info as $label => $value)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">{{ $label }}</span>
                        <span class="small fw-semibold">{{ $value }}</span>
                    </div>
                @endforeach

                @if ($download->original_url)
                    <div class="py-2">
                        <div class="text-muted small">{{ __('Original URL') }}</div>
                        <a href="{{ $download->original_url }}" target="_blank"
                           class="small text-break text-decoration-none"
                           rel="noopener noreferrer">
                            {{ \Illuminate\Support\Str::limit($download->original_url, 60) }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
    const statusUrl  = '{{ route("videodownloader.download.status", $download) }}';
    const deleteUrl  = '{{ route("videodownloader.download.destroy", $download) }}';
    const retryUrl   = '{{ route("videodownloader.download.retry", $download) }}';
    const cancelUrl  = '{{ route("videodownloader.download.cancel", $download) }}';
    const historyUrl = '{{ route("videodownloader.history.index") }}';

    // ── Status Polling ─────────────────────────────────────────────────────────
    let pollInterval = null;
    const terminalStatuses = ['completed', 'failed', 'cancelled'];
    let currentStatus = '{{ $download->status }}';

    function startPolling() {
        if (terminalStatuses.includes(currentStatus)) return;
        pollInterval = setInterval(poll, 3000);
    }

    function poll() {
        fetch(statusUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(({ data: d, ...b }) => {
            if (!b.success) return;
            const status = b.status;

            // Update status pill
            const pill = document.getElementById('vd-status-pill');
            const icon = document.getElementById('vd-status-icon');
            const text = document.getElementById('vd-status-text');
            if (pill) pill.className = `vd-status-pill vd-status-${status}`;
            if (icon) icon.className = `bi ${b.status_icon}`;
            if (text) text.textContent = status.charAt(0).toUpperCase() + status.slice(1);

            // Hide/show processing indicator
            const indicator = document.getElementById('vd-processing-indicator');
            if (indicator) {
                indicator.classList.toggle('d-none', terminalStatuses.includes(status));
            }

            // Error message
            const errBox = document.getElementById('vd-error-box');
            if (errBox) {
                if (b.error_message) {
                    errBox.textContent = b.error_message;
                    errBox.classList.remove('d-none');
                } else {
                    errBox.classList.add('d-none');
                }
            }

            // Show serve button on completion
            if (status === 'completed' && b.is_servable) {
                const serveSection = document.getElementById('vd-serve-section');
                if (serveSection) {
                    serveSection.classList.remove('d-none');
                    const btn = document.getElementById('vd-serve-btn');
                    if (btn && b.serve_url) btn.href = b.serve_url;
                }
                VdToast.success('Download complete! Your file is ready.');
            }

            currentStatus = status;
            if (terminalStatuses.includes(status)) {
                clearInterval(pollInterval);
            }
        })
        .catch(() => {}); // Silent — keep polling
    }

    startPolling();

    // ── Delete ─────────────────────────────────────────────────────────────────
    document.getElementById('vd-delete-btn')?.addEventListener('click', () => {
        if (!confirm('{{ __('Delete this download record and file?') }}')) return;
        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(b => {
            if (b.success) window.location.href = historyUrl;
            else VdToast.error(b.message);
        });
    });

    // ── Retry ──────────────────────────────────────────────────────────────────
    document.getElementById('vd-retry-btn')?.addEventListener('click', function () {
        this.disabled = true;
        fetch(retryUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(b => {
            if (b.success) { VdToast.success(b.message); startPolling(); }
            else { VdToast.error(b.message); this.disabled = false; }
        });
    });

    // ── Cancel ─────────────────────────────────────────────────────────────────
    document.getElementById('vd-cancel-btn')?.addEventListener('click', function () {
        if (!confirm('{{ __('Cancel this download?') }}')) return;
        this.disabled = true;
        fetch(cancelUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(b => {
            if (b.success) { VdToast.success(b.message); location.reload(); }
            else { VdToast.error(b.message); this.disabled = false; }
        });
    });
})();
</script>
@endpush

@endsection