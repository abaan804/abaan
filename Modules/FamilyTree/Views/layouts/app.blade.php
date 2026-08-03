<x-app-layout>
    @section('title', ($heading ?? 'Family Tree') . ' — Family Tree Manager')

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/familytree/app.css') }}">
        {{-- Select2 Bootstrap 5 theme --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    @endpush

    <div class="ft-shell">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
            <div>
                <div class="text-muted small">{{ __('Family Tree Manager') }}</div>
                <h3 class="mb-0">@yield('heading', __('Dashboard'))</h3>
            </div>
            <button class="btn btn-outline-secondary btn-sm d-lg-none"
                    data-bs-toggle="offcanvas" data-bs-target="#ftOffcanvas">
                <i class="bi bi-list"></i> {{ __('Menu') }}
            </button>
        </div>

        <div class="mb-3">
            @include('familytree::partials.global-search')
        </div>

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

    @if (isset($family))
        @can('familytree.manage-members')
            <a href="{{ route('familytree.family.members.index', $family) }}" class="ft-fab"
               title="{{ __('Add Member') }}">
                <i class="bi bi-person-plus"></i>
            </a>
        @endcan
    @endif

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
</x-app-layout>