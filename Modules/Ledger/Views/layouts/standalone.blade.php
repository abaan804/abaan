<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ \App\Http\Middleware\SetLocale::direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'EasyKhata')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ \App\Http\Middleware\SetLocale::direction() === 'rtl' ? '.rtl' : '' }}.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ledger-theme.css') }}">
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-light bg-white border-bottom px-3">
        <div class="d-flex justify-content-between align-items-center w-100 gap-3">
            <span class="fw-bold text-nowrap"><i class="bi bi-journal-bookmark"></i> {{ __('EasyKhata') }}</span>
            <div class="d-none d-md-block flex-grow-1" style="max-width: 400px;">
                @include('ledger::partials.global-search')
            </div>
            <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#ledgerOffcanvas">
                <i class="bi bi-list"></i>
            </button>
            @include('components.language-switcher')
        </div>
    </nav>
    <div class="d-md-none px-3 pt-3">
        @include('ledger::partials.global-search')
    </div>    
    <div class="ledger-shell p-3 p-md-4">
        <div class="row g-4">
            <div class="col-12 col-lg-3 d-none d-lg-block">
                @include('ledger::partials.subnav')
            </div>
            <div class="col-12 col-lg-9">
                @yield('ledger-content')
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="ledgerOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">{{ __('EasyKhata') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            @include('ledger::partials.subnav')
        </div>
    </div>

    @canany(['easykhata.manage-transactions'])
        <!-- <a href="{{ route('ledger.transactions.index',['standalone' => 1]) }}" class="ledger-fab"><i class="bi bi-plus-lg"></i></a> -->
    @endcanany

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>