@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Outstanding Report'))
@section('masjid-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-4">
                <select id="out-season" class="form-select">
                    <option value="">{{ __('All Seasons') }}</option>
                    @foreach ($seasons as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select id="out-status" class="form-select">
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="partial">{{ __('Partial') }}</option>
                    <option value="all">{{ __('All Statuses') }}</option>
                    <option value="paid">{{ __('Paid') }}</option>
                    <option value="overpaid">{{ __('Overpaid') }}</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <div class="d-flex gap-1">
                    <a href="#" id="out-download-pdf" class="btn btn-outline-danger flex-grow-1" title="{{ __('PDF') }}">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="{{ __('Export') }}">
                            <i class="bi bi-download"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" id="out-export-csv">
                                <i class="bi bi-filetype-csv"></i> {{ __('CSV') }}
                            </a></li>
                            <li><a class="dropdown-item" href="#" id="out-export-xlsx">
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
            <div class="text-muted small">{{ __('Total Outstanding') }}</div>
            <div class="h4 mb-0 text-danger" id="out-total">—</div>
        </div>
    </div>
    <div class="col-6">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Members') }}</div>
            <div class="h4 mb-0" id="out-count">—</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle mj-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Member') }}</th>
                    <th>{{ __('Season') }}</th>
                    <th>{{ __('Amount Due') }}</th>
                    <th>{{ __('Amount Paid') }}</th>
                    <th>{{ __('Balance') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody id="out-table-body">
                <tr><td colspan="6" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl = '{{ route("masjid.mosque.reports.outstanding.table", $mosque) }}';
    const tbody = document.getElementById('out-table-body');
    const seasonSel = document.getElementById('out-season');
    const statusSel = document.getElementById('out-status');

    function buildParams() {
        const p = new URLSearchParams();
        if (seasonSel.value) p.set('season_id', seasonSel.value);
        if (statusSel.value) p.set('status', statusSel.value);
        return p;
    }

    function load() {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div></td></tr>`;
        fetch(`${tableUrl}?${buildParams().toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                tbody.innerHTML = html;
                const d = document.getElementById('out-totals-data');
                if (d) {
                    document.getElementById('out-total').textContent = d.dataset.total;
                    document.getElementById('out-count').textContent = d.dataset.count;
                }
            });
    }

    [seasonSel, statusSel].forEach(el => el.addEventListener('change', load));

    document.getElementById('out-download-pdf').addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `{{ route('masjid.mosque.reports.outstanding.pdf', $mosque) }}?${buildParams().toString()}`;
    });

    load();
    document.getElementById('out-export-csv')?.addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `{{ route('masjid.mosque.reports.outstanding.export', [$mosque, 'csv']) }}?${buildParams().toString()}`;
    });
    document.getElementById('out-export-xlsx')?.addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `{{ route('masjid.mosque.reports.outstanding.export', [$mosque, 'xlsx']) }}?${buildParams().toString()}`;
    });
})();
</script>
@endpush
@endsection