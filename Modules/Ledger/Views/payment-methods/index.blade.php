@extends($ledgerLayout)
@section('heading', __('Payment Methods'))
@section('ledger-content')

<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-primary" id="btn-add-method">
        <i class="bi bi-plus-lg"></i> {{ __('Add Payment Method') }}
    </button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ledger-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="methods-table-body">
                <tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="methodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="method-form" novalidate>
                <input type="hidden" id="method-id">
                <div class="modal-header">
                    <h5 class="modal-title" id="methodModalTitle">{{ __('Add Payment Method') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" id="method-name" class="form-control" required placeholder="{{ __('e.g. Cash, Bank, Cheque, Online') }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select id="method-status" class="form-select">
                            <option value="active">{{ __('Active') }}</option>
                            <option value="inactive">{{ __('Inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="method-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="method-save-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteMethodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Payment Method') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete') }} <strong id="delete-method-name"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-method-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/ledger-toast.js') }}"></script>
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl = '{{ route("ledger.payment-methods.table") }}';
    const tbody = document.getElementById('methods-table-body');

    let deleteId = null;
    let currentPage = 1;

    const methodModal = new bootstrap.Modal(document.getElementById('methodModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteMethodModal'));

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;
        fetch(`${tableUrl}?page=${page}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => { tbody.innerHTML = html; })
            .catch(() => LedgerToast.error('{{ __('Failed to load payment methods.') }}'));
    }

    tbody.addEventListener('click', function (e) {
        const link = e.target.closest('#methods-pagination a');
        if (link) {
            e.preventDefault();
            loadTable(new URL(link.href).searchParams.get('page') || 1);
        }
    });

    document.getElementById('btn-add-method').addEventListener('click', function () {
        document.getElementById('method-form').reset();
        document.getElementById('method-id').value = '';
        document.getElementById('methodModalTitle').textContent = '{{ __('Add Payment Method') }}';
        methodModal.show();
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-method');
        if (!btn) return;
        fetch(`/app/ledger/payment-methods/${btn.dataset.id}/json`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(({ data }) => {
                document.getElementById('method-id').value = data.id;
                document.getElementById('method-name').value = data.name;
                document.getElementById('method-status').value = data.status;
                document.getElementById('methodModalTitle').textContent = '{{ __('Edit Payment Method') }}';
                methodModal.show();
            })
            .catch(() => LedgerToast.error('{{ __('Failed to load payment method.') }}'));
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-method');
        if (!btn) return;
        deleteId = btn.dataset.id;
        document.getElementById('delete-method-name').textContent = btn.dataset.name;
        deleteModal.show();
    });

    document.getElementById('confirm-delete-method-btn').addEventListener('click', function () {
        if (!deleteId) return;
        fetch(`/app/ledger/payment-methods/${deleteId}`, {
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

    document.getElementById('method-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('method-id').value;
        const url = id ? `/app/ledger/payment-methods/${id}` : '{{ route("ledger.payment-methods.store") }}';

        const payload = {
            name: document.getElementById('method-name').value,
            status: document.getElementById('method-status').value,
        };
        if (id) payload._method = 'PUT';

        const saveBtn = document.getElementById('method-save-btn');
        const spinner = document.getElementById('method-save-spinner');
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
                    methodModal.hide();
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