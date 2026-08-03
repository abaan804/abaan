@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Seasons'))
@section('masjid-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-3">
                <select id="season-status-filter" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                    <option value="completed">{{ __('Completed') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select id="season-frequency-filter" class="form-select">
                    <option value="">{{ __('All Frequencies') }}</option>
                    <option value="monthly">{{ __('Monthly') }}</option>
                    <option value="quarterly">{{ __('Quarterly') }}</option>
                    <option value="seasonal">{{ __('Seasonal') }}</option>
                    <option value="yearly">{{ __('Yearly') }}</option>
                    <option value="custom">{{ __('Custom') }}</option>
                </select>
            </div>
            <div class="col-12 col-md-4 offset-md-2">
                @can('masjid.manage-seasons')
                    <button type="button" class="btn btn-primary w-100" id="btn-add-season">
                        <i class="bi bi-calendar-plus"></i> {{ __('New Season') }}
                    </button>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle mj-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Season') }}</th>
                    <th>{{ __('Period') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="seasons-table-body">
                <tr><td colspan="5" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="seasonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="season-form" novalidate>
                <input type="hidden" id="season-id">
                <div class="modal-header">
                    <h5 class="modal-title" id="seasonModalTitle">{{ __('New Season') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="season-form-errors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Season Name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="season-name" class="form-control" required placeholder="{{ __('e.g. Ramadan 2026, Winter Season') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                            <input type="date" id="season-start-date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('End Date') }} <span class="text-danger">*</span></label>
                            <input type="date" id="season-end-date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Contribution Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" id="season-contribution-amount" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Frequency') }}</label>
                            <select id="season-frequency" class="form-select">
                                <option value="seasonal">{{ __('Seasonal') }}</option>
                                <option value="monthly">{{ __('Monthly') }}</option>
                                <option value="quarterly">{{ __('Quarterly') }}</option>
                                <option value="yearly">{{ __('Yearly') }}</option>
                                <option value="custom">{{ __('Custom') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select id="season-status" class="form-select">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch pb-2">
                                <input type="checkbox" id="season-auto-assign" class="form-check-input" checked>
                                <label class="form-check-label" for="season-auto-assign">
                                    {{ __('Auto-assign all active members') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea id="season-description" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="season-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="season-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteSeasonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Season') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete') }} <strong id="delete-season-name"></strong>?</p>
                <p class="text-danger small">{{ __('Seasons with existing payments cannot be deleted — set status to Completed instead.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-season-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl = '{{ route("masjid.mosque.seasons.table", $mosque) }}';
    const tbody = document.getElementById('seasons-table-body');
    const statusFilter = document.getElementById('season-status-filter');
    const freqFilter = document.getElementById('season-frequency-filter');

    const seasonModal = new bootstrap.Modal(document.getElementById('seasonModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteSeasonModal'));
    let deleteId = null;
    let currentPage = 1;

    function buildUrl(page = 1) {
        const p = new URLSearchParams();
        if (statusFilter.value) p.set('status', statusFilter.value);
        if (freqFilter.value) p.set('frequency', freqFilter.value);
        p.set('page', page);
        return `${tableUrl}?${p.toString()}`;
    }

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div></td></tr>`;
        fetch(buildUrl(page), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => { tbody.innerHTML = html; })
            .catch(console.error);
    }

    tbody.addEventListener('click', e => {
        const link = e.target.closest('#seasons-pagination a');
        if (link) { e.preventDefault(); loadTable(new URL(link.href).searchParams.get('page') || 1); }
    });

    [statusFilter, freqFilter].forEach(el => el.addEventListener('change', () => loadTable(1)));

    function resetModal() {
        document.getElementById('season-form').reset();
        document.getElementById('season-id').value = '';
        document.getElementById('season-form-errors').classList.add('d-none');
        document.getElementById('season-auto-assign').checked = true;
    }

    document.getElementById('btn-add-season')?.addEventListener('click', () => {
        resetModal();
        document.getElementById('seasonModalTitle').textContent = '{{ __('New Season') }}';
        seasonModal.show();
    });

    tbody.addEventListener('click', e => {
        const editBtn = e.target.closest('.btn-edit-season');
        if (editBtn) {
            fetch(`/app/masjid/{{ $mosque->id }}/seasons/${editBtn.dataset.id}/json`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json()).then(({ data: s }) => {
                    resetModal();
                    document.getElementById('season-id').value = s.id;
                    document.getElementById('season-name').value = s.name ?? '';
                    document.getElementById('season-start-date').value = s.start_date ?? '';
                    document.getElementById('season-end-date').value = s.end_date ?? '';
                    document.getElementById('season-contribution-amount').value = s.contribution_amount ?? '';
                    document.getElementById('season-frequency').value = s.frequency ?? 'seasonal';
                    document.getElementById('season-status').value = s.status ?? 'active';
                    document.getElementById('season-auto-assign').checked = !!s.auto_assign;
                    document.getElementById('season-description').value = s.description ?? '';
                    document.getElementById('seasonModalTitle').textContent = '{{ __('Edit Season') }}';
                    seasonModal.show();
                });
        }

        const deleteBtn = e.target.closest('.btn-delete-season');
        if (deleteBtn) {
            deleteId = deleteBtn.dataset.id;
            document.getElementById('delete-season-name').textContent = deleteBtn.dataset.name;
            deleteModal.show();
        }
    });

    document.getElementById('confirm-delete-season-btn').addEventListener('click', () => {
        if (!deleteId) return;
        fetch(`/app/masjid/{{ $mosque->id }}/seasons/${deleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json().then(b => ({ ok: r.ok, b })))
            .then(({ ok, b }) => {
                deleteModal.hide();
                if (ok && b.success) loadTable(currentPage);
                else alert(b.message ?? '{{ __('Delete failed.') }}');
            });
    });

    document.getElementById('season-form').addEventListener('submit', e => {
        e.preventDefault();
        const id = document.getElementById('season-id').value;
        const url = id ? `/app/masjid/{{ $mosque->id }}/seasons/${id}` : `{{ route('masjid.mosque.seasons.store', $mosque) }}`;

        const payload = {
            name: document.getElementById('season-name').value,
            start_date: document.getElementById('season-start-date').value,
            end_date: document.getElementById('season-end-date').value,
            contribution_amount: document.getElementById('season-contribution-amount').value,
            frequency: document.getElementById('season-frequency').value,
            status: document.getElementById('season-status').value,
            auto_assign: document.getElementById('season-auto-assign').checked ? 1 : 0,
            description: document.getElementById('season-description').value,
        };
        if (id) payload._method = 'PUT';

        const saveBtn = document.getElementById('season-save-btn');
        const spinner = document.getElementById('season-spinner');
        saveBtn.disabled = true; spinner.classList.remove('d-none');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload)
        })
            .then(r => r.json().then(b => ({ status: r.status, b })))
            .then(({ status, b }) => {
                saveBtn.disabled = false; spinner.classList.add('d-none');
                if (status === 422) {
                    const errBox = document.getElementById('season-form-errors');
                    errBox.textContent = Object.values(b.errors ?? {}).flat().join(' ');
                    errBox.classList.remove('d-none');
                    return;
                }
                if (b.success) { seasonModal.hide(); loadTable(currentPage); }
            })
            .catch(() => { saveBtn.disabled = false; spinner.classList.add('d-none'); });
    });

    loadTable(1);
})();
</script>
@endpush
@endsection