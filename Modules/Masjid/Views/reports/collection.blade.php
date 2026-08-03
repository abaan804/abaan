@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Collection Report'))
@section('masjid-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-3">
                <input type="date" id="col-date-from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
            </div>
            <div class="col-6 col-md-3">
                <input type="date" id="col-date-to" class="form-control" value="{{ now()->toDateString() }}">
            </div>
            <div class="col-6 col-md-2">
                <select id="col-season" class="form-select">
                    <option value="">{{ __('All Seasons') }}</option>
                    @foreach ($seasons as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="col-method" class="form-select">
                    <option value="">{{ __('All Methods') }}</option>
                    <option value="cash">{{ __('Cash') }}</option>
                    <option value="bank">{{ __('Bank') }}</option>
                    <option value="online">{{ __('Online') }}</option>
                    <option value="cheque">{{ __('Cheque') }}</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <div class="d-flex gap-1">
                    <a href="#" id="col-download-pdf" class="btn btn-outline-danger flex-grow-1" title="{{ __('PDF') }}">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="{{ __('Export') }}">
                            <i class="bi bi-download"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" id="col-export-csv">
                                <i class="bi bi-filetype-csv"></i> {{ __('CSV') }}
                            </a></li>
                            <li><a class="dropdown-item" href="#" id="col-export-xlsx">
                                <i class="bi bi-file-earmark-excel"></i> {{ __('Excel') }}
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Total Collected') }}</div>
            <div class="h4 mb-0 text-success" id="col-total">—</div>
        </div>
    </div>
    <div class="col-6">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Records') }}</div>
            <div class="h4 mb-0" id="col-count">—</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle mj-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Receipt') }}</th>
                    <th>{{ __('Member') }}</th>
                    <th>{{ __('Season') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Method') }}</th>
                    <th>{{ __('Received By') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody id="col-table-body">
                <tr><td colspan="7" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl = '{{ route("masjid.mosque.reports.collection.table", $mosque) }}';
    const tbody = document.getElementById('col-table-body');
    const dateFrom = document.getElementById('col-date-from');
    const dateTo = document.getElementById('col-date-to');
    const seasonSel = document.getElementById('col-season');
    const methodSel = document.getElementById('col-method');

    function buildParams() {
        const p = new URLSearchParams();
        if (dateFrom.value) p.set('date_from', dateFrom.value);
        if (dateTo.value) p.set('date_to', dateTo.value);
        if (seasonSel.value) p.set('season_id', seasonSel.value);
        if (methodSel.value) p.set('payment_method', methodSel.value);
        return p;
    }

    function load() {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div></td></tr>`;
        fetch(`${tableUrl}?${buildParams().toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                tbody.innerHTML = html;
                const d = document.getElementById('col-totals-data');
                if (d) {
                    document.getElementById('col-total').textContent = d.dataset.total;
                    document.getElementById('col-count').textContent = d.dataset.count;
                }
            });
    }

    [dateFrom, dateTo, seasonSel, methodSel].forEach(el => el.addEventListener('change', load));

    document.getElementById('col-download-pdf').addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `{{ route('masjid.mosque.reports.collection.pdf', $mosque) }}?${buildParams().toString()}`;
    });

    load();

    document.getElementById('col-export-csv')?.addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `{{ route('masjid.mosque.reports.collection.export', [$mosque, 'csv']) }}?${buildParams().toString()}`;
    });
    document.getElementById('col-export-xlsx')?.addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `{{ route('masjid.mosque.reports.collection.export', [$mosque, 'xlsx']) }}?${buildParams().toString()}`;
    });
    
})();
</script>
@endpush
@endsection