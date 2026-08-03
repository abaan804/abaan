@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Events Report') . ' — ' . $family->name)
@section('ft-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3">
                <select id="r-event-type" class="form-select">
                    <option value="">{{ __('All Event Types') }}</option>
                    @foreach (\Modules\FamilyTree\Models\FtEvent::TYPE_LABELS as $key => $label)
                        <option value="{{ $key }}">{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" id="r-date-from" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="date" id="r-date-to" class="form-control">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <a href="#" id="r-export-xlsx" class="btn btn-outline-secondary flex-grow-1">
                    <i class="bi bi-file-earmark-excel"></i> {{ __('Excel') }}
                </a>
                <a href="{{ route('familytree.family.reports.index', $family) }}"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="ft-stat-card p-3 mb-3 text-center">
    <div class="text-muted small">{{ __('Events Found') }}</div>
    <div class="h3 mb-0" id="r-count">—</div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ft-table">
            <thead>
                <tr>
                    <th>{{ __('Member') }}</th>
                    <th>{{ __('Event') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Description') }}</th>
                </tr>
            </thead>
            <tbody id="r-table-body">
                <tr><td colspan="5" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl  = '{{ route("familytree.family.reports.events.table", $family) }}';
    const xlsxUrl   = '{{ route("familytree.family.reports.events.export", [$family, "xlsx"]) }}';
    const tbody     = document.getElementById('r-table-body');
    const typeSel   = document.getElementById('r-event-type');
    const fromInp   = document.getElementById('r-date-from');
    const toInp     = document.getElementById('r-date-to');

    function buildParams() {
        const p = new URLSearchParams();
        if (typeSel.value) p.set('event_type', typeSel.value);
        if (fromInp.value) p.set('date_from', fromInp.value);
        if (toInp.value)   p.set('date_to', toInp.value);
        return p;
    }

    function load() {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div></td></tr>`;
        fetch(`${tableUrl}?${buildParams().toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => {
                tbody.innerHTML = html;
                const d = document.getElementById('r-count-data');
                if (d) document.getElementById('r-count').textContent = d.dataset.count;
            });
    }

    [typeSel, fromInp, toInp].forEach(el => el.addEventListener('change', load));

    document.getElementById('r-export-xlsx').addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `${xlsxUrl}?${buildParams().toString()}`;
    });

    load();
})();
</script>
@endpush
@endsection