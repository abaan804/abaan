@extends($vdLayout ?? 'videodownloader::layouts.app')
@section('heading', __('Dashboard'))
@section('vd-content')

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="vd-stat-card">
            <div class="icon-wrap mb-2" style="background:rgba(26,58,92,.1);">
                <i class="bi bi-cloud-arrow-down" style="color:var(--vd-primary);"></i>
            </div>
            <div class="text-muted small">{{ __('Total Downloads') }}</div>
            <div class="h3 mb-0">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vd-stat-card">
            <div class="icon-wrap mb-2" style="background:rgba(45,106,79,.1);">
                <i class="bi bi-check-circle" style="color:var(--vd-success);"></i>
            </div>
            <div class="text-muted small">{{ __('Completed') }}</div>
            <div class="h3 mb-0 text-success">{{ $stats['completed'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vd-stat-card">
            <div class="icon-wrap mb-2" style="background:rgba(230,57,70,.1);">
                <i class="bi bi-x-circle" style="color:var(--vd-accent);"></i>
            </div>
            <div class="text-muted small">{{ __('Failed') }}</div>
            <div class="h3 mb-0 text-danger">{{ $stats['failed'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vd-stat-card">
            <div class="icon-wrap mb-2" style="background:rgba(244,162,97,.15);">
                <i class="bi bi-arrow-repeat" style="color:var(--vd-warning);"></i>
            </div>
            <div class="text-muted small">{{ __('Processing') }}</div>
            <div class="h3 mb-0" style="color:var(--vd-warning);">
                {{ $stats['pending'] + $stats['processing'] }}
            </div>
        </div>
    </div>
</div>

{{-- Storage + Quick Actions --}}
<div class="row g-4 mb-4">
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
            <div class="card-body">
                <h6 class="fw-bold mb-3">{{ __('Storage Used') }}</h6>
                @php
                    $usedFmt = $storageUsed < 1048576
                        ? round($storageUsed / 1024, 1) . ' KB'
                        : ($storageUsed < 1073741824 ? round($storageUsed / 1048576, 1) . ' MB' : round($storageUsed / 1073741824, 2) . ' GB');
                @endphp
                <div class="d-flex justify-content-between mb-1">
                    <span class="small">{{ $usedFmt }}</span>
                    @if ($storageLimitBytes)
                        <span class="small text-muted">{{ $setting->storage_limit_gb }} GB limit</span>
                    @else
                        <span class="small text-muted">{{ __('No limit set') }}</span>
                    @endif
                </div>
                @if ($storagePercent !== null)
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar {{ $storagePercent > 90 ? 'bg-danger' : ($storagePercent > 70 ? 'bg-warning' : 'bg-success') }}"
                             style="width:{{ $storagePercent }}%;"></div>
                    </div>
                    <div class="small text-muted mt-1">{{ $storagePercent }}% used</div>
                @else
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-primary" style="width:5%;"></div>
                    </div>
                @endif

                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <span class="badge bg-light text-dark border">
                        {{ __('Today: :n', ['n' => $stats['today']]) }}
                    </span>
                    <span class="badge bg-light text-dark border">
                        {{ __('This week: :n', ['n' => $stats['this_week']]) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center gap-3">
                <i class="bi bi-cloud-arrow-down" style="font-size:2.5rem;color:var(--vd-primary);"></i>
                <div>
                    <h6 class="fw-bold mb-1">{{ __('Start a New Download') }}</h6>
                    <p class="text-muted small mb-0">
                        {{ __('Paste a YouTube, TikTok, Instagram or other video URL') }}
                    </p>
                </div>
                @can('videodownloader.create-download')
                    <a href="{{ route('videodownloader.download.create') }}"
                       class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> {{ __('New Download') }}
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

{{-- Platform Breakdown + Format Breakdown --}}
@if ($platforms->isNotEmpty() || $formats->isNotEmpty())
<div class="row g-4 mb-4">
    @if ($platforms->isNotEmpty())
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong>{{ __('Downloads by Platform') }}</strong>
            </div>
            <div class="card-body">
                @foreach ($platforms as $p)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="small fw-semibold">
                            <i class="bi bi-{{ $p->platform === 'youtube' ? 'youtube' : ($p->platform === 'twitter' ? 'twitter-x' : 'globe') }} vd-platform-{{ $p->platform }}"></i>
                            {{ ucfirst($p->platform) }}
                        </span>
                        <span class="badge bg-primary rounded-pill">{{ $p->count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if ($formats->isNotEmpty())
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong>{{ __('Downloads by Format') }}</strong>
            </div>
            <div class="card-body">
                @foreach ($formats as $f)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="small fw-semibold">
                            .{{ strtoupper($f->selected_format_ext ?? '?') }}
                        </span>
                        <span class="badge bg-secondary rounded-pill">{{ $f->count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- Recent Downloads --}}
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <strong>{{ __('Recent Downloads') }}</strong>
        <a href="{{ route('videodownloader.history.index') }}" class="btn btn-sm btn-outline-primary">
            {{ __('View All') }}
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle vd-table">
            <thead>
                <tr>
                    <th>{{ __('Video') }}</th>
                    <th>{{ __('Format') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recent as $dl)
                    <tr>
                        <td data-label="{{ __('Video') }}" class="vd-cell-title">
                            <div class="d-flex align-items-center gap-2">
                                @if ($dl->video_thumbnail)
                                    <img src="{{ $dl->thumbnail_url }}"
                                         class="vd-thumb-sm" alt=""
                                         onerror="this.style.display='none'">
                                @endif
                                <div>
                                    <div class="small fw-semibold" style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        {{ $dl->video_title ?? $dl->original_url }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-globe"></i> {{ ucfirst($dl->platform ?? 'unknown') }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td data-label="{{ __('Format') }}">
                            <span class="badge bg-light text-dark border">
                                {{ $dl->selected_quality ?? '—' }}
                                @if ($dl->selected_format_ext)
                                    .{{ strtoupper($dl->selected_format_ext) }}
                                @endif
                            </span>
                        </td>
                        <td data-label="{{ __('Status') }}">
                            <span class="vd-status-pill vd-status-{{ $dl->status }}">
                                <i class="bi {{ $dl->status_icon }}"></i>
                                {{ ucfirst($dl->status) }}
                            </span>
                        </td>
                        <td data-label="{{ __('Date') }}" class="small text-muted">
                            {{ $dl->created_at->format('d M Y') }}
                        </td>
                        <td>
                            <a href="{{ route('videodownloader.download.show', $dl) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr class="vd-row-empty">
                        <td colspan="5">
                            <div class="vd-empty">
                                <i class="bi bi-cloud-arrow-down"></i>
                                <p>{{ __('No downloads yet') }}</p>
                                <a href="{{ route('videodownloader.download.create') }}"
                                   class="btn btn-primary btn-sm">
                                    {{ __('Start your first download') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection