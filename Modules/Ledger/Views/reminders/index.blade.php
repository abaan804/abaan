@extends($ledgerLayout)
@section('heading', __('Reminders'))
@section('ledger-content')

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-8 col-md-4">
                <select id="reminder-status-filter" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="sent">{{ __('Sent') }}</option>
                    <option value="dismissed">{{ __('Dismissed') }}</option>
                </select>
            </div>
            <div class="col-4 col-md-2 offset-md-6">
                <button type="button" class="btn btn-primary w-100" id="btn-add-reminder">
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
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Party') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Channel') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="reminders-table-body">
                <tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="reminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reminder-form" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add Reminder') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="reminder-form-errors" class="alert alert-danger d-none"></div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                        <input type="text" id="reminder-title" class="form-control" required placeholder="{{ __('e.g. Follow up on invoice') }}">
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">{{ __('Customer') }}</label>
                            <select id="reminder-customer" class="form-select">
                                <option value="">{{ __('— None —') }}</option>
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Supplier') }}</label>
                            <select id="reminder-supplier" class="form-select">
                                <option value="">{{ __('— None —') }}</option>
                                @foreach ($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Due Date') }} <span class="text-danger">*</span></label>
                            <input type="date" id="reminder-due-date" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Amount') }}</label>
                            <input type="number" step="0.01" id="reminder-amount" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notify Via') }}</label>
                            <select id="reminder-channel" class="form-select">
                                <option value="in_app">{{ __('In-App') }}</option>
                                <option value="sms">{{ __('SMS') }}</option>
                                <option value="whatsapp">{{ __('WhatsApp') }}</option>
                                <option value="email">{{ __('Email') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="reminder-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="reminder-save-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteReminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Reminder') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete') }} <strong id="delete-reminder-name"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-reminder-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/ledger-toast.js') }}"></script>
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl = '{{ route("ledger.reminders.table") }}';
    const tbody = document.getElementById('reminders-table-body');
    const statusFilter = document.getElementById('reminder-status-filter');

    let deleteId = null;
    let currentPage = 1;

    const reminderModal = new bootstrap.Modal(document.getElementById('reminderModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteReminderModal'));

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;
        const params = new URLSearchParams();
        if (statusFilter.value) params.set('status', statusFilter.value);
        params.set('page', page);
        fetch(`${tableUrl}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => { tbody.innerHTML = html; })
            .catch(() => LedgerToast.error('{{ __('Failed to load reminders.') }}'));
    }

    tbody.addEventListener('click', function (e) {
        const link = e.target.closest('#reminders-pagination a');
        if (link) {
            e.preventDefault();
            loadTable(new URL(link.href).searchParams.get('page') || 1);
        }
    });

    statusFilter.addEventListener('change', () => loadTable(1));

    function clearFieldErrors() {
        document.querySelectorAll('#reminder-form .is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('#reminder-form .invalid-feedback').forEach(el => el.remove());
    }

    function showFieldErrors(errors) {
        clearFieldErrors();
        Object.entries(errors).forEach(([field, messages]) => {
            const input = document.getElementById(`reminder-${field.replace(/_/g, '-')}`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = messages[0];
                input.insertAdjacentElement('afterend', feedback);
            }
        });
    }

    document.getElementById('btn-add-reminder').addEventListener('click', function () {
        document.getElementById('reminder-form').reset();
        document.getElementById('reminder-form-errors').classList.add('d-none');
        clearFieldErrors();
        reminderModal.show();
    });

    tbody.addEventListener('click', function (e) {
        const dismissBtn = e.target.closest('.btn-dismiss-reminder');
        if (dismissBtn) {
            fetch(`/app/ledger/reminders/${dismissBtn.dataset.id}/dismiss`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            })
                .then(res => res.json())
                .then(body => {
                    if (body.success) { LedgerToast.success(body.message); loadTable(currentPage); }
                    else { LedgerToast.error(body.message ?? '{{ __('Action failed.') }}'); }
                })
                .catch(() => LedgerToast.error('{{ __('Action failed.') }}'));
            return;
        }

        const deleteBtn = e.target.closest('.btn-delete-reminder');
        if (deleteBtn) {
            deleteId = deleteBtn.dataset.id;
            document.getElementById('delete-reminder-name').textContent = deleteBtn.dataset.name;
            deleteModal.show();
        }
    });

    document.getElementById('confirm-delete-reminder-btn').addEventListener('click', function () {
        if (!deleteId) return;
        fetch(`/app/ledger/reminders/${deleteId}`, {
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

    document.getElementById('reminder-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const payload = {
            title: document.getElementById('reminder-title').value,
            customer_id: document.getElementById('reminder-customer').value || null,
            supplier_id: document.getElementById('reminder-supplier').value || null,
            due_date: document.getElementById('reminder-due-date').value,
            amount: document.getElementById('reminder-amount').value || null,
            channel: document.getElementById('reminder-channel').value,
        };

        const saveBtn = document.getElementById('reminder-save-btn');
        const spinner = document.getElementById('reminder-save-spinner');
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');

        fetch('{{ route("ledger.reminders.store") }}', {
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
                if (status === 422) { showFieldErrors(body.errors ?? {}); return; }
                if (body.success) {
                    reminderModal.hide();
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