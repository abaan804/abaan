@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Masjid Contribution Manager'))
@section('masjid-content')

<div id="mosque-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080;"></div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Select a Mosque') }}</h5>
    @can('masjid.manage-mosque-profile')
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMosqueModal">
            <i class="bi bi-plus-lg"></i> {{ __('Add Mosque') }}
        </button>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-3" id="mosque-cards-grid">
    @forelse ($mosques as $mosque)
        <div class="col-12 col-md-6 col-lg-4" id="mosque-card-{{ $mosque->id }}">
            <div class="card shadow-sm h-100 border-0" style="border-radius:14px;">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if ($mosque->logo)
                            <img src="{{ asset('storage/' . $mosque->logo) }}" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:rgba(27,107,69,.1);">
                                <i class="bi bi-building" style="color:var(--mj-primary);font-size:1.4rem;"></i>
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $mosque->mosque_name }}</h6>
                            <div class="text-muted small">{{ $mosque->village_name }}</div>
                        </div>
                    </div>

                    @if ($mosque->scholar_name)
                        <div class="small text-muted mb-1">
                            <i class="bi bi-person"></i> {{ $mosque->scholar_name }}
                        </div>
                    @endif
                    <div class="small text-muted mb-3">
                        <i class="bi bi-people"></i> {{ $mosque->members_count }} {{ __('members') }}
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('masjid.mosque.dashboard', [$mosque,'standalone' => 1]) }}"
                           class="btn flex-grow-1" style="background:var(--mj-primary);color:#fff;">
                            {{ __('Open Dashboard') }}
                        </a>
                        @can('masjid.manage-mosque-profile')
                            <a href="{{ route('masjid.mosque.profile.edit', [$mosque,'standalone' => 1]) }}"
                               class="btn btn-outline-secondary"  title="{{ __('Edit Profile') }}" target="_blank">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-outline-danger btn-delete-mosque"
                                    data-id="{{ $mosque->id }}"
                                    data-name="{{ $mosque->mosque_name }}"
                                    data-members="{{ $mosque->members_count }}"
                                    title="{{ __('Delete Mosque') }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            @include('masjid::partials.empty-state', [
                'icon' => 'bi-building',
                'title' => __('No mosques added yet'),
                'description' => __('Add your first mosque to start managing contributions.'),
            ])
        </div>
    @endforelse
</div>

{{-- Delete Mosque Modal --}}
@can('masjid.manage-mosque-profile')
<div class="modal fade" id="deleteMosqueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle"></i> {{ __('Delete Mosque') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('You are about to delete') }} <strong id="delete-mosque-name"></strong>.</p>
                <div id="delete-mosque-has-members" class="alert alert-danger d-none">
                    <i class="bi bi-people"></i>
                    {{ __('This mosque has members. You must remove all members before deleting the mosque, or set it to Inactive instead.') }}
                </div>
                <div id="delete-mosque-confirm-block">
                    <p class="text-danger small mb-2">
                        {{ __('This will permanently delete the mosque profile. All associated data (members, seasons, payments) must be removed first.') }}
                    </p>
                    <label class="form-label small fw-semibold">
                        {{ __('Type the mosque name to confirm:') }}
                    </label>
                    <input type="text" id="delete-mosque-confirm-input" class="form-control"
                           placeholder="{{ __('Type mosque name here') }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-mosque-btn">
                    <span class="spinner-border spinner-border-sm d-none" id="delete-mosque-spinner"></span>
                    {{ __('Delete Mosque') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endcan

{{-- Add Mosque Modal --}}
@can('masjid.manage-mosque-profile')
<div class="modal fade" id="addMosqueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="mosque-form" enctype="multipart/form-data" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add Mosque') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="mosque-form-errors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Village Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="village_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Mosque Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="mosque_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Scholar Name') }}</label>
                            <input type="text" name="scholar_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Scholar Contact') }}</label>
                            <input type="text" name="scholar_contact" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Committee Name') }}</label>
                            <input type="text" name="committee_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Mosque Contact') }}</label>
                            <input type="text" name="mosque_contact" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('City') }}</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Province') }}</label>
                            <input type="text" name="province" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Country') }}</label>
                            <input type="text" name="country" class="form-control" value="Pakistan">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Address') }}</label>
                            <textarea name="address" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Logo') }}</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="mosque-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="mosque-spinner"></span>
                        {{ __('Create Mosque') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';

    function toast(msg, type = 'success') {
        const container = document.getElementById('mosque-toast-container');
        const el = document.createElement('div');
        el.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        container.appendChild(el);
        new bootstrap.Toast(el, { delay: 5000 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    // ── Add Mosque ────────────────────────────────────────────────────────
    const addMosqueModal = document.getElementById('addMosqueModal')
        ? new bootstrap.Modal(document.getElementById('addMosqueModal'))
        : null;

    document.getElementById('mosque-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const saveBtn = document.getElementById('mosque-save-btn');
        const spinner = document.getElementById('mosque-spinner');
        const errorBox = document.getElementById('mosque-form-errors');

        saveBtn.disabled = true;
        spinner.classList.remove('d-none');
        errorBox.classList.add('d-none');

        fetch('{{ route("masjid.mosques.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: new FormData(this),
        })
            .then(r => r.json().then(b => ({ status: r.status, b })))
            .then(({ status, b }) => {
                saveBtn.disabled = false;
                spinner.classList.add('d-none');
                if (status === 422) {
                    errorBox.textContent = Object.values(b.errors ?? {}).flat().join(' ');
                    errorBox.classList.remove('d-none');
                    return;
                }
                if (b.success) {
                    addMosqueModal?.hide();
                    toast(b.message);
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(() => {
                saveBtn.disabled = false;
                spinner.classList.add('d-none');
            });
    });

    // ── Delete Mosque ─────────────────────────────────────────────────────
    const deleteMosqueModal = document.getElementById('deleteMosqueModal')
        ? new bootstrap.Modal(document.getElementById('deleteMosqueModal'))
        : null;

    let deleteId = null;
    let deleteName = null;

    document.getElementById('mosque-cards-grid')?.addEventListener('click', e => {
        const btn = e.target.closest('.btn-delete-mosque');
        if (!btn) return;

        deleteId = btn.dataset.id;
        deleteName = btn.dataset.name;
        const memberCount = parseInt(btn.dataset.members ?? 0);

        document.getElementById('delete-mosque-name').textContent = deleteName;
        document.getElementById('delete-mosque-confirm-input').value = '';

        const hasMembers = document.getElementById('delete-mosque-has-members');
        const confirmBlock = document.getElementById('delete-mosque-confirm-block');
        const confirmBtn = document.getElementById('confirm-delete-mosque-btn');

        if (memberCount > 0) {
            hasMembers.classList.remove('d-none');
            confirmBlock.classList.add('d-none');
            confirmBtn.disabled = true;
        } else {
            hasMembers.classList.add('d-none');
            confirmBlock.classList.remove('d-none');
            confirmBtn.disabled = false;
        }

        deleteMosqueModal?.show();
    });

    document.getElementById('delete-mosque-confirm-input')?.addEventListener('input', function () {
        const confirmBtn = document.getElementById('confirm-delete-mosque-btn');
        confirmBtn.disabled = this.value.trim() !== deleteName;
    });

    document.getElementById('confirm-delete-mosque-btn')?.addEventListener('click', function () {
        if (!deleteId) return;

        const confirmVal = document.getElementById('delete-mosque-confirm-input').value.trim();
        if (confirmVal !== deleteName) {
            toast('{{ __('Mosque name does not match.') }}', 'error');
            return;
        }

        const spinner = document.getElementById('delete-mosque-spinner');
        this.disabled = true;
        spinner.classList.remove('d-none');

        fetch(`/app/masjid/mosques/${deleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then(r => r.json().then(b => ({ ok: r.ok, b })))
            .then(({ ok, b }) => {
                this.disabled = false;
                spinner.classList.add('d-none');
                deleteMosqueModal?.hide();

                if (ok && b.success) {
                    toast(b.message);
                    document.getElementById(`mosque-card-${deleteId}`)?.remove();
                } else {
                    toast(b.message ?? '{{ __('Delete failed.') }}', 'error');
                }
            })
            .catch(() => {
                this.disabled = false;
                spinner.classList.add('d-none');
                toast('{{ __('Request failed.') }}', 'error');
            });
    });
})();
</script>
@endpush
@endsection