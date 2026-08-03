@extends($ledgerLayout)
@section('heading', __('Customers'))
@section('ledger-content')

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <input type="text" id="customer-search" class="form-control" placeholder="{{ __('Search by name or mobile') }}">
            </div>
            <div class="col-8 col-md-3">
                <select id="customer-status-filter" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="col-4 col-md-3">
                @can('easykhata.manage-customers')
                    <button type="button" class="btn btn-primary w-100" id="btn-add-customer">
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
                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#importCustomersModal">
                    <i class="bi bi-upload"></i>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="importCustomersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Import Customers') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    {{ __('Upload a CSV or Excel file with columns: name, mobile, email, city, address, opening_balance.') }}
                    <a href="{{ route('ledger.customers.import.template') }}">{{ __('Download template') }}</a>
                </p>
                <form id="import-customers-form" enctype="multipart/form-data">
                    <input type="file" name="file" id="import-customers-file" class="form-control" accept=".csv,.xlsx,.xls" required>
                </form>
                <div id="import-customers-result" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary" id="import-customers-submit-btn">
                    <span class="spinner-border spinner-border-sm d-none" id="import-customers-spinner"></span>
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
            <tbody id="customers-table-body">
                <tr><td colspan="5" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="customer-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" id="customer-id" name="id">
                <input type="hidden" id="customer-method" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalTitle">{{ __('Add Customer') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="customer-form-errors" class="alert alert-danger d-none"></div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="customer-name" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">{{ __('Mobile') }}</label>
                            <input type="text" name="mobile" id="customer-mobile" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('City') }}</label>
                            <input type="text" name="city" id="customer-city" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" id="customer-email" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Opening Balance') }}</label>
                            <input type="number" step="0.01" name="opening_balance" id="customer-opening-balance" class="form-control" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Address') }}</label>
                            <textarea name="address" id="customer-address" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Photo') }}</label>
                            <input type="file" name="photo" id="customer-photo" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="customer-status" class="form-select">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="customer-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="customer-save-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete') }} <strong id="delete-customer-name"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-customer-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/ledger-toast.js') }}"></script>
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        ?? '{{ csrf_token() }}';

    const tableUrl = '{{ route("ledger.customers.table") }}';
    const storeUrl = '{{ route("ledger.customers.store") }}';
    const tbody = document.getElementById('customers-table-body');
    const searchInput = document.getElementById('customer-search');
    const statusFilter = document.getElementById('customer-status-filter');

    let deleteId = null;
    let searchDebounce = null;
    let currentPage = 1;

    const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteCustomerModal'));

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
            .catch(() => LedgerToast.error('{{ __('Failed to load customers.') }}'));
    }

    // Intercept Laravel's default pagination links and load via fetch instead of full reload
    tbody.addEventListener('click', function (e) {
        const link = e.target.closest('#customers-pagination a');
        if (link) {
            e.preventDefault();
            const url = new URL(link.href);
            const page = url.searchParams.get('page') || 1;
            loadTable(page);
        }
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => loadTable(1), 400);
    });

    statusFilter.addEventListener('change', () => loadTable(1));

    // Open "Add" modal
    document.getElementById('btn-add-customer')?.addEventListener('click', function () {
        document.getElementById('customer-form').reset();
        document.getElementById('customer-id').value = '';
        document.getElementById('customer-method').value = 'POST';
        document.getElementById('customerModalTitle').textContent = '{{ __('Add Customer') }}';
        document.getElementById('customer-form-errors').classList.add('d-none');
        clearFieldErrors();
        customerModal.show();
    });

    // Open "Edit" modal — fetch current data first
    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-customer');
        if (!btn) return;

        const id = btn.dataset.id;
        fetch(`/app/ledger/customers/${id}/json`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(({ data }) => {
                document.getElementById('customer-form').reset();
                document.getElementById('customer-id').value = data.id;
                document.getElementById('customer-method').value = 'PUT';
                document.getElementById('customer-name').value = data.name ?? '';
                document.getElementById('customer-mobile').value = data.mobile ?? '';
                document.getElementById('customer-city').value = data.city ?? '';
                document.getElementById('customer-email').value = data.email ?? '';
                document.getElementById('customer-opening-balance').value = data.opening_balance ?? 0;
                document.getElementById('customer-address').value = data.address ?? '';
                document.getElementById('customer-status').value = data.status ?? 'active';
                document.getElementById('customerModalTitle').textContent = '{{ __('Edit Customer') }}';
                document.getElementById('customer-form-errors').classList.add('d-none');
                clearFieldErrors();
                customerModal.show();
            })
            .catch(() => LedgerToast.error('{{ __('Failed to load customer.') }}'));
    });

    // Open delete confirmation
    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-customer');
        if (!btn) return;
        deleteId = btn.dataset.id;
        document.getElementById('delete-customer-name').textContent = btn.dataset.name;
        deleteModal.show();
    });

    document.getElementById('confirm-delete-customer-btn').addEventListener('click', function () {
        if (!deleteId) return;

        fetch(`/app/ledger/customers/${deleteId}`, {
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
        document.querySelectorAll('#customer-form .is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('#customer-form .invalid-feedback').forEach(el => el.remove());
    }

    function showFieldErrors(errors) {
        clearFieldErrors();
        Object.entries(errors).forEach(([field, messages]) => {
            const input = document.getElementById(`customer-${field.replace('_', '-')}`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = messages[0];
                input.insertAdjacentElement('afterend', feedback);
            }
        });
    }

    document.getElementById('customer-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const id = document.getElementById('customer-id').value;
        const method = document.getElementById('customer-method').value;
        const url = id ? `/app/ledger/customers/${id}` : '{{ route("ledger.customers.store") }}';

        const formData = new FormData(this);
        if (method === 'PUT') {
            formData.append('_method', 'PUT'); // Laravel method spoofing for multipart PUT
        }

        const saveBtn = document.getElementById('customer-save-btn');
        const spinner = document.getElementById('customer-save-spinner');
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');

        fetch(url, {
            method: 'POST', // always POST + _method spoof, since FormData+PUT is unreliable across browsers
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData,
        })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ status, body }) => {
                saveBtn.disabled = false;
                spinner.classList.add('d-none');

                if (status === 422) {
                    showFieldErrors(body.errors ?? {});
                    return;
                }

                if (body.success) {
                    customerModal.hide();
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

    // Initial load
    loadTable(1);

    function exportUrl(format) {
        const params = new URLSearchParams();
        if (searchInput.value) params.set('search', searchInput.value);
        if (statusFilter.value) params.set('status', statusFilter.value);
        return `/app/ledger/customers/export/${format}?${params.toString()}`;
    }
    document.getElementById('export-csv').addEventListener('click', (e) => { e.preventDefault(); window.location.href = exportUrl('csv'); });
    document.getElementById('export-xlsx').addEventListener('click', (e) => { e.preventDefault(); window.location.href = exportUrl('xlsx'); });
    // import
    document.getElementById('import-customers-submit-btn').addEventListener('click', function () {
        const fileInput = document.getElementById('import-customers-file');
        if (!fileInput.files.length) {
            LedgerToast.error('{{ __('Please choose a file first.') }}');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);

        const btn = this;
        const spinner = document.getElementById('import-customers-spinner');
        const resultDiv = document.getElementById('import-customers-result');
        btn.disabled = true;
        spinner.classList.remove('d-none');
        resultDiv.innerHTML = '';

        fetch('{{ route("ledger.customers.import") }}', {
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