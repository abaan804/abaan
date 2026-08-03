@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Missing Information') . ' — ' . $family->name)
@section('ft-content')

<div class="alert alert-info border-0 mb-3">
    <i class="bi bi-info-circle"></i>
    {{ __('Members shown below have one or more missing fields: Date of Birth, Contact Number, CNIC, or Father link. Click a name to edit the member.') }}
</div>

<div class="ft-stat-card p-3 mb-3 text-center">
    <div class="text-muted small">{{ __('Members with Incomplete Records') }}</div>
    <div class="h3 mb-0" id="r-count">—</div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ft-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Gender') }}</th>
                    <th>{{ __('Missing Fields') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody id="r-table-body">
                <tr><td colspan="4" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl = '{{ route("familytree.family.reports.missing.table", $family) }}';
    const tbody    = document.getElementById('r-table-body');

    fetch(tableUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text()).then(html => {
            tbody.innerHTML = html;
            const d = document.getElementById('r-count-data');
            if (d) document.getElementById('r-count').textContent = d.dataset.count;
        });
})();
</script>
@endpush
@endsection