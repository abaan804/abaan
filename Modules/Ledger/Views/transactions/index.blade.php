@extends($ledgerLayout)
@section('heading', __('Transactions'))
@section('ledger-content')

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-2">
                <input type="date" id="filter-date-from" class="form-control" placeholder="{{ __('From') }}">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="filter-date-to" class="form-control" placeholder="{{ __('To') }}">
            </div>
            <div class="col-6 col-md-2">
                <select id="filter-type" class="form-select">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="credit">{{ __('Credit') }}</option>
                    <option value="debit">{{ __('Debit') }}</option>
                    <option value="income">{{ __('Income') }}</option>
                    <option value="expense">{{ __('Expense') }}</option>
                    <option value="transfer">{{ __('Transfer') }}</option>
                    <option value="opening_balance">{{ __('Opening Balance') }}</option>
                    <option value="adjustment">{{ __('Adjustment') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <input type="text" id="filter-search" class="form-control" placeholder="{{ __('Search reference/notes') }}">
            </div>
            <div class="col-12 col-md-3">
                @can('easykhata.manage-transactions')
                    <button type="button" class="btn btn-primary w-100" id="btn-add-transaction">
                        <i class="bi bi-plus-lg"></i> {{ __('Add Transaction') }}
                    </button>
                @endcan
            </div>
            <div class="col-6 col-md-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary w-100 dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-download"></i> <span class="d-none d-sm-inline">{{ __('Export') }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" id="export-csv">{{ __('Export CSV') }}</a></li>
                        <li><a class="dropdown-item" href="#" id="export-xlsx">{{ __('Export Excel') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#importTransactionsModal">
                    <i class="bi bi-upload"></i> <span class="d-none d-sm-inline">{{ __('Import') }}</span>
                </button>
            </div>
        </div>
        <div class="row g-2 mt-2">
            <div class="col-6 col-md-3">
                <select id="filter-customer" class="form-select">
                    <option value="">{{ __('All Customers') }}</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select id="filter-supplier" class="form-select">
                    <option value="">{{ __('All Suppliers') }}</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select id="filter-category" class="form-select">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select id="filter-payment-method" class="form-select">
                    <option value="">{{ __('All Payment Methods') }}</option>
                    @foreach ($paymentMethods as $pm)
                        <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<!-- Import Model -->
 <div class="modal fade" id="importTransactionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Import Transactions') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    {{ __('Columns: date, type, customer, supplier, category, payment_method, amount, reference_no, notes.') }}
                    <br>
                    {{ __('Customers/Suppliers must already exist (add them first). Categories and Payment Methods will be created automatically if new.') }}
                    <br>
                    <a href="{{ route('ledger.transactions.import.template') }}">{{ __('Download template') }}</a>
                </p>
                <input type="file" id="import-transactions-file" class="form-control" accept=".csv,.xlsx,.xls" required>
                <div id="import-transactions-result" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary" id="import-transactions-submit-btn">
                    <span class="spinner-border spinner-border-sm d-none" id="import-transactions-spinner"></span>
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
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Party') }}</th>
                    {{-- <th>{{ __('Category') }}</th> --}}
                    <th>{{ __('Method') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="transactions-table-body">
                <tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>
{{-- VIew Modal --}}
<div class="modal fade" id="transactionViewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">{{ __('Transaction Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-bordered mb-0">
                    <tr>
                        <th width="35%">{{ __('Reference No') }}</th>
                        <td id="view-reference"></td>
                    </tr>

                    <tr>
                        <th>{{ __('Category') }}</th>
                        <td id="view-category"></td>
                    </tr>

                    <tr>
                        <th>{{ __('Payment Method') }}</th>
                        <td id="view-method"></td>
                    </tr>

                    <tr>
                        <th>{{ __('Notes') }}</th>
                        <td id="view-notes"></td>
                    </tr>
                    <tr>
                        <th>Attachments</th>
                        <td id="view-attachments"></td>
                    </tr>
                </table>

            </div>

        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="transaction-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" id="transaction-id" name="id">
                <input type="hidden" id="transaction-method" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionModalTitle">{{ __('Add Transaction') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="transaction-form-errors" class="alert alert-danger d-none"></div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                            <select name="type" id="transaction-type" class="form-select" required>
                                <option value="debit">{{ __('Debit (Customer Paid You/ Supplier Owe You)') }}</option>
                                <option value="credit">{{ __('Credit (You Owe Customer/You Paid Supplier)') }}</option>
                                <option value="income">{{ __('Income') }}</option>
                                <option value="expense">{{ __('Expense') }}</option>
                                <option value="transfer">{{ __('Transfer') }}</option>
                                <option value="opening_balance">{{ __('Opening Balance') }}</option>
                                <option value="adjustment">{{ __('Adjustment') }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="transaction-amount" class="form-control" required>
                        </div>

                        <div class="col-12 col-md-6" id="field-customer">
                            <label class="form-label">{{ __('Customer') }}</label>
                            <select name="customer_id" id="transaction-customer" class="form-select">
                                <option value="">{{ __('— Select Customer —') }}</option>
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6" id="field-supplier">
                            <label class="form-label">{{ __('Supplier') }}</label>
                            <select name="supplier_id" id="transaction-supplier" class="form-select">
                                <option value="">{{ __('— Select Supplier —') }}</option>
                                @foreach ($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6" id="field-category">
                            <label class="form-label">{{ __('Category') }}</label>
                            <select name="category_id" id="transaction-category" class="form-select">
                                <option value="">{{ __('— Select Category —') }}</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" data-type="{{ $cat->type }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('Payment Method') }}</label>
                            <select name="payment_method_id" id="transaction-payment-method" class="form-select">
                                <option value="">{{ __('— Select Method —') }}</option>
                                @foreach ($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('Transaction Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" id="transaction-date" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('Reference No.') }}</label>
                            <input type="text" name="reference_no" id="transaction-reference" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" id="transaction-notes" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="col-12" id="field-attachments">
                            <label class="form-label">{{ __('Attachments') }}</label>
                            <input type="file" name="attachments[]" id="transaction-attachments" class="form-control" multiple>
                            <div id="existing-attachments" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="transaction-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="transaction-save-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Transaction') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete this transaction of') }} <strong id="delete-transaction-name"></strong>? {{ __('This cannot be undone.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-transaction-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/ledger-toast.js') }}"></script>
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl = '{{ route("ledger.transactions.table") }}';
    const tbody = document.getElementById('transactions-table-body');

    const filters = {
        dateFrom: document.getElementById('filter-date-from'),
        dateTo: document.getElementById('filter-date-to'),
        type: document.getElementById('filter-type'),
        search: document.getElementById('filter-search'),
        customer: document.getElementById('filter-customer'),
        supplier: document.getElementById('filter-supplier'),
        category: document.getElementById('filter-category'),
        paymentMethod: document.getElementById('filter-payment-method'),
    };

    let deleteId = null;
    let searchDebounce = null;
    let currentPage = 1;

    const transactionModal = new bootstrap.Modal(document.getElementById('transactionModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteTransactionModal'));

    function buildTableUrl(page = 1) {
        const params = new URLSearchParams();
        if (filters.dateFrom.value) params.set('date_from', filters.dateFrom.value);
        if (filters.dateTo.value) params.set('date_to', filters.dateTo.value);
        if (filters.type.value) params.set('type', filters.type.value);
        if (filters.search.value) params.set('search', filters.search.value);
        if (filters.customer.value) params.set('customer_id', filters.customer.value);
        if (filters.supplier.value) params.set('supplier_id', filters.supplier.value);
        if (filters.category.value) params.set('category_id', filters.category.value);
        if (filters.paymentMethod.value) params.set('payment_method_id', filters.paymentMethod.value);
        params.set('page', page);
        return `${tableUrl}?${params.toString()}`;
    }

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;
        fetch(buildTableUrl(page), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => { tbody.innerHTML = html; })
            .catch(() => LedgerToast.error('{{ __('Failed to load transactions.') }}'));

        // Deep-link support: ?customer_id=X pre-opens Add modal with that customer selected
        const urlParams = new URLSearchParams(window.location.search);
        const preselectCustomer = urlParams.get('customer_id');
        if (preselectCustomer) {
            filters.customer.value = preselectCustomer;
            document.getElementById('btn-add-transaction')?.click();
            setTimeout(() => {
                document.getElementById('transaction-type').value = 'debit';
                applyTypeVisibility();
                document.getElementById('transaction-customer').value = preselectCustomer;
            }, 150);
        }
    }

    tbody.addEventListener('click', function (e) {
        const link = e.target.closest('#transactions-pagination a');
        if (link) {
            e.preventDefault();
            const url = new URL(link.href);
            loadTable(url.searchParams.get('page') || 1);
        }
    });

    Object.values(filters).forEach(el => {
        const eventName = (el.type === 'text') ? 'input' : 'change';
        el.addEventListener(eventName, function () {
            if (eventName === 'input') {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(() => loadTable(1), 400);
            } else {
                loadTable(1);
            }
        });
    });

    // Type-dependent field visibility
    function applyTypeVisibility() {
        const type = document.getElementById('transaction-type').value;
        const showCustomer = ['debit', 'credit', 'opening_balance', 'adjustment'].includes(type);
        const showSupplier = showCustomer;
        const showCategory = ['income', 'expense'].includes(type);

        document.getElementById('field-customer').style.display = showCustomer ? '' : 'none';
        document.getElementById('field-supplier').style.display = showSupplier ? '' : 'none';
        document.getElementById('field-category').style.display = showCategory ? '' : 'none';
    }
    document.getElementById('transaction-type').addEventListener('change', applyTypeVisibility);

    document.getElementById('btn-add-transaction')?.addEventListener('click', function () {
        document.getElementById('transaction-form').reset();
        document.getElementById('transaction-id').value = '';
        document.getElementById('transaction-method').value = 'POST';
        document.getElementById('transaction-date').value = new Date().toISOString().slice(0, 10);
        document.getElementById('transactionModalTitle').textContent = '{{ __('Add Transaction') }}';
        document.getElementById('transaction-form-errors').classList.add('d-none');
        document.getElementById('existing-attachments').innerHTML = '';
        document.getElementById('field-attachments').style.display = '';
        clearFieldErrors();
        applyTypeVisibility();
        transactionModal.show();
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-transaction');
        if (!btn) return;
        const id = btn.dataset.id;
        fetch(`/app/ledger/transactions/${id}/json`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(({ data }) => {
                document.getElementById('transaction-form').reset();
                document.getElementById('transaction-id').value = data.id;
                document.getElementById('transaction-method').value = 'PUT';
                document.getElementById('transaction-type').value = data.type;
                document.getElementById('transaction-amount').value = data.amount;
                document.getElementById('transaction-customer').value = data.customer_id ?? '';
                document.getElementById('transaction-supplier').value = data.supplier_id ?? '';
                document.getElementById('transaction-category').value = data.category_id ?? '';
                document.getElementById('transaction-payment-method').value = data.payment_method_id ?? '';
                document.getElementById('transaction-date').value = data.transaction_date;
                document.getElementById('transaction-reference').value = data.reference_no ?? '';
                document.getElementById('transaction-notes').value = data.notes ?? '';
                document.getElementById('transactionModalTitle').textContent = '{{ __('Edit Transaction') }}';
                document.getElementById('transaction-form-errors').classList.add('d-none');

                // Attachments are not editable via this form (no add/remove on edit upload field) — show existing as removable chips
                document.getElementById('field-attachments').style.display = 'none';
                const existingWrap = document.getElementById('existing-attachments');
                existingWrap.innerHTML = '';
                (data.attachments || []).forEach(att => {
                    const chip = document.createElement('span');
                    chip.className = 'badge bg-light text-dark border d-inline-flex align-items-center gap-1';
                    chip.innerHTML = `<a href="/storage/${att.file_path}" target="_blank" class="text-decoration-none">${att.original_name}</a>
                        <button type="button" class="btn-close btn-close-sm" style="font-size:.6rem;" data-attachment-id="${att.id}"></button>`;
                    existingWrap.appendChild(chip);
                });

                clearFieldErrors();
                applyTypeVisibility();
                transactionModal.show();
            })
            .catch(() => LedgerToast.error('{{ __('Failed to load transaction.') }}'));
    });

    // Remove an existing attachment (separate small AJAX action, independent of the main form save)
    document.getElementById('existing-attachments').addEventListener('click', function (e) {
        const btn = e.target.closest('[data-attachment-id]');
        if (!btn) return;
        const attachmentId = btn.dataset.attachmentId;
        fetch(`/app/ledger/transaction-attachments/${attachmentId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .then(res => res.json())
            .then(body => {
                if (body.success) {
                    btn.closest('span').remove();
                    LedgerToast.success(body.message);
                } else {
                    LedgerToast.error(body.message);
                }
            });
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-transaction');
        if (!btn) return;
        deleteId = btn.dataset.id;
        document.getElementById('delete-transaction-name').textContent = btn.dataset.name;
        deleteModal.show();
    });

    document.getElementById('confirm-delete-transaction-btn').addEventListener('click', function () {
        if (!deleteId) return;
        fetch(`/app/ledger/transactions/${deleteId}`, {
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
        document.querySelectorAll('#transaction-form .is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('#transaction-form .invalid-feedback').forEach(el => el.remove());
    }

    function showFieldErrors(errors) {
        clearFieldErrors();
        Object.entries(errors).forEach(([field, messages]) => {
            const input = document.getElementById(`transaction-${field.replace(/_/g, '-')}`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = messages[0];
                input.insertAdjacentElement('afterend', feedback);
            }
        });
    }

    document.getElementById('transaction-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('transaction-id').value;
        const method = document.getElementById('transaction-method').value;
        const url = id ? `/app/ledger/transactions/${id}` : '{{ route("ledger.transactions.store") }}';

        const formData = new FormData(this);
        if (method === 'PUT') formData.append('_method', 'PUT');

        const saveBtn = document.getElementById('transaction-save-btn');
        const spinner = document.getElementById('transaction-save-spinner');
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
                    transactionModal.hide();
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
        if (filters.dateFrom.value) params.set('date_from', filters.dateFrom.value);
        if (filters.dateTo.value) params.set('date_to', filters.dateTo.value);
        if (filters.type.value) params.set('type', filters.type.value);
        if (filters.search.value) params.set('search', filters.search.value);
        if (filters.customer.value) params.set('customer_id', filters.customer.value);
        if (filters.supplier.value) params.set('supplier_id', filters.supplier.value);
        if (filters.category.value) params.set('category_id', filters.category.value);
        if (filters.paymentMethod.value) params.set('payment_method_id', filters.paymentMethod.value);
        return `/app/ledger/transactions/export/${format}?${params.toString()}`;
    }
    document.getElementById('export-csv').addEventListener('click', (e) => { e.preventDefault(); window.location.href = exportUrl('csv'); });
    document.getElementById('export-xlsx').addEventListener('click', (e) => { e.preventDefault(); window.location.href = exportUrl('xlsx'); });

     document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-view-transaction');
        if (!btn) return;

        document.getElementById('view-reference').textContent = btn.getAttribute('data-reference') || '—';
        document.getElementById('view-category').textContent = btn.getAttribute('data-category') || '—';
        document.getElementById('view-method').textContent = btn.getAttribute('data-method') || '—';
        document.getElementById('view-notes').textContent = btn.getAttribute('data-notes') || '—';

        // attachments
        const attachments = JSON.parse(btn.getAttribute('data-attachments') || '[]');

        const container = document.getElementById('view-attachments');

        if (!attachments.length) {
            container.innerHTML = '—';
            return;
        }

        container.innerHTML = attachments.map(file => {
            return `
                <div class="mb-1">
                    <a href="/storage/${file.file_path}" target="_blank">
                        📎 ${file.original_name}
                    </a>
                </div>
            `;
        }).join('');

        
    });

    document.getElementById('import-transactions-submit-btn').addEventListener('click', function () {
        const fileInput = document.getElementById('import-transactions-file');
        if (!fileInput.files.length) {
            LedgerToast.error('{{ __('Please choose a file first.') }}');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);

        const btn = this;
        const spinner = document.getElementById('import-transactions-spinner');
        const resultDiv = document.getElementById('import-transactions-result');
        btn.disabled = true;
        spinner.classList.remove('d-none');
        resultDiv.innerHTML = '';

        fetch('{{ route("ledger.transactions.import") }}', {
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
                        html += `<li>{{ __('Row') }} ${f.row} (${f.summary}): ${f.errors.join(' ')}</li>`;
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