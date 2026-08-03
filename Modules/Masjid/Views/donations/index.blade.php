@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Donations'))
@section('masjid-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-2">
                <select id="don-type-filter" class="form-select">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="named">{{ __('Named Donor') }}</option>
                    <option value="anonymous">{{ __('Anonymous') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="don-season-filter" class="form-select">
                    <option value="">{{ __('All Seasons') }}</option>
                    @foreach ($seasons as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="text" id="don-search" class="form-control"
                       placeholder="{{ __('Search donor / purpose') }}">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="don-date-from" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" id="don-date-to" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                @can('masjid.manage-payments')
                    <button type="button" class="btn btn-primary w-100" id="btn-add-donation">
                        <i class="bi bi-gift"></i> {{ __('Add Donation') }}
                    </button>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-4">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Total Donations') }}</div>
            <div class="h5 mb-0 text-success" id="don-total">—</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Records') }}</div>
            <div class="h5 mb-0" id="don-count">—</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle mj-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Donor') }}</th>
                    <th>{{ __('Purpose') }}</th>
                    <th>{{ __('Season') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="don-table-body">
                <tr><td colspan="7" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm"
                         style="color:var(--mj-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Add/Edit Modal --}}
@can('masjid.manage-payments')
<div class="modal fade" id="donationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="donation-form" novalidate>
                <input type="hidden" id="donation-id">
                <div class="modal-header" style="background:var(--mj-primary);color:#fff;">
                    <h5 class="modal-title" id="donationModalTitle">{{ __('Add Donation') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="donation-form-errors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Donation Type') }} <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="don_type_radio"
                                           id="type-named" value="named" checked>
                                    <label class="form-check-label" for="type-named">
                                        <i class="bi bi-person-check"></i> {{ __('Named Donor') }}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="don_type_radio"
                                           id="type-anonymous" value="anonymous">
                                    <label class="form-check-label" for="type-anonymous">
                                        <i class="bi bi-incognito"></i> {{ __('Anonymous') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        {{-- Named donor fields --}}
                        <div id="named-fields" class="col-12">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Donor Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="don-donor-name" class="form-control"
                                           placeholder="{{ __('Full name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Mobile') }}</label>
                                    <input type="text" id="don-donor-mobile" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Address') }}</label>
                                    <input type="text" id="don-donor-address" class="form-control">
                                </div>
                            </div>
                        </div>
                        {{-- Anonymous fields --}}
                        <div id="anonymous-fields" class="col-md-6 d-none">
                            <label class="form-label">{{ __('Day / Description') }}</label>
                            <input type="text" id="don-day-description" class="form-control"
                                   placeholder="{{ __('e.g. Friday, Jumma, Eid ul Fitr') }}">
                        </div>
                        {{-- Common fields --}}
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" id="don-amount" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Donation Date') }} <span class="text-danger">*</span></label>
                            <input type="date" id="don-date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Purpose') }}</label>
                            <input type="text" id="don-purpose" class="form-control"
                                   placeholder="{{ __('e.g. Masjid construction, Ramadan expenses') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Season (optional)') }}</label>
                            <select id="don-season" class="form-select">
                                <option value="">{{ __('— Not linked to season —') }}</option>
                                @foreach ($seasons as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Receipt No.') }}</label>
                            <input type="text" id="don-receipt" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea id="don-notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="donation-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="donation-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteDonationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">{{ __('Delete Donation') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete this donation of') }} <strong id="delete-don-amount"></strong>?</p>
                <p class="text-danger small">{{ __('This cannot be undone.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-donation-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endcan

<div id="don-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080;"></div>

@push('scripts')
<script>
(function () {
    const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl    = '{{ route("masjid.mosque.donations.table", $mosque) }}';
    const storeUrl    = '{{ route("masjid.mosque.donations.store", $mosque) }}';
    const tbody       = document.getElementById('don-table-body');
    const donModalEl  = document.getElementById('donationModal');

    const donModal    = donModalEl ? new bootstrap.Modal(donModalEl) : null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteDonationModal'));
    let deleteId = null;
    let searchDebounce = null;
    let currentPage = 1;

    function toast(msg, type = 'success') {
        const c  = document.getElementById('don-toast-container');
        const el = document.createElement('div');
        el.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        c.appendChild(el);
        new bootstrap.Toast(el, { delay: 4000 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    // ── Type Toggle ───────────────────────────────────────────────────────────
    function toggleType(type) {
        const isNamed = type === 'named';
        document.getElementById('named-fields').classList.toggle('d-none', !isNamed);
        document.getElementById('anonymous-fields').classList.toggle('d-none', isNamed);
    }

    document.querySelectorAll('input[name="don_type_radio"]').forEach(r =>
        r.addEventListener('change', () => toggleType(r.value))
    );

    // ── Table Loading ─────────────────────────────────────────────────────────
    function buildUrl(page = 1) {
        const p = new URLSearchParams();
        if (document.getElementById('don-type-filter').value)
            p.set('type', document.getElementById('don-type-filter').value);
        if (document.getElementById('don-season-filter').value)
            p.set('season_id', document.getElementById('don-season-filter').value);
        if (document.getElementById('don-search').value)
            p.set('search', document.getElementById('don-search').value);
        if (document.getElementById('don-date-from').value)
            p.set('date_from', document.getElementById('don-date-from').value);
        if (document.getElementById('don-date-to').value)
            p.set('date_to', document.getElementById('don-date-to').value);
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
                const d = document.getElementById('don-totals-data');
                if (d) {
                    document.getElementById('don-total').textContent = d.dataset.total;
                    document.getElementById('don-count').textContent = d.dataset.count;
                }
            });
    }

    tbody.addEventListener('click', e => {
        const link = e.target.closest('#don-pagination a');
        if (link) { e.preventDefault(); loadTable(new URL(link.href).searchParams.get('page') || 1); }
    });

    document.getElementById('don-search').addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => loadTable(1), 400);
    });
    ['don-type-filter','don-season-filter','don-date-from','don-date-to'].forEach(id =>
        document.getElementById(id)?.addEventListener('change', () => loadTable(1))
    );

    // ── Reset Form ────────────────────────────────────────────────────────────
    function resetForm() {
        document.getElementById('donation-form').reset();
        document.getElementById('donation-id').value = '';
        document.getElementById('donation-form-errors').classList.add('d-none');
        document.getElementById('don-date').value = new Date().toISOString().slice(0, 10);
        document.querySelector('input[value="named"]').checked = true;
        toggleType('named');
    }

    document.getElementById('btn-add-donation')?.addEventListener('click', () => {
        resetForm();
        document.getElementById('donationModalTitle').textContent = '{{ __('Add Donation') }}';
        donModal?.show();
    });

    // ── Edit ──────────────────────────────────────────────────────────────────
    tbody.addEventListener('click', e => {
        const editBtn = e.target.closest('.btn-edit-donation');
        if (editBtn) {
            fetch(`/app/masjid/{{ $mosque->id }}/donations/${editBtn.dataset.id}/json`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(({ data: d }) => {
                resetForm();
                document.getElementById('donation-id').value   = d.id;
                document.querySelector(`input[value="${d.type}"]`).checked = true;
                toggleType(d.type);
                document.getElementById('don-donor-name').value    = d.donor_name ?? '';
                document.getElementById('don-donor-mobile').value  = d.donor_mobile ?? '';
                document.getElementById('don-donor-address').value = d.donor_address ?? '';
                document.getElementById('don-day-description').value = d.day_description ?? '';
                document.getElementById('don-amount').value    = d.amount ?? '';
                document.getElementById('don-date').value      = d.donation_date ?? '';
                document.getElementById('don-purpose').value   = d.purpose ?? '';
                document.getElementById('don-season').value    = d.season_id ?? '';
                document.getElementById('don-receipt').value   = d.receipt_no ?? '';
                document.getElementById('don-notes').value     = d.notes ?? '';
                document.getElementById('donationModalTitle').textContent = '{{ __('Edit Donation') }}';
                donModal?.show();
            });
        }

        const deleteBtn = e.target.closest('.btn-delete-donation');
        if (deleteBtn) {
            deleteId = deleteBtn.dataset.id;
            document.getElementById('delete-don-amount').textContent = deleteBtn.dataset.amount;
            deleteModal.show();
        }
    });

    // ── Delete ────────────────────────────────────────────────────────────────
    document.getElementById('confirm-delete-donation-btn')?.addEventListener('click', () => {
        if (!deleteId) return;
        fetch(`/app/masjid/{{ $mosque->id }}/donations/${deleteId}`, {
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

    // ── Save ──────────────────────────────────────────────────────────────────
    document.getElementById('donation-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const id  = document.getElementById('donation-id').value;
        const url = id
            ? `/app/masjid/{{ $mosque->id }}/donations/${id}`
            : storeUrl;
        const btn = document.getElementById('donation-save-btn');
        const sp  = document.getElementById('donation-spinner');
        btn.disabled = true; sp.classList.remove('d-none');

        const type = document.querySelector('input[name="don_type_radio"]:checked').value;
        const payload = {
            type,
            donor_name:      type === 'named'     ? document.getElementById('don-donor-name').value : null,
            donor_mobile:    type === 'named'     ? document.getElementById('don-donor-mobile').value : null,
            donor_address:   type === 'named'     ? document.getElementById('don-donor-address').value : null,
            day_description: type === 'anonymous' ? document.getElementById('don-day-description').value : null,
            amount:       document.getElementById('don-amount').value,
            donation_date:document.getElementById('don-date').value,
            purpose:      document.getElementById('don-purpose').value,
            season_id:    document.getElementById('don-season').value || null,
            receipt_no:   document.getElementById('don-receipt').value,
            notes:        document.getElementById('don-notes').value,
        };
        if (id) payload._method = 'PUT';

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
        .then(r => r.json().then(b => ({ status: r.status, b })))
        .then(({ status, b }) => {
            btn.disabled = false; sp.classList.add('d-none');
            if (status === 422) {
                const eb = document.getElementById('donation-form-errors');
                eb.textContent = Object.values(b.errors ?? {}).flat().join(' ');
                eb.classList.remove('d-none');
                return;
            }
            if (b.success) { donModal?.hide(); toast(b.message); loadTable(currentPage); }
            else toast(b.message ?? '{{ __('Save failed.') }}', 'error');
        })
        .catch(() => { btn.disabled = false; sp.classList.add('d-none'); });
    });

    loadTable(1);
})();
</script>
@endpush
@endsection