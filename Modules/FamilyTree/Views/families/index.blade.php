@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Families'))
@section('ft-content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
        <input type="text" id="family-search" class="form-control" style="max-width:280px;"
               placeholder="{{ __('Search families...') }}">
        <select id="family-status-filter" class="form-select" style="max-width:160px;">
            <option value="">{{ __('All Statuses') }}</option>
            <option value="active">{{ __('Active') }}</option>
            <option value="inactive">{{ __('Inactive') }}</option>
        </select>
    </div>
    @can('familytree.manage-families')
        <button type="button" class="btn btn-primary" id="btn-add-family">
            <i class="bi bi-plus-lg"></i> {{ __('Add Family') }}
        </button>
    @endcan
</div>

<div id="families-grid" class="row g-3">
    <div class="col-12 text-center py-5">
        <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div>
    </div>
</div>

{{-- Add/Edit Modal --}}
@can('familytree.manage-families')
<div class="modal fade" id="familyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="family-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" id="family-id">
                <div class="modal-header">
                    <h5 class="modal-title" id="familyModalTitle">{{ __('Add Family') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="family-form-errors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Family Name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="family-name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Village') }}</label>
                            <input type="text" id="family-village" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('City') }}</label>
                            <input type="text" id="family-city" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('District') }}</label>
                            <input type="text" id="family-district" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Province') }}</label>
                            <input type="text" id="family-province" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Country') }}</label>
                            <input type="text" id="family-country" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select id="family-status" class="form-select">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Address') }}</label>
                            <textarea id="family-address" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea id="family-description" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea id="family-notes" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Family Photo') }}</label>
                            <input type="file" id="family-photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="family-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="family-spinner"></span>
                        {{ __('Save Family') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteFamilyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle"></i> {{ __('Delete Family') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete') }} <strong id="delete-family-name"></strong>?</p>
                <p class="text-danger small">
                    {{ __('Families with members cannot be deleted. Remove all members first or set the family to Inactive.') }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-family-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl  = '{{ route("familytree.families.table") }}';
    const grid      = document.getElementById('families-grid');
    const search    = document.getElementById('family-search');
    const statusFilter = document.getElementById('family-status-filter');

    const familyModal = new bootstrap.Modal(document.getElementById('familyModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteFamilyModal'));
    let deleteId = null;
    let searchDebounce = null;
    let currentSearch = '';
    let currentStatus = '';

    // ── Load grid ──────────────────────────────────────────────────────────────
    function loadGrid() {
        grid.innerHTML = `<div class="col-12 text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div></div>`;

        const params = new URLSearchParams();
        if (currentSearch) params.set('search', currentSearch);
        if (currentStatus) params.set('status', currentStatus);

        fetch(`${tableUrl}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => { grid.innerHTML = html; });
    }

    search.addEventListener('input', function () {
        clearTimeout(searchDebounce);
        currentSearch = this.value;
        searchDebounce = setTimeout(loadGrid, 400);
    });
    statusFilter.addEventListener('change', function () {
        currentStatus = this.value;
        loadGrid();
    });

    // ── Add ────────────────────────────────────────────────────────────────────
    document.getElementById('btn-add-family')?.addEventListener('click', () => {
        document.getElementById('family-form').reset();
        document.getElementById('family-id').value = '';
        document.getElementById('familyModalTitle').textContent = '{{ __('Add Family') }}';
        document.getElementById('family-form-errors').classList.add('d-none');
        document.getElementById('family-country').value = 'Pakistan';
        familyModal.show();
    });

    // ── Edit ───────────────────────────────────────────────────────────────────
    grid.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.btn-edit-family');
        if (editBtn) {
            fetch(`/app/family-tree/families/${editBtn.dataset.id}/json`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(({ data: f }) => {
                document.getElementById('family-form').reset();
                document.getElementById('family-id').value     = f.id;
                document.getElementById('family-name').value        = f.name ?? '';
                document.getElementById('family-village').value     = f.village ?? '';
                document.getElementById('family-city').value        = f.city ?? '';
                document.getElementById('family-district').value    = f.district ?? '';
                document.getElementById('family-province').value    = f.province ?? '';
                document.getElementById('family-country').value     = f.country ?? '';
                document.getElementById('family-status').value      = f.status ?? 'active';
                document.getElementById('family-address').value     = f.address ?? '';
                document.getElementById('family-description').value = f.description ?? '';
                document.getElementById('family-notes').value       = f.notes ?? '';
                document.getElementById('familyModalTitle').textContent = '{{ __('Edit Family') }}';
                document.getElementById('family-form-errors').classList.add('d-none');
                familyModal.show();
            });
        }

        const deleteBtn = e.target.closest('.btn-delete-family');
        if (deleteBtn) {
            deleteId = deleteBtn.dataset.id;
            document.getElementById('delete-family-name').textContent = deleteBtn.dataset.name;
            deleteModal.show();
        }
    });

    // ── Delete ─────────────────────────────────────────────────────────────────
    document.getElementById('confirm-delete-family-btn')?.addEventListener('click', () => {
        if (!deleteId) return;
        fetch(`/app/family-tree/families/${deleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json().then(b => ({ ok: r.ok, b })))
        .then(({ ok, b }) => {
            deleteModal.hide();
            if (ok && b.success) { FtToast.success(b.message); loadGrid(); }
            else FtToast.error(b.message ?? '{{ __('Delete failed.') }}');
        });
    });

    // ── Save ───────────────────────────────────────────────────────────────────
    document.getElementById('family-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const id  = document.getElementById('family-id').value;
        const url = id ? `/app/family-tree/families/${id}` : '{{ route("familytree.families.store") }}';

        const fd = new FormData();
        fd.append('name',        document.getElementById('family-name').value);
        fd.append('village',     document.getElementById('family-village').value);
        fd.append('city',        document.getElementById('family-city').value);
        fd.append('district',    document.getElementById('family-district').value);
        fd.append('province',    document.getElementById('family-province').value);
        fd.append('country',     document.getElementById('family-country').value);
        fd.append('status',      document.getElementById('family-status').value);
        fd.append('address',     document.getElementById('family-address').value);
        fd.append('description', document.getElementById('family-description').value);
        fd.append('notes',       document.getElementById('family-notes').value);
        const photo = document.getElementById('family-photo').files[0];
        if (photo) fd.append('photo', photo);
        if (id) fd.append('_method', 'PUT');

        const btn = document.getElementById('family-save-btn');
        const sp  = document.getElementById('family-spinner');
        const eb  = document.getElementById('family-form-errors');
        btn.disabled = true; sp.classList.remove('d-none');

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: fd,
        })
        .then(r => r.json().then(b => ({ status: r.status, b })))
        .then(({ status, b }) => {
            btn.disabled = false; sp.classList.add('d-none');
            if (status === 422) {
                eb.textContent = Object.values(b.errors ?? {}).flat().join(' ');
                eb.classList.remove('d-none');
                return;
            }
            if (b.success) { familyModal.hide(); FtToast.success(b.message); loadGrid(); }
            else { eb.textContent = b.message; eb.classList.remove('d-none'); }
        })
        .catch(() => { btn.disabled = false; sp.classList.add('d-none'); });
    });

    loadGrid();
})();
</script>
@endpush
@endsection