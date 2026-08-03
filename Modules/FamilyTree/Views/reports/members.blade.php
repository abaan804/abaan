@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Members Report') . ' — ' . $family->name)
@section('ft-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-2">
                <select id="r-gender" class="form-select">
                    <option value="">{{ __('All Genders') }}</option>
                    <option value="male">{{ __('Male') }}</option>
                    <option value="female">{{ __('Female') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="r-life-status" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    <option value="living">{{ __('Living') }}</option>
                    <option value="deceased">{{ __('Deceased') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="r-marital" class="form-select">
                    <option value="">{{ __('All Marital') }}</option>
                    <option value="married">{{ __('Married') }}</option>
                    <option value="unmarried">{{ __('Unmarried') }}</option>
                    <option value="divorced">{{ __('Divorced') }}</option>
                    <option value="widowed">{{ __('Widowed') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="r-blood" class="form-select">
                    <option value="">{{ __('All Blood Groups') }}</option>
                    @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                        <option value="{{ $bg }}">{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <div class="d-flex gap-2">
                    <a href="#" id="r-download-pdf" class="btn btn-outline-danger flex-grow-1">
                        <i class="bi bi-file-earmark-pdf"></i> {{ __('PDF') }}
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" id="r-export-csv">
                                <i class="bi bi-filetype-csv"></i> {{ __('Export CSV') }}
                            </a></li>
                            <li><a class="dropdown-item" href="#" id="r-export-xlsx">
                                <i class="bi bi-file-earmark-excel"></i> {{ __('Export Excel') }}
                            </a></li>
                        </ul>
                    </div>
                    <a href="{{ route('familytree.family.reports.index', $family) }}"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-4">
        <div class="ft-stat-card p-3 text-center">
            <div class="text-muted small">{{ __('Total') }}</div>
            <div class="h4 mb-0" id="r-total">—</div>
        </div>
    </div>
    <div class="col-4">
        <div class="ft-stat-card p-3 text-center">
            <div class="text-muted small">{{ __('Male') }}</div>
            <div class="h4 mb-0" id="r-male" style="color:var(--ft-primary);">—</div>
        </div>
    </div>
    <div class="col-4">
        <div class="ft-stat-card p-3 text-center">
            <div class="text-muted small">{{ __('Female') }}</div>
            <div class="h4 mb-0" id="r-female" style="color:var(--ft-female);">—</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ft-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Father') }}</th>
                    <th>{{ __('Gender') }}</th>
                    <th>{{ __('DOB / Age') }}</th>
                    <th>{{ __('Life Status') }}</th>
                    <th>{{ __('Marital') }}</th>
                    <th>{{ __('Contact') }}</th>
                    <th>{{ __('Occupation') }}</th>
                </tr>
            </thead>
            <tbody id="r-table-body">
                <tr><td colspan="8" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl = '{{ route("familytree.family.reports.members.table", $family) }}';
    const pdfUrl   = '{{ route("familytree.family.reports.members.pdf", $family) }}';
    const csvUrl   = '{{ route("familytree.family.reports.members.export", [$family, "csv"]) }}';
    const xlsxUrl  = '{{ route("familytree.family.reports.members.export", [$family, "xlsx"]) }}';
    const tbody    = document.getElementById('r-table-body');

    const filters = {
        gender:        document.getElementById('r-gender'),
        life_status:   document.getElementById('r-life-status'),
        marital_status:document.getElementById('r-marital'),
        blood_group:   document.getElementById('r-blood'),
    };

    function buildParams() {
        const p = new URLSearchParams();
        Object.entries(filters).forEach(([k, el]) => { if (el.value) p.set(k, el.value); });
        return p;
    }

    function load() {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div></td></tr>`;
        fetch(`${tableUrl}?${buildParams().toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => {
                tbody.innerHTML = html;
                const d = document.getElementById('r-totals-data');
                if (d) {
                    document.getElementById('r-total').textContent  = d.dataset.total;
                    document.getElementById('r-male').textContent   = d.dataset.male;
                    document.getElementById('r-female').textContent = d.dataset.female;
                }
            });
    }

    Object.values(filters).forEach(el => el.addEventListener('change', load));

    document.getElementById('r-download-pdf').addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `${pdfUrl}?${buildParams().toString()}`;
    });
    document.getElementById('r-export-csv').addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `${csvUrl}?${buildParams().toString()}`;
    });
    document.getElementById('r-export-xlsx').addEventListener('click', e => {
        e.preventDefault();
        window.location.href = `${xlsxUrl}?${buildParams().toString()}`;
    });

    load();
})();
</script>
@endpush
@endsection