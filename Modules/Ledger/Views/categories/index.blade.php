@extends($ledgerLayout)
@section('heading', __('Categories'))
@section('ledger-content')

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-8 col-md-4">
                <select id="category-type-filter" class="form-select">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="income">{{ __('Income') }}</option>
                    <option value="expense">{{ __('Expense') }}</option>
                </select>
            </div>
            <div class="col-4 col-md-2 offset-md-6">
                <button type="button" class="btn btn-primary w-100" id="btn-add-category">
                    <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">{{ __('Add') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ledger-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="categories-table-body">
                <tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="category-form" novalidate>
                <input type="hidden" id="category-id">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalTitle">{{ __('Add Category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" id="category-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                        <select id="category-type" class="form-select" required>
                            <option value="income">{{ __('Income') }}</option>
                            <option value="expense">{{ __('Expense') }}</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select id="category-status" class="form-select">
                            <option value="active">{{ __('Active') }}</option>
                            <option value="inactive">{{ __('Inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="category-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="category-save-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete') }} <strong id="delete-category-name"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-category-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/ledger-toast.js') }}"></script>
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl = '{{ route("ledger.categories.table") }}';
    const tbody = document.getElementById('categories-table-body');
    const typeFilter = document.getElementById('category-type-filter');

    let deleteId = null;
    let currentPage = 1;

    const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;
        const params = new URLSearchParams();
        if (typeFilter.value) params.set('type', typeFilter.value);
        params.set('page', page);
        fetch(`${tableUrl}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => { tbody.innerHTML = html; })
            .catch(() => LedgerToast.error('{{ __('Failed to load categories.') }}'));
    }

    tbody.addEventListener('click', function (e) {
        const link = e.target.closest('#categories-pagination a');
        if (link) {
            e.preventDefault();
            loadTable(new URL(link.href).searchParams.get('page') || 1);
        }
    });

    typeFilter.addEventListener('change', () => loadTable(1));

    document.getElementById('btn-add-category').addEventListener('click', function () {
        document.getElementById('category-form').reset();
        document.getElementById('category-id').value = '';
        document.getElementById('categoryModalTitle').textContent = '{{ __('Add Category') }}';
        categoryModal.show();
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-category');
        if (!btn) return;
        fetch(`/app/ledger/categories/${btn.dataset.id}/json`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(({ data }) => {
                document.getElementById('category-id').value = data.id;
                document.getElementById('category-name').value = data.name;
                document.getElementById('category-type').value = data.type;
                document.getElementById('category-status').value = data.status;
                document.getElementById('categoryModalTitle').textContent = '{{ __('Edit Category') }}';
                categoryModal.show();
            })
            .catch(() => LedgerToast.error('{{ __('Failed to load category.') }}'));
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-category');
        if (!btn) return;
        deleteId = btn.dataset.id;
        document.getElementById('delete-category-name').textContent = btn.dataset.name;
        deleteModal.show();
    });

    document.getElementById('confirm-delete-category-btn').addEventListener('click', function () {
        if (!deleteId) return;
        fetch(`/app/ledger/categories/${deleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .then(res => res.json().then(body => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                deleteModal.hide();
                if (ok && body.success) { LedgerToast.success(body.message); loadTable(currentPage); }
                else { LedgerToast.error(body.message ?? '{{ __('Delete failed.') }}'); }
            })
            .catch(() => LedgerToast.error('{{ __('Delete failed.') }}'));
    });

    document.getElementById('category-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('category-id').value;
        const url = id ? `/app/ledger/categories/${id}` : '{{ route("ledger.categories.store") }}';

        const payload = {
            name: document.getElementById('category-name').value,
            type: document.getElementById('category-type').value,
            status: document.getElementById('category-status').value,
        };
        if (id) payload._method = 'PUT';

        const saveBtn = document.getElementById('category-save-btn');
        const spinner = document.getElementById('category-save-spinner');
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ status, body }) => {
                saveBtn.disabled = false;
                spinner.classList.add('d-none');
                if (status === 422) {
                    LedgerToast.error(Object.values(body.errors ?? {}).flat().join(' '));
                    return;
                }
                if (body.success) {
                    categoryModal.hide();
                    LedgerToast.success(body.message);
                    loadTable(currentPage);
                } else {
                    LedgerToast.error(body.message ?? '{{ __('Something went wrong.') }}');
                }
            })
            .catch(() => {
                saveBtn.disabled = false;
                spinner.classList.add('d-none');
                LedgerToast.error('{{ __('Something went wrong.') }}');
            });
    });

    loadTable(1);
})();
</script>
@endpush
@endsection