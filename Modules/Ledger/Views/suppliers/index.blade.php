@extends($ledgerLayout)
@section('heading', __('Suppliers'))
@section('ledger-content')

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <input type="text" id="supplier-search" class="form-control" placeholder="{{ __('Search by name or mobile') }}">
            </div>
            <div class="col-8 col-md-3">
                <select id="supplier-status-filter" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="col-4 col-md-3">
                @can('easykhata.manage-suppliers')
                    <button type="button" class="btn btn-primary w-100" id="btn-add-supplier">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">{{ __('Add') }}</span>
                    </button>
                @endcan
            </div>
            <div class="col-4 col-md-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary w-100 dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-download"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" id="export-csv">{{ __('Export CSV') }}</a></li>
                        <li><a class="dropdown-item" href="#" id="export-xlsx">{{ __('Export Excel') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-4 col-md-2">
                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#importSuppliersModal">
                    <i class="bi bi-upload"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importSuppliersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Import Suppliers') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    {{ __('Upload a CSV or Excel file with columns: name, mobile, email, city, address, opening_balance.') }}
                    <a href="{{ route('ledger.suppliers.import.template') }}">{{ __('Download template') }}</a>
                </p>
                <form id="import-suppliers-form" enctype="multipart/form-data">
                    <input type="file" name="file" id="import-suppliers-file" class="form-control" accept=".csv,.xlsx,.xls" required>
                </form>
                <div id="import-suppliers-result" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary" id="import-suppliers-submit-btn">
                    <span class="spinner-border spinner-border-sm d-none" id="import-suppliers-spinner"></span>
                    {{ __('Upload & Import') }}
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
                    <th>{{ __('Mobile') }}</th>
                    <th>{{ __('City') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="suppliers-table-body">
                <tr><td colspan="5" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="supplier-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" id="supplier-id" name="id">
                <input type="hidden" id="supplier-method" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="supplierModalTitle">{{ __('Add Supplier') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="supplier-form-errors" class="alert alert-danger d-none"></div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="supplier-name" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">{{ __('Mobile') }}</label>
                            <input type="text" name="mobile" id="supplier-mobile" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('City') }}</label>
                            <input type="text" name="city" id="supplier-city" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" id="supplier-email" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Opening Balance') }}</label>
                            <input type="number" step="0.01" name="opening_balance" id="supplier-opening-balance" class="form-control" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Address') }}</label>
                            <textarea name="address" id="supplier-address" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Photo') }}</label>
                            <input type="file" name="photo" id="supplier-photo" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="supplier-status" class="form-select">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="supplier-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="supplier-save-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Supplier') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete') }} <strong id="delete-supplier-name"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-supplier-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/ledger-toast.js') }}"></script>
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl = '{{ route("ledger.suppliers.table") }}';
    const tbody = document.getElementById('suppliers-table-body');
    const searchInput = document.getElementById('supplier-search');
    const statusFilter = document.getElementById('supplier-status-filter');

    let deleteId = null;
    let searchDebounce = null;
    let currentPage = 1;

    const supplierModal = new bootstrap.Modal(document.getElementById('supplierModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteSupplierModal'));

    function buildTableUrl(page = 1) {
        const params = new URLSearchParams();
        if (searchInput.value) params.set('search', searchInput.value);
        if (statusFilter.value) params.set('status', statusFilter.value);
        params.set('page', page);
        return `${tableUrl}?${params.toString()}`;
    }

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;

        fetch(buildTableUrl(page), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => { tbody.innerHTML = html; })
            .catch(() => LedgerToast.error('{{ __('Failed to load suppliers.') }}'));
        // Deep-link support: ?supplier_id=X pre-opens Add modal with that supplier selected
        // const preselectSupplier = urlParams.get('supplier_id');
        // if (preselectSupplier) {
        //     filters.supplier.value = preselectSupplier;
        //     document.getElementById('btn-add-transaction')?.click();
        //     setTimeout(() => {
        //         document.getElementById('transaction-type').value = 'credit';
        //         applyTypeVisibility();
        //         document.getElementById('transaction-supplier').value = preselectSupplier;
        //     }, 150);
        // }
    }

    tbody.addEventListener('click', function (e) {
        const link = e.target.closest('#suppliers-pagination a');
        if (link) {
            e.preventDefault();
            const url = new URL(link.href);
            loadTable(url.searchParams.get('page') || 1);
        }
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => loadTable(1), 400);
    });

    statusFilter.addEventListener('change', () => loadTable(1));

    document.getElementById('btn-add-supplier')?.addEventListener('click', function () {
        document.getElementById('supplier-form').reset();
        document.getElementById('supplier-id').value = '';
        document.getElementById('supplier-method').value = 'POST';
        document.getElementById('supplierModalTitle').textContent = '{{ __('Add Supplier') }}';
        document.getElementById('supplier-form-errors').classList.add('d-none');
        clearFieldErrors();
        supplierModal.show();
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-supplier');
        if (!btn) return;
        const id = btn.dataset.id;
        fetch(`/app/ledger/suppliers/${id}/json`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(({ data }) => {
                document.getElementById('supplier-form').reset();
                document.getElementById('supplier-id').value = data.id;
                document.getElementById('supplier-method').value = 'PUT';
                document.getElementById('supplier-name').value = data.name ?? '';
                document.getElementById('supplier-mobile').value = data.mobile ?? '';
                document.getElementById('supplier-city').value = data.city ?? '';
                document.getElementById('supplier-email').value = data.email ?? '';
                document.getElementById('supplier-opening-balance').value = data.opening_balance ?? 0;
                document.getElementById('supplier-address').value = data.address ?? '';
                document.getElementById('supplier-status').value = data.status ?? 'active';
                document.getElementById('supplierModalTitle').textContent = '{{ __('Edit Supplier') }}';
                document.getElementById('supplier-form-errors').classList.add('d-none');
                clearFieldErrors();
                supplierModal.show();
            })
            .catch(() => LedgerToast.error('{{ __('Failed to load supplier.') }}'));
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-supplier');
        if (!btn) return;
        deleteId = btn.dataset.id;
        document.getElementById('delete-supplier-name').textContent = btn.dataset.name;
        deleteModal.show();
    });

    document.getElementById('confirm-delete-supplier-btn').addEventListener('click', function () {
        if (!deleteId) return;
        fetch(`/app/ledger/suppliers/${deleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .then(res => res.json().then(body => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                deleteModal.hide();
                if (ok && body.success) {
                    LedgerToast.success(body.message);
                    loadTable(currentPage);
                } else {
                    LedgerToast.error(body.message ?? '{{ __('Delete failed.') }}');
                }
            })
            .catch(() => LedgerToast.error('{{ __('Delete failed.') }}'));
    });

    function clearFieldErrors() {
        document.querySelectorAll('#supplier-form .is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('#supplier-form .invalid-feedback').forEach(el => el.remove());
    }

    function showFieldErrors(errors) {
        clearFieldErrors();
        Object.entries(errors).forEach(([field, messages]) => {
            const input = document.getElementById(`supplier-${field.replace('_', '-')}`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = messages[0];
                input.insertAdjacentElement('afterend', feedback);
            }
        });
    }

    document.getElementById('supplier-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('supplier-id').value;
        const method = document.getElementById('supplier-method').value;
        const url = id ? `/app/ledger/suppliers/${id}` : '{{ route("ledger.suppliers.store") }}';

        const formData = new FormData(this);
        if (method === 'PUT') formData.append('_method', 'PUT');

        const saveBtn = document.getElementById('supplier-save-btn');
        const spinner = document.getElementById('supplier-save-spinner');
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData,
        })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ status, body }) => {
                saveBtn.disabled = false;
                spinner.classList.add('d-none');
                if (status === 422) { showFieldErrors(body.errors ?? {}); return; }
                if (body.success) {
                    supplierModal.hide();
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

    function exportUrl(format) {
        const params = new URLSearchParams();
        if (searchInput.value) params.set('search', searchInput.value);
        if (statusFilter.value) params.set('status', statusFilter.value);
        return `/app/ledger/suppliers/export/${format}?${params.toString()}`;
    }
    document.getElementById('export-csv').addEventListener('click', (e) => { e.preventDefault(); window.location.href = exportUrl('csv'); });
    document.getElementById('export-xlsx').addEventListener('click', (e) => { e.preventDefault(); window.location.href = exportUrl('xlsx'); });

    // import
    document.getElementById('import-suppliers-submit-btn').addEventListener('click', function () {
        const fileInput = document.getElementById('import-suppliers-file');
        if (!fileInput.files.length) {
            LedgerToast.error('{{ __('Please choose a file first.') }}');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);

        const btn = this;
        const spinner = document.getElementById('import-suppliers-spinner');
        const resultDiv = document.getElementById('import-suppliers-result');
        btn.disabled = true;
        spinner.classList.remove('d-none');
        resultDiv.innerHTML = '';

        fetch('{{ route("ledger.suppliers.import") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData,
        })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ status, body }) => {
                btn.disabled = false;
                spinner.classList.add('d-none');

                if (status === 422) {
                    resultDiv.innerHTML = `<div class="alert alert-danger">${Object.values(body.errors ?? {}).flat().join(' ')}</div>`;
                    return;
                }

                let html = `<div class="alert alert-success">${body.message}</div>`;
                if (body.failed_count > 0) {
                    html += `<div class="alert alert-warning"><strong>${body.failed_count} {{ __('rows failed') }}:</strong><ul class="mb-0 small">`;
                    body.failed.forEach(f => {
                        html += `<li>{{ __('Row') }} ${f.row} (${f.name}): ${f.errors.join(', ')}</li>`;
                    });
                    html += `</ul></div>`;
                }
                resultDiv.innerHTML = html;

                if (body.imported_count > 0) {
                    LedgerToast.success(body.message);
                    loadTable(1);
                }
            })
            .catch(() => {
                btn.disabled = false;
                spinner.classList.add('d-none');
                LedgerToast.error('{{ __('Import failed.') }}');
            });
    });
    
})();


</script>
@endpush
@endsection