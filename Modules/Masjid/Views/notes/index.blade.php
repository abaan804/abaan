@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Notes'))
@section('masjid-content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">{{ __('Pin important notes to keep them at the top.') }}</div>
    <button type="button" class="btn btn-primary" id="btn-add-note">
        <i class="bi bi-sticky-fill"></i> {{ __('Add Note') }}
    </button>
</div>

{{-- Pinned Notes --}}
@if ($pinnedNotes->isNotEmpty())
    <h6 class="small text-muted fw-bold mb-2">
        <i class="bi bi-pin-angle-fill text-warning"></i> {{ __('PINNED') }}
    </h6>
    <div class="row g-3 mb-4">
        @foreach ($pinnedNotes as $note)
            @include('masjid::notes._card', ['note' => $note])
        @endforeach
    </div>
@endif

{{-- General Notes --}}
<div class="d-flex gap-3 mb-3">
    <button type="button" class="btn btn-sm btn-primary active" id="tab-general-btn"
            onclick="showTab('general')">
        {{ __('General Notes') }} ({{ $generalNotes->total() }})
    </button>
    <button type="button" class="btn btn-sm btn-outline-primary" id="tab-season-btn"
            onclick="showTab('season')">
        {{ __('Season Notes') }} ({{ $seasonNotes->total() }})
    </button>
</div>

<div id="tab-general" class="row g-3">
    @forelse ($generalNotes as $note)
        @include('masjid::notes._card', ['note' => $note])
    @empty
        <div class="col-12 text-center text-muted py-5">
            <i class="bi bi-sticky" style="font-size:2rem;"></i>
            <p class="mt-2">{{ __('No general notes yet.') }}</p>
        </div>
    @endforelse
</div>

<div id="tab-season" class="row g-3 d-none">
    @forelse ($seasonNotes as $note)
        @include('masjid::notes._card', ['note' => $note])
    @empty
        <div class="col-12 text-center text-muted py-5">
            <i class="bi bi-sticky" style="font-size:2rem;"></i>
            <p class="mt-2">{{ __('No season notes yet.') }}</p>
        </div>
    @endforelse
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="note-form" novalidate>
                <input type="hidden" id="note-id">
                <div class="modal-header" style="background:var(--mj-primary);color:#fff;">
                    <h5 class="modal-title" id="noteModalTitle">{{ __('Add Note') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="note-form-errors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Note Type') }}</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="note_type_radio"
                                       id="note-type-general" value="general" checked>
                                <label class="form-check-label" for="note-type-general">
                                    {{ __('General') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="note_type_radio"
                                       id="note-type-season" value="season">
                                <label class="form-check-label" for="note-type-season">
                                    {{ __('Season Note') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div id="note-season-field" class="mb-3 d-none">
                        <label class="form-label">{{ __('Season') }} <span class="text-danger">*</span></label>
                        <select id="note-season" class="form-select">
                            <option value="">{{ __('— Select Season —') }}</option>
                            @foreach ($seasons as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                        <input type="text" id="note-title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Content') }} <span class="text-danger">*</span></label>
                        <textarea id="note-content" rows="5" class="form-control" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Color Label') }}</label>
                            <select id="note-color" class="form-select">
                                <option value="default">{{ __('Default (White)') }}</option>
                                <option value="warning">{{ __('Yellow (Warning)') }}</option>
                                <option value="danger">{{ __('Red (Important)') }}</option>
                                <option value="success">{{ __('Green (Done)') }}</option>
                                <option value="info">{{ __('Blue (Info)') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="note-pinned">
                                <label class="form-check-label" for="note-pinned">
                                    <i class="bi bi-pin-angle text-warning"></i>
                                    {{ __('Pin this note') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="note-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="note-spinner"></span>
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="note-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080;"></div>

@push('scripts')
<script>
(function () {
    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const storeUrl   = '{{ route("masjid.mosque.notes.store", $mosque) }}';
    const noteModal  = new bootstrap.Modal(document.getElementById('noteModal'));

    function toast(msg, type = 'success') {
        const c  = document.getElementById('note-toast-container');
        const el = document.createElement('div');
        el.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        c.appendChild(el);
        new bootstrap.Toast(el, { delay: 4000 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    // ── Tab Toggle ────────────────────────────────────────────────────────────
    window.showTab = function(tab) {
        document.getElementById('tab-general').classList.toggle('d-none', tab !== 'general');
        document.getElementById('tab-season').classList.toggle('d-none', tab !== 'season');
        document.getElementById('tab-general-btn').classList.toggle('active', tab === 'general');
        document.getElementById('tab-general-btn').classList.toggle('btn-primary', tab === 'general');
        document.getElementById('tab-general-btn').classList.toggle('btn-outline-primary', tab !== 'general');
        document.getElementById('tab-season-btn').classList.toggle('active', tab === 'season');
        document.getElementById('tab-season-btn').classList.toggle('btn-primary', tab === 'season');
        document.getElementById('tab-season-btn').classList.toggle('btn-outline-primary', tab !== 'season');
    };

    // ── Note Type Toggle ──────────────────────────────────────────────────────
    document.querySelectorAll('input[name="note_type_radio"]').forEach(r =>
        r.addEventListener('change', () => {
            document.getElementById('note-season-field')
                .classList.toggle('d-none', r.value !== 'season');
        })
    );

    function resetNoteForm() {
        document.getElementById('note-form').reset();
        document.getElementById('note-id').value = '';
        document.getElementById('note-form-errors').classList.add('d-none');
        document.getElementById('note-season-field').classList.add('d-none');
        document.querySelector('input[value="general"]').checked = true;
    }

    document.getElementById('btn-add-note').addEventListener('click', () => {
        resetNoteForm();
        document.getElementById('noteModalTitle').textContent = '{{ __('Add Note') }}';
        noteModal.show();
    });

    // ── Edit / Delete / Pin (delegated to document) ───────────────────────────
    document.addEventListener('click', e => {
        const editBtn = e.target.closest('.btn-edit-note');
        if (editBtn) {
            fetch(`/app/masjid/{{ $mosque->id }}/notes/${editBtn.dataset.id}/json`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(({ data: n }) => {
                resetNoteForm();
                document.getElementById('note-id').value = n.id;
                document.querySelector(`input[value="${n.type}"]`).checked = true;
                document.getElementById('note-season-field')
                    .classList.toggle('d-none', n.type !== 'season');
                document.getElementById('note-season').value  = n.season_id ?? '';
                document.getElementById('note-title').value   = n.title ?? '';
                document.getElementById('note-content').value = n.content ?? '';
                document.getElementById('note-color').value   = n.color ?? 'default';
                document.getElementById('note-pinned').checked = !!n.is_pinned;
                document.getElementById('noteModalTitle').textContent = '{{ __('Edit Note') }}';
                noteModal.show();
            });
        }

        const deleteBtn = e.target.closest('.btn-delete-note');
        if (deleteBtn) {
            if (!confirm('{{ __('Delete this note?') }}')) return;
            fetch(`/app/masjid/{{ $mosque->id }}/notes/${deleteBtn.dataset.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(b => {
                if (b.success) {
                    toast(b.message);
                    deleteBtn.closest('.col-12, .col-md-6').remove();
                }
            });
        }

        const pinBtn = e.target.closest('.btn-pin-note');
        if (pinBtn) {
            fetch(`/app/masjid/{{ $mosque->id }}/notes/${pinBtn.dataset.id}/toggle-pin`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(b => {
                if (b.success) { toast(b.message); location.reload(); }
            });
        }
    });

    // ── Save ──────────────────────────────────────────────────────────────────
    document.getElementById('note-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const id  = document.getElementById('note-id').value;
        const url = id
            ? `/app/masjid/{{ $mosque->id }}/notes/${id}`
            : storeUrl;
        const btn = document.getElementById('note-save-btn');
        const sp  = document.getElementById('note-spinner');
        btn.disabled = true; sp.classList.remove('d-none');

        const type = document.querySelector('input[name="note_type_radio"]:checked').value;
        const payload = {
            type,
            season_id:  type === 'season' ? document.getElementById('note-season').value : null,
            title:      document.getElementById('note-title').value,
            content:    document.getElementById('note-content').value,
            color:      document.getElementById('note-color').value,
            is_pinned:  document.getElementById('note-pinned').checked,
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
                const eb = document.getElementById('note-form-errors');
                eb.textContent = Object.values(b.errors ?? {}).flat().join(' ');
                eb.classList.remove('d-none');
                return;
            }
            if (b.success) { noteModal.hide(); toast(b.message); location.reload(); }
        })
        .catch(() => { btn.disabled = false; sp.classList.add('d-none'); });
    });
})();
</script>
@endpush
@endsection