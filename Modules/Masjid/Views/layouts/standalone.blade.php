<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ \App\Http\Middleware\SetLocale::direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('heading', 'Masjid') — {{ $mosque->mosque_name ?? 'Masjid' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ \App\Http\Middleware\SetLocale::direction() === 'rtl' ? '.rtl' : '' }}.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/masjid-theme.css') }}">
</head>
<body>
    <nav class="navbar navbar-light bg-white border-bottom px-3">
        <div class="d-flex justify-content-between align-items-center w-100 gap-3">
            <span class="fw-bold text-nowrap" style="color: var(--mj-primary);">
                <i class="bi bi-building"></i> {{ $mosque->mosque_name ?? 'Masjid' }}
            </span>
            <div class="d-none d-md-block flex-grow-1" style="max-width: 380px;">
                @include('masjid::partials.global-search')
            </div>
            <button class="btn btn-outline-secondary btn-sm d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#masjidOffcanvas">
                <i class="bi bi-list"></i>
            </button>
            @include('components.language-switcher')
        </div>
    </nav>

    <div class="d-md-none px-3 pt-3">
        @include('masjid::partials.global-search')
    </div>

    <div class="masjid-shell p-3 p-md-4">
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
            <h5 class="offcanvas-title">{{ $mosque->mosque_name ?? 'Masjid' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            
        </div>
        <div class="offcanvas-body">@include('masjid::partials.subnav')</div>
    </div>

    @canany(['masjid.manage-payments'])
        @if (isset($mosque))
            <a href="{{ route('masjid.mosque.payments.index', $mosque) }}" class="mj-fab">
                <i class="bi bi-plus-lg"></i>
            </a>
        @endif
    @endcanany

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>