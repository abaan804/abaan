<x-app-layout>
    @section('title', ($heading ?? 'Masjid') . ' — ' . ($mosque->mosque_name ?? 'Masjid'))

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/masjid-theme.css') }}">
        {{-- Inside @push('styles') --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
            
    @endpush

    <div class="masjid-shell">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
            <div>
                <div class="text-muted small">{{ __('Masjid Contribution Manager') }}</div>
                <h3 class="mb-0">@yield('heading', __('Dashboard'))</h3>
            </div>
            <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#masjidOffcanvas">
                <i class="bi bi-list"></i> {{ __('Menu') }}
            </button>
        </div>

        <div class="mb-3">
            @include('masjid::partials.global-search')
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-3 d-none d-lg-block">
                @include('masjid::partials.subnav')
            </div>
            <div class="col-12 col-lg-9">
                @yield('masjid-content')
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="masjidOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">{{ $mosque->mosque_name ?? __('Masjid') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            @include('masjid::partials.subnav')
        </div>
    </div>

    @canany(['masjid.manage-payments'])
        @if (isset($mosque))
            <a href="{{ route('masjid.mosque.payments.index', [$mosque,'standalone'=>1]) }}" class="mj-fab" title="{{ __('Record Payment') }}">
                <i class="bi bi-plus-lg"></i>
            </a>
        @endif
    @endcanany

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @stack('scripts')
</x-app-layout>