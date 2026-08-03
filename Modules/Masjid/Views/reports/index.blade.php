@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Reports'))
@section('masjid-content')

<div class="row g-3 mb-4">
    @php
        $cards = [
            ['icon' => 'bi-cash-stack', 'title' => __('Collection Report'), 'desc' => __('All payments with filters and PDF export'), 'route' => route('masjid.mosque.reports.collection', $mosque)],
            ['icon' => 'bi-exclamation-circle', 'title' => __('Outstanding Report'), 'desc' => __('Pending and partial members by season'), 'route' => route('masjid.mosque.reports.outstanding', $mosque)],
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="col-12 col-md-6 col-lg-4">
            <a href="{{ $card['route'].'?standalone=1' }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100" style="border-radius:14px;">
                    <div class="card-body">
                        <i class="bi {{ $card['icon'] }} fs-2" style="color:var(--mj-primary);"></i>
                        <h6 class="mt-3 fw-bold">{{ $card['title'] }}</h6>
                        <p class="text-muted small mb-0">{{ $card['desc'] }}</p>
                    </div>
                </div>
            </a>
        </div>
    @endforeach

    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-header bg-white border-0"><strong>{{ __('Season Reports') }}</strong></div>
            <div class="card-body">
                @forelse ($seasons as $season)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <span class="fw-semibold">{{ $season->name }}</span>
                            <span class="ms-2 badge bg-{{ $season->status === 'active' ? 'success' : ($season->status === 'completed' ? 'secondary' : 'warning') }}">{{ ucfirst($season->status) }}</span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('masjid.mosque.reports.season', [$mosque, $season,'standalone' => 1]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-bar-chart"></i> {{ __('View') }}
                            </a>
                            <a href="{{ route('masjid.mosque.reports.season.pdf', [$mosque, $season]) }}" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-file-earmark-pdf"></i> {{ __('PDF') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('No seasons created yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection