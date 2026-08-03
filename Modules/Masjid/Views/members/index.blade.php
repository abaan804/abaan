@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Members'))
@section('masjid-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12 col-md-5">
                <input type="text" id="member-search" class="form-control" placeholder="{{ __('Search name, mobile, CNIC') }}">
            </div>
            <div class="col-6 col-md-2">
                <select id="member-status-filter" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="member-sort" class="form-select">
                    <option value="name">{{ __('Name A-Z') }}</option>
                    <option value="joining_date">{{ __('Joining Date') }}</option>
                    <option value="created_at">{{ __('Newest') }}</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <div class="d-flex gap-2">
                    @can('masjid.manage-members')
                        <button type="button" class="btn btn-primary flex-grow-1" id="btn-add-member">
                            <i class="bi bi-person-plus"></i> {{ __('Add') }}
                        </button>
                    @endcan
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="{{ __('Export') }}">
                            <i class="bi bi-download"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" id="export-members-csv">
                                <i class="bi bi-filetype-csv"></i> {{ __('Export CSV') }}
                            </a></li>
                            <li><a class="dropdown-item" href="#" id="export-members-xlsx">
                                <i class="bi bi-file-earmark-excel"></i> {{ __('Export Excel') }}
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle mj-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Mobile') }}</th>
                    <th>{{ __('Joining') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="members-table-body">
                <tr><td colspan="5" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="member-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" id="member-id">
                <input type="hidden" id="member-method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="memberModalTitle">{{ __('Add Member') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="member-form-errors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="member-name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Father Name') }}</label>
                            <input type="text" name="father_name" id="member-father-name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Mobile') }} <span class="text-danger">*</span></label>
                            <input type="text" name="mobile" id="member-mobile" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('WhatsApp') }}</label>
                            <input type="text" name="whatsapp" id="member-whatsapp" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" id="member-email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('CNIC') }}</label>
                            <input type="text" name="cnic" id="member-cnic" class="form-control" placeholder="00000-0000000-0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Occupation') }}</label>
                            <input type="text" name="occupation" id="member-occupation" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Joining Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="joining_date" id="member-joining-date" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Address') }}</label>
                            <textarea name="address" id="member-address" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="member-status" class="form-select">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Photo') }}</label>
                            <input type="file" name="photo" id="member-photo" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" id="member-notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="member-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="member-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete modal --}}
<div class="modal fade" id="deleteMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Member') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete') }} <strong id="delete-member-name"></strong>?</p>
                <p class="text-danger small">{{ __('This cannot be undone. Members with payment history cannot be deleted — set to Inactive instead.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-member-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl = '{{ route("masjid.mosque.members.table", $mosque) }}';
    const tbody = document.getElementById('members-table-body');
    const searchInput = document.getElementById('member-search');
    const statusFilter = document.getElementById('member-status-filter');
    const sortSelect = document.getElementById('member-sort');

    const memberModal = new bootstrap.Modal(document.getElementById('memberModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteMemberModal'));
    let deleteId = null;
    let searchDebounce = null;
    let currentPage = 1;

    function buildUrl(page = 1) {
        const p = new URLSearchParams();
        if (searchInput.value) p.set('search', searchInput.value);
        if (statusFilter.value) p.set('status', statusFilter.value);
        if (sortSelect.value) p.set('sort', sortSelect.value);
        p.set('page', page);
        return `${tableUrl}?${p.toString()}`;
    }

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div></td></tr>`;
        fetch(buildUrl(page), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => { tbody.innerHTML = html; })
            .catch(() => console.error('load failed'));
    }

    tbody.addEventListener('click', e => {
        const link = e.target.closest('#members-pagination a');
        if (link) { e.preventDefault(); loadTable(new URL(link.href).searchParams.get('page') || 1); }
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => loadTable(1), 400);
    });
    [statusFilter, sortSelect].forEach(el => el.addEventListener('change', () => loadTable(1)));

    function clearErrors() {
        document.querySelectorAll('#member-form .is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('#member-form .invalid-feedback').forEach(el => el.remove());
    }

    function showErrors(errors) {
        clearErrors();
        Object.entries(errors).forEach(([field, msgs]) => {
            const key = field.replace(/_/g, '-');
            const input = document.getElementById(`member-${key}`);
            if (input) {
                input.classList.add('is-invalid');
                const fb = document.createElement('div');
                fb.className = 'invalid-feedback';
                fb.textContent = msgs[0];
                input.insertAdjacentElement('afterend', fb);
            }
        });
    }

    document.getElementById('btn-add-member')?.addEventListener('click', () => {
        document.getElementById('member-form').reset();
        document.getElementById('member-id').value = '';
        document.getElementById('member-method').value = 'POST';
        document.getElementById('memberModalTitle').textContent = '{{ __('Add Member') }}';
        document.getElementById('member-joining-date').value = new Date().toISOString().slice(0, 10);
        document.getElementById('member-form-errors').classList.add('d-none');
        clearErrors();
        memberModal.show();
    });

    tbody.addEventListener('click', e => {
        const btn = e.target.closest('.btn-edit-member');
        if (!btn) return;
        fetch(`/app/masjid/{{ $mosque->id }}/members/${btn.dataset.id}/json`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(({ data: m }) => {
                document.getElementById('member-form').reset();
                document.getElementById('member-id').value = m.id;
                document.getElementById('member-method').value = 'PUT';
                document.getElementById('member-name').value = m.name ?? '';
                document.getElementById('member-father-name').value = m.father_name ?? '';
                document.getElementById('member-mobile').value = m.mobile ?? '';
                document.getElementById('member-whatsapp').value = m.whatsapp ?? '';
                document.getElementById('member-email').value = m.email ?? '';
                document.getElementById('member-cnic').value = m.cnic ?? '';
                document.getElementById('member-occupation').value = m.occupation ?? '';
                document.getElementById('member-joining-date').value = m.joining_date ?? '';
                document.getElementById('member-address').value = m.address ?? '';
                document.getElementById('member-status').value = m.status ?? 'active';
                document.getElementById('member-notes').value = m.notes ?? '';
                document.getElementById('memberModalTitle').textContent = '{{ __('Edit Member') }}';
                document.getElementById('member-form-errors').classList.add('d-none');
                clearErrors();
                memberModal.show();
            });
    });

    tbody.addEventListener('click', e => {
        const btn = e.target.closest('.btn-delete-member');
        if (!btn) return;
        deleteId = btn.dataset.id;
        document.getElementById('delete-member-name').textContent = btn.dataset.name;
        deleteModal.show();
    });

    document.getElementById('confirm-delete-member-btn').addEventListener('click', () => {
        if (!deleteId) return;
        fetch(`/app/masjid/{{ $mosque->id }}/members/${deleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json().then(b => ({ ok: r.ok, b })))
            .then(({ ok, b }) => {
                deleteModal.hide();
                if (ok && b.success) { loadTable(currentPage); }
                else { alert(b.message ?? '{{ __('Delete failed.') }}'); }
            });
    });

    document.getElementById('member-form').addEventListener('submit', e => {
        e.preventDefault();
        const id = document.getElementById('member-id').value;
        const method = document.getElementById('member-method').value;
        const url = id ? `/app/masjid/{{ $mosque->id }}/members/${id}` : `{{ route('masjid.mosque.members.store', $mosque) }}`;
        const formData = new FormData(document.getElementById('member-form'));
        if (method === 'PUT') formData.append('_method', 'PUT');

        const saveBtn = document.getElementById('member-save-btn');
        const spinner = document.getElementById('member-spinner');
        saveBtn.disabled = true; spinner.classList.remove('d-none');

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData
        })
            .then(r => r.json().then(b => ({ status: r.status, b })))
            .then(({ status, b }) => {
                saveBtn.disabled = false; spinner.classList.add('d-none');
                if (status === 422) { showErrors(b.errors ?? {}); return; }
                if (b.success) { memberModal.hide(); loadTable(currentPage); }
                else { document.getElementById('member-form-errors').textContent = b.message; document.getElementById('member-form-errors').classList.remove('d-none'); }
            })
            .catch(() => { saveBtn.disabled = false; spinner.classList.add('d-none'); });
    });

    function memberExportUrl(format) {
        const p = new URLSearchParams();
        if (searchInput.value) p.set('search', searchInput.value);
        if (statusFilter.value) p.set('status', statusFilter.value);
        return `/app/masjid/{{ $mosque->id }}/members/export/${format}?${p.toString()}`;
    }
    document.getElementById('export-members-csv')?.addEventListener('click', e => {
        e.preventDefault(); window.location.href = memberExportUrl('csv');
    });
    document.getElementById('export-members-xlsx')?.addEventListener('click', e => {
        e.preventDefault(); window.location.href = memberExportUrl('xlsx');
    });
    
    loadTable(1);
})();
</script>
@endpush
@endsection