@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Masjid Expenses'))
@section('masjid-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-2">
                <select id="exp-category-filter" class="form-select">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}">{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="exp-season-filter" class="form-select">
                    <option value="">{{ __('All Seasons') }}</option>
                    @foreach ($seasons as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="text" id="exp-search" class="form-control"
                       placeholder="{{ __('Search title / paid to') }}">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="exp-date-from" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="exp-date-to" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                @can('masjid.manage-payments')
                    <button type="button" class="btn btn-primary w-100" id="btn-add-expense">
                        <i class="bi bi-cash-stack"></i> {{ __('Add Expense') }}
                    </button>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-4">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Total Expenses') }}</div>
            <div class="h5 mb-0 text-danger" id="exp-total">—</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Records') }}</div>
            <div class="h5 mb-0" id="exp-count">—</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle mj-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Paid To') }}</th>
                    <th>{{ __('Season') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="exp-table-body">
                <tr><td colspan="7" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@can('masjid.manage-payments')
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="expense-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" id="expense-id">
                <div class="modal-header" style="background:var(--mj-primary);color:#fff;">
                    <h5 class="modal-title" id="expenseModalTitle">{{ __('Add Expense') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="expense-form-errors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                            <select name="category" id="exp-category" class="form-select" required>
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}">{{ __($label) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="exp-title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="amount"
                                   id="exp-amount" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Expense Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" id="exp-date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Paid To') }}</label>
                            <input type="text" name="paid_to" id="exp-paid-to" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Receipt No.') }}</label>
                            <input type="text" name="receipt_no" id="exp-receipt" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Season (optional)') }}</label>
                            <select name="season_id" id="exp-season" class="form-select">
                                <option value="">{{ __('— Not linked to season —') }}</option>
                                @foreach ($seasons as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Attachment') }}</label>
                            <input type="file" name="attachment" id="exp-attachment"
                                   class="form-control" accept="image/*,.pdf">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" id="exp-notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="expense-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="expense-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">{{ __('Delete Expense') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete') }} <strong id="delete-exp-title"></strong>?</p>
                <p class="text-danger small">{{ __('This cannot be undone.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-expense-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endcan

<div id="exp-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080;"></div>

@push('scripts')
<script>
(function () {
    const csrfToken    = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl     = '{{ route("masjid.mosque.expenses.table", $mosque) }}';
    const storeUrl     = '{{ route("masjid.mosque.expenses.store", $mosque) }}';
    const tbody        = document.getElementById('exp-table-body');
    const expModalEl   = document.getElementById('expenseModal');

    const expModal    = expModalEl ? new bootstrap.Modal(expModalEl) : null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteExpenseModal'));
    let deleteId = null;
    let searchDebounce = null;
    let currentPage = 1;

    function toast(msg, type = 'success') {
        const c  = document.getElementById('exp-toast-container');
        const el = document.createElement('div');
        el.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        c.appendChild(el);
        new bootstrap.Toast(el, { delay: 4000 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    function buildUrl(page = 1) {
        const p = new URLSearchParams();
        if (document.getElementById('exp-category-filter').value)
            p.set('category', document.getElementById('exp-category-filter').value);
        if (document.getElementById('exp-season-filter').value)
            p.set('season_id', document.getElementById('exp-season-filter').value);
        if (document.getElementById('exp-search').value)
            p.set('search', document.getElementById('exp-search').value);
        if (document.getElementById('exp-date-from').value)
            p.set('date_from', document.getElementById('exp-date-from').value);
        if (document.getElementById('exp-date-to').value)
            p.set('date_to', document.getElementById('exp-date-to').value);
        p.set('page', page);
        return `${tableUrl}?${p.toString()}`;
    }

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4">
            <div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div></td></tr>`;
        fetch(buildUrl(page), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                tbody.innerHTML = html;
                const d = document.getElementById('exp-totals-data');
                if (d) {
                    document.getElementById('exp-total').textContent = d.dataset.total;
                    document.getElementById('exp-count').textContent = d.dataset.count;
                }
            });
    }

    tbody.addEventListener('click', e => {
        const link = e.target.closest('#exp-pagination a');
        if (link) { e.preventDefault(); loadTable(new URL(link.href).searchParams.get('page') || 1); }
    });

    document.getElementById('exp-search').addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => loadTable(1), 400);
    });
    ['exp-category-filter','exp-season-filter','exp-date-from','exp-date-to'].forEach(id =>
        document.getElementById(id)?.addEventListener('change', () => loadTable(1))
    );

    function resetForm() {
        document.getElementById('expense-form').reset();
        document.getElementById('expense-id').value = '';
        document.getElementById('expense-form-errors').classList.add('d-none');
        document.getElementById('exp-date').value = new Date().toISOString().slice(0, 10);
    }

    document.getElementById('btn-add-expense')?.addEventListener('click', () => {
        resetForm();
        document.getElementById('expenseModalTitle').textContent = '{{ __('Add Expense') }}';
        expModal?.show();
    });

    tbody.addEventListener('click', e => {
        const editBtn = e.target.closest('.btn-edit-expense');
        if (editBtn) {
            fetch(`/app/masjid/{{ $mosque->id }}/expenses/${editBtn.dataset.id}/json`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(({ data: d }) => {
                resetForm();
                document.getElementById('expense-id').value  = d.id;
                document.getElementById('exp-category').value = d.category ?? 'other';
                document.getElementById('exp-title').value    = d.title ?? '';
                document.getElementById('exp-amount').value   = d.amount ?? '';
                document.getElementById('exp-date').value     = d.expense_date ?? '';
                document.getElementById('exp-paid-to').value  = d.paid_to ?? '';
                document.getElementById('exp-receipt').value  = d.receipt_no ?? '';
                document.getElementById('exp-season').value   = d.season_id ?? '';
                document.getElementById('exp-notes').value    = d.notes ?? '';
                document.getElementById('expenseModalTitle').textContent = '{{ __('Edit Expense') }}';
                expModal?.show();
            });
        }

        const deleteBtn = e.target.closest('.btn-delete-expense');
        if (deleteBtn) {
            deleteId = deleteBtn.dataset.id;
            document.getElementById('delete-exp-title').textContent = deleteBtn.dataset.title;
            deleteModal.show();
        }
    });

    document.getElementById('confirm-delete-expense-btn')?.addEventListener('click', () => {
        if (!deleteId) return;
        fetch(`/app/masjid/{{ $mosque->id }}/expenses/${deleteId}`, {
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

    document.getElementById('expense-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const id  = document.getElementById('expense-id').value;
        const url = id
            ? `/app/masjid/{{ $mosque->id }}/expenses/${id}`
            : storeUrl;
        const btn = document.getElementById('expense-save-btn');
        const sp  = document.getElementById('expense-spinner');
        btn.disabled = true; sp.classList.remove('d-none');

        const fd = new FormData(this);
        if (id) fd.append('_method', 'PUT');

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: fd,
        })
        .then(r => r.json().then(b => ({ status: r.status, b })))
        .then(({ status, b }) => {
            btn.disabled = false; sp.classList.add('d-none');
            if (status === 422) {
                const eb = document.getElementById('expense-form-errors');
                eb.textContent = Object.values(b.errors ?? {}).flat().join(' ');
                eb.classList.remove('d-none');
                return;
            }
            if (b.success) { expModal?.hide(); toast(b.message); loadTable(currentPage); }
            else toast(b.message ?? '{{ __('Save failed.') }}', 'error');
        })
        .catch(() => { btn.disabled = false; sp.classList.add('d-none'); });
    });

    loadTable(1);
})();
</script>
@endpush
@endsection