@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Marriage Report') . ' — ' . $family->name)
@section('ft-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3">
                <select id="r-status" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="divorced">{{ __('Divorced') }}</option>
                    <option value="widowed">{{ __('Widowed') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" id="r-year" class="form-control"
                       placeholder="{{ __('Year') }}" min="1900" max="{{ now()->year }}">
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
    <div class="text-muted small">{{ __('Marriage Records') }}</div>
    <div class="h3 mb-0" id="r-count">—</div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ft-table">
            <thead>
                <tr>
                    <th>{{ __('Husband') }}</th>
                    <th>{{ __('Wife') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Children') }}</th>
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
    const tableUrl  = '{{ route("familytree.family.reports.marriages.table", $family) }}';
    const tbody     = document.getElementById('r-table-body');
    const statusSel = document.getElementById('r-status');
    const yearInp   = document.getElementById('r-year');

    function load() {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div></td></tr>`;
        const p = new URLSearchParams();
        if (statusSel.value) p.set('status', statusSel.value);
        if (yearInp.value)   p.set('year', yearInp.value);
        fetch(`${tableUrl}?${p.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => {
                tbody.innerHTML = html;
                const d = document.getElementById('r-count-data');
                if (d) document.getElementById('r-count').textContent = d.dataset.count;
            });
    }

    [statusSel, yearInp].forEach(el => el.addEventListener('change', load));
    load();
})();
</script>
@endpush
@endsection