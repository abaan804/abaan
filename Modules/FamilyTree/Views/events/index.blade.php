@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Life Events') . ' — ' . $family->name)
@section('ft-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-4">
                <select id="event-member-filter"
                        class="form-select ft-member-select"
                        data-placeholder="{{ __('All Members') }}">
                    <option value=""></option>
                    @foreach ($members as $m)
                        <option value="{{ $m->id }}">{{ $m->full_name }} - {{ $m->father?->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select id="event-type-filter" class="form-select">
                    <option value="">{{ __('All Event Types') }}</option>
                    @foreach (\Modules\FamilyTree\Models\FtEvent::TYPE_LABELS as $key => $label)
                        <option value="{{ $key }}">{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                @can('familytree.manage-events')
                    <button type="button" class="btn btn-primary w-100" id="btn-add-event">
                        <i class="bi bi-calendar-plus"></i> {{ __('Add Event') }}
                    </button>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ft-table">
            <thead>
                <tr>
                    <th>{{ __('Member') }}</th>
                    <th>{{ __('Event') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Media') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="events-table-body">
                <tr><td colspan="6" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@can('familytree.manage-events')
{{-- Add/Edit Event Modal --}}
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="event-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" id="event-id">
                <div class="modal-header" style="background:var(--ft-primary);color:#fff;">
                    <h5 class="modal-title" id="eventModalTitle">{{ __('Add Event') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="event-form-errors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Member') }} <span class="text-danger">*</span></label>
                            <select name="member_id" id="ev-member"
                                    class="form-select ft-member-select"
                                    data-placeholder="{{ __('— Select Member —') }}" required>
                                <option value=""></option>
                                @foreach ($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->full_name }}-{{ $m->father?->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Event Type') }} <span class="text-danger">*</span></label>
                            <select name="event_type" id="ev-type" class="form-select" required>
                                @foreach (\Modules\FamilyTree\Models\FtEvent::TYPE_LABELS as $key => $label)
                                    <option value="{{ $key }}">{{ __($label) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12" id="ev-custom-title-field" style="display:none;">
                            <label class="form-label">{{ __('Event Title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="event_title" id="ev-title" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Event Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" id="ev-date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Location') }}</label>
                            <input type="text" name="location" id="ev-location" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" id="ev-description" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="ev-status" class="form-select">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-12" id="ev-media-field">
                            <label class="form-label">{{ __('Media (Photos/Documents)') }}</label>
                            <input type="file" name="media[]" id="ev-media"
                                   class="form-control" multiple accept="image/*,.pdf,.doc,.docx">
                            <div class="form-text">{{ __('Max 10MB per file. Images and documents accepted.') }}</div>
                            <div id="ev-existing-media" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="event-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="event-spinner"></span>
                        {{ __('Save Event') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteEventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">{{ __('Delete Event') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete') }} <strong id="delete-event-name"></strong>?</p>
                <p class="small text-danger">{{ __('All attached media will also be deleted.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-event-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
(function () {
    const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl    = '{{ route("familytree.family.events.table", $family) }}';
    const storeUrl    = '{{ route("familytree.family.events.store", $family) }}';
    const tbody       = document.getElementById('events-table-body');
    const memberFilter = document.getElementById('event-member-filter');
    const typeFilter   = document.getElementById('event-type-filter');

    const eventModal  = new bootstrap.Modal(document.getElementById('eventModal'));
    FtSelect2.onModal(document.getElementById('eventModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
    let deleteId = null, currentPage = 1;

$(document).ready(function () {
    FtSelect2.init(document.querySelector('.card.shadow-sm.border-0.mb-3'));
});

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div></td></tr>`;
        const p = new URLSearchParams();
        if (memberFilter.value) p.set('member_id', memberFilter.value);
        if (typeFilter.value)   p.set('event_type', typeFilter.value);
        p.set('page', page);
        fetch(`${tableUrl}?${p.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => { tbody.innerHTML = html; });
    }

    tbody.addEventListener('click', e => {
        const link = e.target.closest('#events-pagination a');
        if (link) { e.preventDefault(); loadTable(new URL(link.href).searchParams.get('page') || 1); }
    });

    [memberFilter, typeFilter].forEach(el => el.addEventListener('change', () => loadTable(1)));

    // Custom title field toggle
    document.getElementById('ev-type')?.addEventListener('change', function () {
        document.getElementById('ev-custom-title-field').style.display =
            this.value === 'custom' ? '' : 'none';
    });

    function resetEventForm() {
        document.getElementById('event-form').reset();
        document.getElementById('event-id').value = '';
        document.getElementById('ev-custom-title-field').style.display = 'none';
        document.getElementById('event-form-errors').classList.add('d-none');
        document.getElementById('ev-existing-media').innerHTML = '';
        document.getElementById('ev-media-field').style.display = '';
    }

    document.getElementById('btn-add-event')?.addEventListener('click', () => {
        resetEventForm();
        document.getElementById('eventModalTitle').textContent = '{{ __('Add Event') }}';
        eventModal.show();
    });

    tbody.addEventListener('click', e => {
        const editBtn = e.target.closest('.btn-edit-event');
        if (editBtn) {
            fetch(`/app/family-tree/{{ $family->id }}/events/${editBtn.dataset.id}/json`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json()).then(({ data: ev }) => {
                resetEventForm();
                document.getElementById('event-id').value       = ev.id;
                $('#ev-member').val(ev.member_id ?? '').trigger('change');
                document.getElementById('ev-type').value        = ev.event_type ?? 'custom';
                document.getElementById('ev-title').value       = ev.event_title ?? '';
                document.getElementById('ev-date').value        = ev.event_date ?? '';
                document.getElementById('ev-location').value    = ev.location ?? '';
                document.getElementById('ev-description').value = ev.description ?? '';
                document.getElementById('ev-status').value      = ev.status ?? 'active';
                document.getElementById('ev-custom-title-field').style.display =
                    ev.event_type === 'custom' ? '' : 'none';
                document.getElementById('ev-media-field').style.display = 'none';

                // Existing media chips
                const wrap = document.getElementById('ev-existing-media');
                wrap.innerHTML = '';
                (ev.media || []).forEach(m => {
                    const chip = document.createElement('span');
                    chip.className = 'badge bg-light text-dark border d-inline-flex align-items-center gap-1';
                    chip.innerHTML = `<a href="/storage/${m.file_path}" target="_blank" class="text-decoration-none small">${m.original_name}</a>
                        <button type="button" class="btn-close" style="font-size:.5rem;" data-media-id="${m.id}"></button>`;
                    wrap.appendChild(chip);
                });
                wrap.addEventListener('click', function(e) {
                    const btn = e.target.closest('[data-media-id]');
                    if (!btn) return;
                    fetch(`/app/family-tree/{{ $family->id }}/event-media/${btn.dataset.mediaId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(r => r.json()).then(b => { if (b.success) btn.closest('span').remove(); });
                });

                document.getElementById('eventModalTitle').textContent = '{{ __('Edit Event') }}';
                eventModal.show();
            });
        }

        const deleteBtn = e.target.closest('.btn-delete-event');
        if (deleteBtn) {
            deleteId = deleteBtn.dataset.id;
            document.getElementById('delete-event-name').textContent = deleteBtn.dataset.name;
            deleteModal.show();
        }
    });

    document.getElementById('confirm-delete-event-btn')?.addEventListener('click', () => {
        if (!deleteId) return;
        fetch(`/app/family-tree/{{ $family->id }}/events/${deleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json().then(b => ({ ok: r.ok, b })))
        .then(({ ok, b }) => {
            deleteModal.hide();
            if (ok && b.success) { FtToast.success(b.message); loadTable(currentPage); }
            else FtToast.error(b.message ?? '{{ __('Delete failed.') }}');
        });
    });

    document.getElementById('event-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const id  = document.getElementById('event-id').value;
        const url = id ? `/app/family-tree/{{ $family->id }}/events/${id}` : storeUrl;
        const fd  = new FormData(this);
        if (id) fd.append('_method', 'PUT');

        const btn = document.getElementById('event-save-btn');
        const sp  = document.getElementById('event-spinner');
        btn.disabled = true; sp.classList.remove('d-none');

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: fd,
        })
        .then(r => r.json().then(b => ({ status: r.status, b })))
        .then(({ status, b }) => {
            btn.disabled = false; sp.classList.add('d-none');
            if (status === 422) {
                const eb = document.getElementById('event-form-errors');
                eb.textContent = Object.values(b.errors ?? {}).flat().join(' ');
                eb.classList.remove('d-none');
                return;
            }
            if (b.success) { eventModal.hide(); FtToast.success(b.message); loadTable(currentPage); }
        })
        .catch(() => { btn.disabled = false; sp.classList.add('d-none'); });
    });

    loadTable(1);
})();
</script>
@endpush
@endsection