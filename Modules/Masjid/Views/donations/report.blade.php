@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Donation Report'))
@section('masjid-content')

{{-- Summary Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 text-center">
            <div class="text-muted small">{{ __('Total Donations') }}</div>
            <div class="h4 mb-0 text-success">{{ formatCurrency($totalAll) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 text-center">
            <div class="text-muted small">{{ __('Named Donors') }}</div>
            <div class="h4 mb-0" style="color:var(--mj-primary);">{{ formatCurrency($totalNamed) }}</div>
            <div class="text-muted small mt-1">{{ $countNamed }} {{ __('records') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 text-center">
            <div class="text-muted small">{{ __('Anonymous') }}</div>
            <div class="h4 mb-0 text-secondary">{{ formatCurrency($totalAnonymous) }}</div>
            <div class="text-muted small mt-1">{{ $countAnonymous }} {{ __('records') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 text-center">
            <div class="text-muted small">{{ __('Total Records') }}</div>
            <div class="h4 mb-0">{{ $countNamed + $countAnonymous }}</div>
        </div>
    </div>
</div>

{{-- Filters + Export --}}
<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-2">
                <select id="rp-type" class="form-select">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="named">{{ __('Named') }}</option>
                    <option value="anonymous">{{ __('Anonymous') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="rp-season" class="form-select">
                    <option value="">{{ __('All Seasons') }}</option>
                    @foreach ($seasons as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="text" id="rp-search" class="form-control"
                       placeholder="{{ __('Name / receipt / purpose') }}">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="rp-date-from" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="rp-date-to" class="form-control">
            </div>
            <div class="col-12 col-md-2">
                <div class="d-flex gap-1">
                    <a href="#" id="rp-pdf-btn" class="btn btn-outline-danger flex-grow-1"
                       title="{{ __('Download PDF') }}">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle"
                                data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#" id="rp-csv-btn">
                                    <i class="bi bi-filetype-csv"></i> {{ __('Export CSV') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" id="rp-xlsx-btn">
                                    <i class="bi bi-file-earmark-excel"></i> {{ __('Export Excel') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtered Total --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Filtered Total') }}</div>
            <div class="h5 mb-0 text-success" id="rp-total">—</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Records') }}</div>
            <div class="h5 mb-0" id="rp-count">—</div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle mj-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Donor') }}</th>
                    <th>{{ __('Purpose') }}</th>
                    <th>{{ __('Receipt') }}</th>
                    <th>{{ __('Season') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Slip') }}</th>
                </tr>
            </thead>
            <tbody id="rp-table-body">
                <tr><td colspan="8" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl = '{{ route("masjid.mosque.donations.report.table", $mosque) }}';
    const pdfUrl   = '{{ route("masjid.mosque.donations.report.pdf", $mosque) }}';
    const csvUrl   = '{{ route("masjid.mosque.donations.report.export", [$mosque, "csv"]) }}';
    const xlsxUrl  = '{{ route("masjid.mosque.donations.report.export", [$mosque, "xlsx"]) }}';
    const tbody    = document.getElementById('rp-table-body');
    let searchTimer = null;

    function buildParams() {
        const p = new URLSearchParams();
        if (document.getElementById('rp-type').value)      p.set('type', document.getElementById('rp-type').value);
        if (document.getElementById('rp-season').value)    p.set('season_id', document.getElementById('rp-season').value);
        if (document.getElementById('rp-search').value)    p.set('search', document.getElementById('rp-search').value);
        if (document.getElementById('rp-date-from').value) p.set('date_from', document.getElementById('rp-date-from').value);
        if (document.getElementById('rp-date-to').value)   p.set('date_to', document.getElementById('rp-date-to').value);
        return p;
    }

    function load(page = 1) {
        const p = buildParams();
        p.set('page', page);
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4">
            <div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div></td></tr>`;
        fetch(`${tableUrl}?${p.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                tbody.innerHTML = html;
                const d = document.getElementById('rp-totals-data');
                if (d) {
                    document.getElementById('rp-total').textContent = d.dataset.total;
                    document.getElementById('rp-count').textContent = d.dataset.count;
                }
            });
    }

    tbody.addEventListener('click', e => {
        const link = e.target.closest('#rp-pagination a');
        if (link) { e.preventDefault(); load(new URL(link.href).searchParams.get('page') || 1); }
    });

    document.getElementById('rp-search').addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => load(1), 400);
    });
    ['rp-type','rp-season','rp-date-from','rp-date-to'].forEach(id =>
        document.getElementById(id)?.addEventListener('change', () => load(1))
    );

    document.getElementById('rp-pdf-btn').addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `${pdfUrl}?${buildParams()}`;
    });
    document.getElementById('rp-csv-btn').addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `${csvUrl}&${buildParams()}`;
    });
    document.getElementById('rp-xlsx-btn').addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `${xlsxUrl}&${buildParams()}`;
    });

    load(1);
})();
</script>
@endpush
@endsection