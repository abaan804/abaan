<x-app-layout>
    @section('title', ($heading ?? 'Video Downloader') . ' — Abaan')

    @push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="{{ asset('css/videodownloader/app.css') }}">
    @endpush

    <div class="vd-shell">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
            <div>
                <div class="text-muted small">{{ __('Video Downloader') }}</div>
                <h3 class="mb-0">@yield('heading', __('Dashboard'))</h3>
            </div>
            <button class="btn btn-outline-secondary btn-sm d-lg-none"
                    data-bs-toggle="offcanvas" data-bs-target="#vdOffcanvas">
                <i class="bi bi-list"></i> {{ __('Menu') }}
            </button>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-3 d-none d-lg-block">
                @include('videodownloader::partials.subnav')
            </div>
            <div class="col-12 col-lg-9">
                @include('videodownloader::partials.toast')
                @yield('vd-content')
            </div>
        </div>
    </div>

    {{-- Mobile offcanvas sidebar --}}
    <div class="offcanvas offcanvas-start" tabindex="-1" id="vdOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">{{ __('Video Downloader') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            @include('videodownloader::partials.subnav')
        </div>
    </div>

    <div id="vd-toast-container"
         class="toast-container position-fixed bottom-0 end-0 p-3"
         style="z-index:1080;"></div>

    @push('scripts')
        <script src="{{ asset('js/videodownloader/app.js') }}"></script>
    @endpush

    @stack('scripts')
</x-app-layout>