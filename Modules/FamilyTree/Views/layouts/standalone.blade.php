<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ur','ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('heading') — {{ $family->name ?? 'Family Tree' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ in_array(app()->getLocale(), ['ur','ar']) ? '.rtl' : '' }}.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/familytree/app.css') }}">

    {{-- Select2 Bootstrap 5 theme --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-light bg-white border-bottom px-3">
        <div class="d-flex justify-content-between align-items-center w-100 gap-3">
            <span class="fw-bold text-nowrap" style="color:var(--ft-primary);">
                <i class="bi bi-diagram-3"></i> {{ $family->name ?? __('Family Tree') }}
            </span>
            <div class="d-none d-md-block flex-grow-1" style="max-width:380px;">
                @include('familytree::partials.global-search')
            </div>
            <button class="btn btn-outline-secondary btn-sm d-lg-none"
                    data-bs-toggle="offcanvas" data-bs-target="#ftOffcanvas">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </nav>

    <div class="d-md-none px-3 pt-3">@include('familytree::partials.global-search')</div>

    <div class="ft-shell p-3 p-md-4">
        <div class="row g-4">
            <div class="col-12 col-lg-3 d-none d-lg-block">
                @include('familytree::partials.subnav')
            </div>
            <div class="col-12 col-lg-9">
                @include('familytree::partials.toast')
                @yield('ft-content')
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="ftOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">{{ $family->name ?? __('Family Tree') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">@include('familytree::partials.subnav')</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/familytree/select2-init.js') }}"></script>

    {{-- Translated labels for Select2 (set before any Select2 init) --}}
    <script>
    window.FtSelect2Labels = {
        noResults : '{{ __('No results found') }}',
        searching : '{{ __('Searching...') }}',
    };
    </script>

    @stack('scripts')
</body>
</html>