<x-app-layout>
    @section('title', $title ?? 'EasyKhata')

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/ledger-theme.css') }}">
    @endpush

    <div class="ledger-shell">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <div class="text-muted small">{{ __('EasyKhata') }}</div>
                <h3 class="mb-0">@yield('heading', __('Dashboard'))</h3>
            </div>
            <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#ledgerOffcanvas">
                <i class="bi bi-list"></i> {{ __('Menu') }}
            </button>
        </div>

        <div class="mb-4">
            @include('ledger::partials.global-search')
        </div>

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
        <a href="{{ route('ledger.transactions.index',['standalone' => 1]) }}" class="ledger-fab">
            <i class="bi bi-plus-lg"></i>
        </a>
    @endcanany
</x-app-layout>