@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Birth Report') . ' — ' . $family->name)
@section('ft-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-3">
                <input type="number" id="r-year" class="form-control" placeholder="{{ __('Year (e.g. 1990)') }}"
                       min="1900" max="{{ now()->year }}">
            </div>
            <div class="col-6 col-md-3">
                <input type="date" id="r-date-from" class="form-control" placeholder="{{ __('From') }}">
            </div>
            <div class="col-6 col-md-3">
                <input type="date" id="r-date-to" class="form-control" placeholder="{{ __('To') }}">
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <a href="{{ route('familytree.family.reports.index', $family) }}"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="ft-stat-card p-3 mb-3 text-center">
    <div class="text-muted small">{{ __('Births Found') }}</div>
    <div class="h3 mb-0" id="r-count">—</div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ft-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Date of Birth') }}</th>
                    <th>{{ __('Place of Birth') }}</th>
                    <th>{{ __('Age') }}</th>
                    <th>{{ __('Gender') }}</th>
                    <th>{{ __('Father') }}</th>
                </tr>
            </thead>
            <tbody id="r-table-body">
                <tr><td colspan="6" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl = '{{ route("familytree.family.reports.births.table", $family) }}';
    const tbody    = document.getElementById('r-table-body');
    const yearInp  = document.getElementById('r-year');
    const fromInp  = document.getElementById('r-date-from');
    const toInp    = document.getElementById('r-date-to');

    function buildParams() {
        const p = new URLSearchParams();
        if (yearInp.value) p.set('year', yearInp.value);
        if (fromInp.value) p.set('date_from', fromInp.value);
        if (toInp.value)   p.set('date_to', toInp.value);
        return p;
    }

    function load() {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div></td></tr>`;
        fetch(`${tableUrl}?${buildParams().toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => {
                tbody.innerHTML = html;
                const d = document.getElementById('r-count-data');
                if (d) document.getElementById('r-count').textContent = d.dataset.count;
            });
    }

    [yearInp, fromInp, toInp].forEach(el => el.addEventListener('change', load));
    load();
})();
</script>
@endpush
@endsection