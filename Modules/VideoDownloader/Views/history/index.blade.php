@extends($vdLayout ?? 'videodownloader::layouts.app')
@section('heading', __('Download History'))
@section('vd-content')

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12 col-md-3">
                <input type="text" id="h-search" class="form-control"
                       placeholder="{{ __('Search title, URL, quality...') }}">
            </div>
            <div class="col-6 col-md-2">
                <select id="h-status" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach(['pending','processing','completed','failed','cancelled'] as $s)
                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="h-platform" class="form-select">
                    <option value="">{{ __('All Platforms') }}</option>
                    @foreach(array_keys(config('videodownloader.platforms', [])) as $p)
                        <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="h-date-from" class="form-control"
                       placeholder="{{ __('From') }}">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="h-date-to" class="form-control"
                       placeholder="{{ __('To') }}">
            </div>
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="button" id="h-reset-btn"
                        class="btn btn-outline-secondary w-100" title="{{ __('Reset') }}">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle vd-table">
            <thead>
                <tr>
                    <th>{{ __('Video') }}</th>
                    <th>{{ __('Format') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Size') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="h-table-body">
                <tr><td colspan="6" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm" style="color:var(--vd-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl = '{{ route("videodownloader.history.table") }}';
    const tbody    = document.getElementById('h-table-body');
    const search   = document.getElementById('h-search');
    const status   = document.getElementById('h-status');
    const platform = document.getElementById('h-platform');
    const dateFrom = document.getElementById('h-date-from');
    const dateTo   = document.getElementById('h-date-to');

    let searchTimer = null;
    let currentPage = 1;

    function buildParams(page = 1) {
        const p = new URLSearchParams();
        if (search.value)   p.set('search', search.value);
        if (status.value)   p.set('status', status.value);
        if (platform.value) p.set('platform', platform.value);
        if (dateFrom.value) p.set('date_from', dateFrom.value);
        if (dateTo.value)   p.set('date_to', dateTo.value);
        p.set('page', page);
        return p;
    }

    function load(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--vd-primary);"></div>
        </td></tr>`;
        fetch(`${tableUrl}?${buildParams(page).toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text()).then(html => { tbody.innerHTML = html; });
    }

    search.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => load(1), 400);
    });

    [status, platform, dateFrom, dateTo].forEach(el =>
        el.addEventListener('change', () => load(1))
    );

    document.getElementById('h-reset-btn').addEventListener('click', () => {
        [search, status, platform, dateFrom, dateTo].forEach(el => el.value = '');
        load(1);
    });

    tbody.addEventListener('click', e => {
        const link = e.target.closest('#h-pagination a');
        if (link) {
            e.preventDefault();
            load(new URL(link.href).searchParams.get('page') || 1);
        }
    });

    load(1);
})();
</script>
@endpush

@endsection