@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Death Report') . ' — ' . $family->name)
@section('ft-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="number" id="r-year" class="form-control"
                       placeholder="{{ __('Year (e.g. 2020)') }}" min="1900" max="{{ now()->year }}">
            </div>
            <div class="col-md-2">
                <a href="{{ route('familytree.family.reports.index', $family) }}"
                   class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="ft-stat-card p-3 mb-3 text-center">
    <div class="text-muted small">{{ __('Deceased Members Found') }}</div>
    <div class="h3 mb-0" id="r-count">—</div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ft-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Date of Birth') }}</th>
                    <th>{{ __('Date of Death') }}</th>
                    <th>{{ __('Age at Death') }}</th>
                    <th>{{ __('Burial Place') }}</th>
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
    const tableUrl = '{{ route("familytree.family.reports.deaths.table", $family) }}';
    const tbody    = document.getElementById('r-table-body');
    const yearInp  = document.getElementById('r-year');

    function load() {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div></td></tr>`;
        const p = new URLSearchParams();
        if (yearInp.value) p.set('year', yearInp.value);
        fetch(`${tableUrl}?${p.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => {
                tbody.innerHTML = html;
                const d = document.getElementById('r-count-data');
                if (d) document.getElementById('r-count').textContent = d.dataset.count;
            });
    }

    yearInp.addEventListener('change', load);
    load();
})();
</script>
@endpush
@endsection