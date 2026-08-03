@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Marriages') . ' — ' . $family->name)
@section('ft-content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <select id="marriage-status-filter" class="form-select" style="max-width:180px;">
        <option value="">{{ __('All Statuses') }}</option>
        <option value="active">{{ __('Active') }}</option>
        <option value="divorced">{{ __('Divorced') }}</option>
        <option value="widowed">{{ __('Widowed') }}</option>
    </select>
    @can('familytree.manage-relationships')
        <button type="button" class="btn btn-primary" id="btn-add-marriage">
            <i class="bi bi-heart"></i> {{ __('Record Marriage') }}
        </button>
    @endcan
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
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="marriages-table-body">
                <tr><td colspan="6" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Add/Edit Modal --}}
@can('familytree.manage-relationships')
<div class="modal fade" id="marriageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="marriage-form" novalidate>
                <input type="hidden" id="marriage-id">
                <div class="modal-header" style="background:var(--ft-primary);color:#fff;">
                    <h5 class="modal-title" id="marriageModalTitle">{{ __('Record Marriage') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="marriage-form-errors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Husband') }} <span class="text-danger">*</span></label>
                            <select id="m-husband" class="form-select ft-member-select"
                                    data-placeholder="{{ __('— Select Husband —') }}" required>
                                <option value=""></option>
                                @foreach ($males as $m)
                                    <option value="{{ $m->id }}">{{ $m->full_name }}-{{ $m->father?->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Wife') }} <span class="text-danger">*</span></label>
                            <select id="m-wife" class="form-select ft-member-select"
                                    data-placeholder="{{ __('— Select Wife —') }}" required>
                                <option value=""></option>
                                @foreach ($females as $f)
                                    <option value="{{ $f->id }}">{{ $f->full_name }} - {{ $m->father?->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Marriage Date') }}</label>
                            <input type="date" id="m-marriage-date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Marriage Place') }}</label>
                            <input type="text" id="m-marriage-place" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Marriage Type') }}</label>
                            <select id="m-marriage-type" class="form-select">
                                <option value="nikah">{{ __('Nikah') }}</option>
                                <option value="civil">{{ __('Civil') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select id="m-status" class="form-select">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="divorced">{{ __('Divorced') }}</option>
                                <option value="widowed">{{ __('Widowed') }}</option>
                            </select>
                        </div>
                        <div id="divorce-date-field" class="col-md-6 d-none">
                            <label class="form-label">{{ __('Divorce Date') }}</label>
                            <input type="date" id="m-divorce-date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea id="m-notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="marriage-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="marriage-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteMarriageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">{{ __('Delete Marriage Record') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete this marriage record?') }}</p>
                <p class="small text-danger">{{ __('This action cannot be undone.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-marriage-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
(function () {
    const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl    = '{{ route("familytree.family.marriages.table", $family) }}';
    const storeUrl    = '{{ route("familytree.family.marriages.store", $family) }}';
    const tbody       = document.getElementById('marriages-table-body');
    const statusFilter = document.getElementById('marriage-status-filter');

    const marriageModal = new bootstrap.Modal(document.getElementById('marriageModal'));
    FtSelect2.onModal(document.getElementById('marriageModal'));
    const deleteModal   = new bootstrap.Modal(document.getElementById('deleteMarriageModal'));
    let deleteId = null;

    function loadTable() {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div></td></tr>`;
        const params = new URLSearchParams();
        if (statusFilter.value) params.set('status', statusFilter.value);
        fetch(`${tableUrl}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => { tbody.innerHTML = html; });
    }

    statusFilter.addEventListener('change', loadTable);

    // Status toggle shows/hides divorce date
    document.getElementById('m-status')?.addEventListener('change', function () {
        document.getElementById('divorce-date-field')?.classList.toggle('d-none',
            !['divorced','widowed'].includes(this.value));
    });

    document.getElementById('btn-add-marriage')?.addEventListener('click', () => {
        document.getElementById('marriage-form').reset();
        document.getElementById('marriage-id').value = '';
        document.getElementById('marriageModalTitle').textContent = '{{ __('Record Marriage') }}';
        document.getElementById('marriage-form-errors').classList.add('d-none');
        document.getElementById('divorce-date-field').classList.add('d-none');
        marriageModal.show();
    });

    tbody.addEventListener('click', e => {
        const editBtn = e.target.closest('.btn-edit-marriage');
        if (editBtn) {
            fetch(`/app/family-tree/{{ $family->id }}/marriages/${editBtn.dataset.id}/json`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json()).then(({ data: m }) => {
                document.getElementById('marriage-form').reset();
                document.getElementById('marriage-id').value   = m.id;
                $('#m-husband').val(m.husband_id ?? '').trigger('change');
                $('#m-wife').val(m.wife_id ?? '').trigger('change');
                document.getElementById('m-marriage-date').value  = m.marriage_date ?? '';
                document.getElementById('m-marriage-place').value = m.marriage_place ?? '';
                document.getElementById('m-marriage-type').value  = m.marriage_type ?? 'nikah';
                document.getElementById('m-status').value         = m.status ?? 'active';
                document.getElementById('m-divorce-date').value   = m.divorce_date ?? '';
                document.getElementById('m-notes').value          = m.notes ?? '';
                document.getElementById('divorce-date-field').classList.toggle('d-none',
                    !['divorced','widowed'].includes(m.status));
                document.getElementById('marriageModalTitle').textContent = '{{ __('Edit Marriage') }}';
                document.getElementById('marriage-form-errors').classList.add('d-none');
                marriageModal.show();
            });
        }

        const deleteBtn = e.target.closest('.btn-delete-marriage');
        if (deleteBtn) { deleteId = deleteBtn.dataset.id; deleteModal.show(); }
    });

    document.getElementById('confirm-delete-marriage-btn')?.addEventListener('click', () => {
        if (!deleteId) return;
        fetch(`/app/family-tree/{{ $family->id }}/marriages/${deleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json().then(b => ({ ok: r.ok, b })))
        .then(({ ok, b }) => {
            deleteModal.hide();
            if (ok && b.success) { FtToast.success(b.message); loadTable(); }
            else FtToast.error(b.message ?? '{{ __('Delete failed.') }}');
        });
    });

    document.getElementById('marriage-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const id  = document.getElementById('marriage-id').value;
        const url = id ? `/app/family-tree/{{ $family->id }}/marriages/${id}` : storeUrl;

        const payload = {
            husband_id:     document.getElementById('m-husband').value,
            wife_id:        document.getElementById('m-wife').value,
            marriage_date:  document.getElementById('m-marriage-date').value || null,
            marriage_place: document.getElementById('m-marriage-place').value,
            marriage_type:  document.getElementById('m-marriage-type').value,
            status:         document.getElementById('m-status').value,
            divorce_date:   document.getElementById('m-divorce-date').value || null,
            notes:          document.getElementById('m-notes').value,
        };
        if (id) payload._method = 'PUT';

        const btn = document.getElementById('marriage-save-btn');
        const sp  = document.getElementById('marriage-spinner');
        btn.disabled = true; sp.classList.remove('d-none');

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken,
                       'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        })
        .then(r => r.json().then(b => ({ status: r.status, b })))
        .then(({ status, b }) => {
            btn.disabled = false; sp.classList.add('d-none');
            if (status === 422) {
                const eb = document.getElementById('marriage-form-errors');
                eb.textContent = Object.values(b.errors ?? {}).flat().join(' ');
                eb.classList.remove('d-none');
                return;
            }
            if (b.success) { marriageModal.hide(); FtToast.success(b.message); loadTable(); }
        })
        .catch(() => { btn.disabled = false; sp.classList.add('d-none'); });
    });

    loadTable();
})();
</script>
@endpush
@endsection