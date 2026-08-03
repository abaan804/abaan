@extends($vdLayout ?? 'videodownloader::layouts.app')
@section('heading', __('Reports'))
@section('vd-content')

{{-- Usage Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="vd-stat-card text-center">
            <div class="text-muted small">{{ __('Total') }}</div>
            <div class="h3 mb-0">{{ $stats['total_downloads'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vd-stat-card text-center">
            <div class="text-muted small">{{ __('Successful') }}</div>
            <div class="h3 mb-0 text-success">{{ $stats['successful'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vd-stat-card text-center">
            <div class="text-muted small">{{ __('Failed') }}</div>
            <div class="h3 mb-0 text-danger">{{ $stats['failed'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="vd-stat-card text-center">
            <div class="text-muted small">{{ __('Audio Only') }}</div>
            <div class="h3 mb-0" style="color:#7c3aed;">{{ $stats['audio_only_count'] }}</div>
        </div>
    </div>
</div>

{{-- Filters + Export --}}
<div class="card border-0 shadow-sm mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-2">
                <select id="r-status" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach(['pending','processing','completed','failed','cancelled'] as $s)
                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="r-platform" class="form-select">
                    <option value="">{{ __('All Platforms') }}</option>
                    @foreach(array_keys(config('videodownloader.platforms', [])) as $p)
                        <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="r-date-from" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="r-date-to" class="form-control">
            </div>
            <div class="col-12 col-md-4">
                <div class="d-flex gap-2">
                    <a href="#" id="r-pdf-btn" class="btn btn-outline-danger flex-grow-1">
                        <i class="bi bi-file-earmark-pdf"></i> {{ __('PDF') }}
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle"
                                data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" id="r-csv-btn">
                                <i class="bi bi-filetype-csv"></i> {{ __('CSV') }}
                            </a></li>
                            <li><a class="dropdown-item" href="#" id="r-xlsx-btn">
                                <i class="bi bi-file-earmark-excel"></i> {{ __('Excel') }}
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle vd-table">
            <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Platform') }}</th>
                    <th>{{ __('Quality') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Size') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody id="r-table-body">
                <tr><td colspan="7" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm"
                         style="color:var(--vd-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl = '{{ route("videodownloader.reports.table") }}';
    const pdfUrl   = '{{ route("videodownloader.reports.pdf") }}';
    const csvUrl   = '{{ route("videodownloader.reports.export", "csv") }}';
    const xlsxUrl  = '{{ route("videodownloader.reports.export", "xlsx") }}';
    const tbody    = document.getElementById('r-table-body');

    function buildParams() {
        const p = new URLSearchParams();
        const s = document.getElementById('r-status').value;
        const pl = document.getElementById('r-platform').value;
        const df = document.getElementById('r-date-from').value;
        const dt = document.getElementById('r-date-to').value;
        if (s)  p.set('status', s);
        if (pl) p.set('platform', pl);
        if (df) p.set('date_from', df);
        if (dt) p.set('date_to', dt);
        return p;
    }

    function load() {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5">
            <div class="spinner-border spinner-border-sm"
                 style="color:var(--vd-primary);"></div></td></tr>`;
        fetch(`${tableUrl}?${buildParams().toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text()).then(html => { tbody.innerHTML = html; });
    }

    ['r-status','r-platform','r-date-from','r-date-to'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', load);
    });

    document.getElementById('r-pdf-btn')?.addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `${pdfUrl}?${buildParams()}`;
    });
    document.getElementById('r-csv-btn')?.addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `${csvUrl}&${buildParams()}`;
    });
    document.getElementById('r-xlsx-btn')?.addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `${xlsxUrl}&${buildParams()}`;
    });

    load();
})();
</script>
@endpush

@endsection