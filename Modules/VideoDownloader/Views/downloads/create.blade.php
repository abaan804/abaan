@extends($vdLayout ?? 'videodownloader::layouts.app')
@section('heading', __('New Download'))
@section('vd-content')

@if ($atLimit)
    <div class="alert alert-warning border-0 mb-4">
        <i class="bi bi-exclamation-triangle"></i>
        {{ __('You have :n active download(s) — the maximum allowed is :max. Please wait for one to complete before starting another.', ['n' => $activeDownloads, 'max' => $setting->max_concurrent_downloads]) }}
    </div>
@endif

{{-- URL Input Section --}}
<div id="vd-url-section" {{ $atLimit ? 'style=opacity:.6;pointer-events:none' : '' }}>
    <div class="vd-url-box mb-4">
        <label class="form-label fw-semibold mb-3">
            <i class="bi bi-link-45deg"></i> {{ __('Enter Video URL') }}
        </label>
        <form id="vd-url-form" novalidate>
            <div class="input-group input-group-lg">
                <input type="url"
                       id="vd-url-input"
                       class="form-control vd-url-input"
                       placeholder="https://www.youtube.com/watch?v=..."
                       autocomplete="off" autocorrect="off" spellcheck="false">
                <button type="button" class="btn btn-outline-secondary" id="vd-paste-btn"
                        title="{{ __('Paste from clipboard') }}">
                    <i class="bi bi-clipboard"></i>
                </button>
                <button type="submit" class="btn btn-primary px-4" {{ $atLimit ? 'disabled' : '' }}>
                    <i class="bi bi-search"></i> {{ __('Fetch') }}
                </button>
            </div>
        </form>

        <div class="mt-3 d-flex flex-wrap gap-2">
            @foreach (array_keys($platforms) as $platform)
                <span class="badge bg-light text-dark border small">
                    <i class="bi bi-check-circle text-success"></i>
                    {{ ucfirst($platform) }}
                </span>
            @endforeach
        </div>
    </div>
</div>

{{-- Loading Section --}}
<div id="vd-loading-section" style="display:none;">
    <div class="card border-0 shadow-sm p-5 text-center" style="border-radius:14px;">
        <div class="spinner-border mx-auto mb-3" style="color:var(--vd-primary);width:3rem;height:3rem;"></div>
        <h5 class="fw-bold">{{ __('Fetching video information...') }}</h5>
        <p class="text-muted small">{{ __('This usually takes 2–10 seconds') }}</p>
    </div>
</div>

{{-- Formats Section --}}
<div id="vd-formats-section" style="display:none;">

    {{-- Metadata Card --}}
    <div id="vd-metadata-section" class="mb-4"></div>

    {{-- Hidden fields for form submission --}}
    <input type="hidden" id="vd-selected-url">
    <input type="hidden" id="vd-selected-format-id">
    <input type="hidden" id="vd-selected-quality">
    <input type="hidden" id="vd-selected-ext">
    <input type="hidden" id="vd-selected-audio-only" value="0">

    {{-- Format Selector --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <strong>{{ __('Choose Download Format') }}</strong>
            <span class="text-muted small ms-2">{{ __('Click a format to select it') }}</span>
        </div>
        <div class="card-body">
            <h6 class="small text-muted fw-bold mb-2">{{ __('VIDEO FORMATS') }}</h6>
            <div id="vd-formats-video"></div>

            <div id="vd-audio-section" style="display:none;">
                <hr>
                <h6 class="small text-muted fw-bold mb-2">{{ __('AUDIO ONLY') }}</h6>
                <div id="vd-formats-audio"></div>
            </div>
        </div>
    </div>

    {{-- Action Row --}}
    <div class="d-flex gap-3 align-items-center flex-wrap">
        <button type="button" class="btn btn-primary btn-lg px-5" id="vd-start-btn" disabled>
            <i class="bi bi-cloud-arrow-down"></i> {{ __('Start Download') }}
        </button>
        <button type="button" class="btn btn-outline-secondary"
                onclick="document.getElementById('vd-formats-section').style.display='none';
                         document.getElementById('vd-url-section').style.display='';">
            <i class="bi bi-arrow-left"></i> {{ __('Try another URL') }}
        </button>
    </div>
</div>

{{-- Info Box --}}
<div class="alert alert-light border mt-4 small">
    <i class="bi bi-info-circle text-primary"></i>
    {{ __('Downloads are processed in the background. You will be redirected to the download page where you can track progress.') }}
    <br>
    {{ __('Maximum file size: :size MB. Retention: :days days.', ['size' => $setting->max_file_size_mb, 'days' => $setting->retention_days]) }}
</div>

@push('scripts')
<script>
window.VdRoutes = {
    fetchMetadata: '{{ route("videodownloader.download.fetch-metadata") }}',
    start:         '{{ route("videodownloader.download.start") }}',
};
VdDownload.init();
</script>
@endpush

@endsection