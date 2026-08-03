@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Payments'))
@section('masjid-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-2">
                <input type="text" id="pay-search" class="form-control" placeholder="{{ __('Receipt / reference / member') }}">
            </div>
            <div class="col-6 col-md-2">
                <select id="pay-season-filter" class="form-select">
                    <option value="">{{ __('All Seasons') }}</option>
                    @foreach ($seasons as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="pay-method-filter" class="form-select">
                    <option value="">{{ __('All Methods') }}</option>
                    <option value="cash">{{ __('Cash') }}</option>
                    <option value="bank">{{ __('Bank') }}</option>
                    <option value="online">{{ __('Online') }}</option>
                    <option value="cheque">{{ __('Cheque') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="pay-date-from" class="form-control" placeholder="{{ __('From') }}">
            </div>
            <div class="col-6 col-md-1">
                <input type="date" id="pay-date-to" class="form-control" placeholder="{{ __('To') }}">
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex gap-2">
                    @can('masjid.manage-payments')
                        <button type="button" class="btn btn-primary flex-grow-1" id="btn-add-payment">
                            <i class="bi bi-cash-coin"></i> {{ __('Record') }}
                        </button>
                    @endcan
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="{{ __('Export') }}">
                            <i class="bi bi-download"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" id="export-payments-csv">
                                <i class="bi bi-filetype-csv"></i> {{ __('Export CSV') }}
                            </a></li>
                            <li><a class="dropdown-item" href="#" id="export-payments-xlsx">
                                <i class="bi bi-file-earmark-excel"></i> {{ __('Export Excel') }}
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Totals bar --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-4">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Filtered Total') }}</div>
            <div class="h5 mb-0 text-success" id="pay-total">—</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Records') }}</div>
            <div class="h5 mb-0" id="pay-count">—</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Today') }}</div>
            <div class="h5 mb-0 text-success">{{ formatCurrency($mosque->payments()->whereDate('payment_date', today())->sum('amount_paid')) }}</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle mj-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Receipt') }}</th>
                    <th>{{ __('Member') }}</th>
                    <th>{{ __('Season') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Method') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="payments-table-body">
                <tr><td colspan="7" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Add/Edit Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="payment-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" id="payment-id">
                <input type="hidden" id="payment-method-spoof">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalTitle">{{ __('Record Payment') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="payment-form-errors" class="alert alert-danger d-none"></div>

                    {{-- Season member info card — shows due/paid/balance when member+season selected --}}
                    <div id="season-member-info" class="alert alert-light border mb-3 d-none">
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="small text-muted">{{ __('Due') }}</div>
                                <div class="fw-bold" id="smi-due">—</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">{{ __('Paid') }}</div>
                                <div class="fw-bold text-success" id="smi-paid">—</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">{{ __('Remaining') }}</div>
                                <div class="fw-bold text-danger" id="smi-balance">—</div>
                            </div>
                        </div>
                        <input type="hidden" id="season-member-id-field" name="season_member_id">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Member') }} <span class="text-danger">*</span></label>
                            <select name="member_id" id="payment-member" class="form-select" required>
                                <option value="">{{ __('— Select Member —') }}</option>
                                @foreach ($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->mobile }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Season') }} <span class="text-danger">*</span></label>
                            <select name="season_id" id="payment-season" class="form-select" required>
                                <option value="">{{ __('— Select Season —') }}</option>
                                @foreach ($seasons as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" name="amount_paid" id="payment-amount" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment-date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Payment Method') }}</label>
                            <select name="payment_method" id="payment-method" class="form-select">
                                <option value="cash">{{ __('Cash') }}</option>
                                <option value="bank">{{ __('Bank') }}</option>
                                <option value="online">{{ __('Online') }}</option>
                                <option value="cheque">{{ __('Cheque') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Reference No.') }}</label>
                            <input type="text" name="reference_no" id="payment-reference" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" id="payment-notes" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12" id="field-attachments">
                            <label class="form-label">{{ __('Attachments') }}</label>
                            <input type="file" name="attachments[]" id="payment-attachments" class="form-control" multiple>
                            <div id="existing-attachments" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="payment-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="payment-spinner"></span>
                        {{ __('Save Payment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deletePaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete this payment of') }} <strong id="delete-payment-amount"></strong>?</p>
                <p class="text-danger small">{{ __('This will update the member\'s balance and season status immediately.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-payment-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="pay-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080;"></div>

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl = '{{ route("masjid.mosque.payments.table", $mosque) }}';
    const smInfoUrl = '{{ route("masjid.mosque.payments.season-member-info", $mosque) }}';
    const tbody = document.getElementById('payments-table-body');

    const filters = {
        search: document.getElementById('pay-search'),
        season: document.getElementById('pay-season-filter'),
        method: document.getElementById('pay-method-filter'),
        dateFrom: document.getElementById('pay-date-from'),
        dateTo: document.getElementById('pay-date-to'),
    };

    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deletePaymentModal'));
    let deleteId = null;
    let searchDebounce = null;
    let currentPage = 1;

    function toast(msg, type = 'success') {
        const container = document.getElementById('pay-toast-container');
        const el = document.createElement('div');
        el.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        container.appendChild(el);
        new bootstrap.Toast(el, { delay: 4000 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    function buildUrl(page = 1) {
        const p = new URLSearchParams();
        if (filters.search.value) p.set('search', filters.search.value);
        if (filters.season.value) p.set('season_id', filters.season.value);
        if (filters.method.value) p.set('payment_method', filters.method.value);
        if (filters.dateFrom.value) p.set('date_from', filters.dateFrom.value);
        if (filters.dateTo.value) p.set('date_to', filters.dateTo.value);
        p.set('page', page);
        return `${tableUrl}?${p.toString()}`;
    }

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div></td></tr>`;
        fetch(buildUrl(page), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                tbody.innerHTML = html;
                const dataEl = document.getElementById('pay-totals-data');
                if (dataEl) {
                    document.getElementById('pay-total').textContent = dataEl.dataset.total;
                    document.getElementById('pay-count').textContent = dataEl.dataset.count;
                }
            });
    }

    tbody.addEventListener('click', e => {
        const link = e.target.closest('#payments-pagination a');
        if (link) { e.preventDefault(); loadTable(new URL(link.href).searchParams.get('page') || 1); }
    });

    filters.search.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => loadTable(1), 400);
    });
    [filters.season, filters.method, filters.dateFrom, filters.dateTo].forEach(el =>
        el.addEventListener('change', () => loadTable(1))
    );

    // Season Member Info — fetch when member+season both selected
    function fetchSeasonMemberInfo() {
        const memberId = document.getElementById('payment-member').value;
        const seasonId = document.getElementById('payment-season').value;
        const infoCard = document.getElementById('season-member-info');

        if (!memberId || !seasonId) { infoCard.classList.add('d-none'); return; }

        fetch(`${smInfoUrl}?member_id=${memberId}&season_id=${seasonId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (!data.found) {
                    infoCard.classList.add('d-none');
                    document.getElementById('season-member-id-field').value = '';
                    toast('{{ __('This member is not assigned to the selected season.') }}', 'error');
                    return;
                }
                document.getElementById('season-member-id-field').value = data.season_member_id;
                document.getElementById('smi-due').textContent = data.amount_due;
                document.getElementById('smi-paid').textContent = data.amount_paid;
                document.getElementById('smi-balance').textContent = data.balance;
                // Pre-fill amount with remaining balance if positive
                if (parseFloat(data.balance) > 0) {
                    document.getElementById('payment-amount').value = Math.round(parseFloat(data.balance));
                }
                infoCard.classList.remove('d-none');
            });
    }

    ['payment-member', 'payment-season'].forEach(id =>
        document.getElementById(id).addEventListener('change', fetchSeasonMemberInfo)
    );

    function clearErrors() {
        document.querySelectorAll('#payment-form .is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('#payment-form .invalid-feedback').forEach(el => el.remove());
    }

    function showErrors(errors) {
        clearErrors();
        Object.entries(errors).forEach(([field, msgs]) => {
            const key = field.replace(/_/g, '-');
            const input = document.getElementById(`payment-${key}`) ?? document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const fb = document.createElement('div');
                fb.className = 'invalid-feedback';
                fb.textContent = msgs[0];
                input.insertAdjacentElement('afterend', fb);
            }
        });
    }

    function resetForm() {
        document.getElementById('payment-form').reset();
        document.getElementById('payment-id').value = '';
        document.getElementById('payment-method-spoof').value = '';
        document.getElementById('payment-form-errors').classList.add('d-none');
        document.getElementById('season-member-info').classList.add('d-none');
        document.getElementById('season-member-id-field').value = '';
        document.getElementById('existing-attachments').innerHTML = '';
        document.getElementById('field-attachments').style.display = '';
        document.getElementById('payment-date').value = new Date().toISOString().slice(0, 10);
        clearErrors();
    }

    document.getElementById('btn-add-payment')?.addEventListener('click', () => {
        resetForm();
        document.getElementById('paymentModalTitle').textContent = '{{ __('Record Payment') }}';
        paymentModal.show();

        // Pre-fill from query string if deep-linked from member page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('member_id')) {
            document.getElementById('payment-member').value = urlParams.get('member_id');
        }
        if (urlParams.get('season_id')) {
            document.getElementById('payment-season').value = urlParams.get('season_id');
        }
        if (urlParams.get('member_id') && urlParams.get('season_id')) {
            fetchSeasonMemberInfo();
        }
    });

    tbody.addEventListener('click', e => {
        const editBtn = e.target.closest('.btn-edit-payment');
        if (editBtn) {
            fetch(`/app/masjid/{{ $mosque->id }}/payments/${editBtn.dataset.id}/json`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(({ data: p }) => {
                    resetForm();
                    document.getElementById('payment-id').value = p.id;
                    document.getElementById('payment-method-spoof').value = 'PUT';
                    document.getElementById('payment-member').value = p.member_id ?? '';
                    document.getElementById('payment-season').value = p.season_id ?? '';
                    document.getElementById('season-member-id-field').value = p.season_member_id ?? '';
                    document.getElementById('payment-amount').value = p.amount_paid ?? '';
                    document.getElementById('payment-date').value = p.payment_date ?? '';
                    document.getElementById('payment-method').value = p.payment_method ?? 'cash';
                    document.getElementById('payment-reference').value = p.reference_no ?? '';
                    document.getElementById('payment-notes').value = p.notes ?? '';
                    document.getElementById('paymentModalTitle').textContent = '{{ __('Edit Payment') }}';
                    document.getElementById('field-attachments').style.display = 'none';

                    const wrap = document.getElementById('existing-attachments');
                    wrap.innerHTML = '';
                    (p.attachments || []).forEach(att => {
                        const chip = document.createElement('span');
                        chip.className = 'badge bg-light text-dark border d-inline-flex align-items-center gap-1';
                        chip.innerHTML = `<a href="/storage/${att.file_path}" target="_blank" class="text-decoration-none">${att.original_name}</a>
                            <button type="button" class="btn-close" style="font-size:.55rem;" data-att-id="${att.id}"></button>`;
                        wrap.appendChild(chip);
                    });
                    paymentModal.show();
                });
        }

        const deleteBtn = e.target.closest('.btn-delete-payment');
        if (deleteBtn) {
            deleteId = deleteBtn.dataset.id;
            document.getElementById('delete-payment-amount').textContent = deleteBtn.dataset.amount;
            deleteModal.show();
        }
    });

    // Remove attachment
    document.getElementById('existing-attachments').addEventListener('click', e => {
        const btn = e.target.closest('[data-att-id]');
        if (!btn) return;
        fetch(`/app/masjid/{{ $mosque->id }}/payment-attachments/${btn.dataset.attId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(b => { if (b.success) { btn.closest('span').remove(); toast(b.message); } });
    });

    document.getElementById('confirm-delete-payment-btn').addEventListener('click', () => {
        if (!deleteId) return;
        fetch(`/app/masjid/{{ $mosque->id }}/payments/${deleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json().then(b => ({ ok: r.ok, b })))
            .then(({ ok, b }) => {
                deleteModal.hide();
                if (ok && b.success) { toast(b.message); loadTable(currentPage); }
                else toast(b.message ?? '{{ __('Delete failed.') }}', 'error');
            });
    });

    document.getElementById('payment-form').addEventListener('submit', e => {
        e.preventDefault();
        const id = document.getElementById('payment-id').value;
        const url = id ? `/app/masjid/{{ $mosque->id }}/payments/${id}` : `{{ route('masjid.mosque.payments.store', $mosque) }}`;
        const formData = new FormData(document.getElementById('payment-form'));
        if (id) formData.append('_method', 'PUT');

        const saveBtn = document.getElementById('payment-save-btn');
        const spinner = document.getElementById('payment-spinner');
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
                if (b.success) {
                    paymentModal.hide();
                    toast(b.message);
                    loadTable(currentPage);
                } else {
                    document.getElementById('payment-form-errors').textContent = b.message;
                    document.getElementById('payment-form-errors').classList.remove('d-none');
                }
            })
            .catch(() => { saveBtn.disabled = false; spinner.classList.add('d-none'); });
    });

    function paymentExportUrl(format) {
        const p = new URLSearchParams();
        if (filters.search.value) p.set('search', filters.search.value);
        if (filters.season.value) p.set('season_id', filters.season.value);
        if (filters.method.value) p.set('payment_method', filters.method.value);
        if (filters.dateFrom.value) p.set('date_from', filters.dateFrom.value);
        if (filters.dateTo.value) p.set('date_to', filters.dateTo.value);
        return `/app/masjid/{{ $mosque->id }}/payments/export/${format}?${p.toString()}`;
    }
    document.getElementById('export-payments-csv')?.addEventListener('click', e => {
        e.preventDefault(); window.location.href = paymentExportUrl('csv');
    });
    document.getElementById('export-payments-xlsx')?.addEventListener('click', e => {
        e.preventDefault(); window.location.href = paymentExportUrl('xlsx');
    });

    loadTable(1);
})();
</script>
@endpush
@endsection