@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Backups'))
@section('masjid-content')

<div id="backup-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080;"></div>

{{-- Create Backup --}}
<div class="row g-4 mb-4">
    <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong><i class="bi bi-building"></i> {{ __('Mosque Backup') }}</strong>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    {{ __('Creates a backup of this mosque only: profile, members, seasons, payments and settings. Stored on the server and available to download anytime.') }}
                </p>
                <button type="button" class="btn btn-primary w-100" id="btn-backup-mosque">
                    <i class="bi bi-database-add"></i> {{ __('Create Mosque Backup') }}
                </button>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong><i class="bi bi-buildings"></i> {{ __('Full Module Backup') }}</strong>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    {{ __('Creates a backup of ALL mosques in your account. Use this for full account-level disaster recovery.') }}
                </p>
                <button type="button" class="btn btn-outline-primary w-100" id="btn-backup-company">
                    <i class="bi bi-database-add"></i> {{ __('Create Full Module Backup') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Upload & Restore --}}
<div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
    <div class="card-header bg-white border-0">
        <strong><i class="bi bi-upload"></i> {{ __('Upload & Restore') }}</strong>
    </div>
    <div class="card-body">
        <div class="alert alert-warning d-flex gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div class="small">
                <strong>{{ __('Warning:') }}</strong>
                {{ __('Restoring a backup will permanently replace the current mosque data. All existing members, seasons, and payments for this mosque will be deleted and replaced with the backup content. This action cannot be undone.') }}
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-5">
                <label class="form-label">{{ __('Select Backup File (.json)') }}</label>
                <input type="file" id="upload-backup-file" class="form-control" accept=".json">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">
                    {{ __('Type mosque name to confirm') }}
                    <span class="text-danger">*</span>
                    <small class="text-muted">({{ $mosque->mosque_name }})</small>
                </label>
                <input type="text" id="upload-confirm-name" class="form-control" placeholder="{{ $mosque->mosque_name }}">
            </div>
            <div class="col-12 col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-danger w-100" id="btn-upload-restore">
                    <i class="bi bi-arrow-counterclockwise"></i> {{ __('Upload & Restore') }}
                </button>
            </div>
        </div>

        <div id="restore-result" class="mt-3"></div>
    </div>
</div>

{{-- Stored Backups List --}}
<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-clock-history"></i> {{ __('Stored Backups') }}</strong>
        <span class="badge bg-light text-dark border">{{ $backups->count() }}</span>
    </div>
    <div class="table-responsive" id="backups-list">
        @if ($backups->isEmpty())
            @include('masjid::partials.empty-state', [
                'icon' => 'bi-database-x',
                'title' => __('No backups yet'),
                'description' => __('Create your first backup above.'),
            ])
        @else
            <table class="table table-hover mb-0 align-middle mj-responsive-table">
                <thead>
                    <tr>
                        <th>{{ __('Filename') }}</th>
                        <th>{{ __('Mode') }}</th>
                        <th>{{ __('Size') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($backups as $backup)
                        <tr>
                            <td data-label="{{ __('File') }}" class="mj-cell-name">
                                <code class="small">{{ $backup['filename'] }}</code>
                            </td>
                            <td data-label="{{ __('Mode') }}">
                                <span class="badge {{ $backup['mode'] === 'mosque' ? 'bg-primary' : 'bg-success' }}">
                                    <i class="bi {{ $backup['mode'] === 'mosque' ? 'bi-building' : 'bi-buildings' }}"></i>
                                    {{ $backup['mode'] === 'mosque' ? __('Mosque') : __('Full Module') }}
                                </span>
                            </td>
                            <td data-label="{{ __('Size') }}">{{ $backup['size'] }}</td>
                            <td data-label="{{ __('Created') }}">
                                {{ $backup['generated_at']?->format('d M Y, H:i') ?? $backup['modified_at']->format('d M Y, H:i') }}
                            </td>
                            <td class="mj-cell-actions">
                                <a href="{{ route('masjid.mosque.backups.download', [$mosque, $backup['filename']]) }}"
                                   class="btn btn-sm btn-outline-primary" title="{{ __('Download') }}">
                                    <i class="bi bi-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-warning btn-restore-stored"
                                        data-filename="{{ $backup['filename'] }}" title="{{ __('Restore') }}">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-backup"
                                        data-filename="{{ $backup['filename'] }}" title="{{ __('Delete') }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Restore from stored backup modal --}}
<div class="modal fade" id="restoreStoredModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Restore Backup') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger small">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ __('This will DELETE all current mosque data and replace it with the backup. This cannot be undone.') }}
                </div>
                <p class="small">{{ __('Restoring file:') }} <strong id="restore-stored-filename"></strong></p>
                <label class="form-label">
                    {{ __('Type') }} <strong>{{ $mosque->mosque_name }}</strong> {{ __('to confirm') }}
                </label>
                <input type="text" id="restore-stored-confirm" class="form-control" placeholder="{{ $mosque->mosque_name }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-restore-stored-btn">
                    <span class="spinner-border spinner-border-sm d-none" id="restore-stored-spinner"></span>
                    {{ __('Restore Now') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const mosqueName = '{{ $mosque->mosque_name }}';
    const restoreStoredModal = new bootstrap.Modal(document.getElementById('restoreStoredModal'));
    let restoreFilename = null;

    function toast(msg, type = 'success') {
        const container = document.getElementById('backup-toast-container');
        const el = document.createElement('div');
        el.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        container.appendChild(el);
        new bootstrap.Toast(el, { delay: 5000 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    function setLoading(btn, loading) {
        btn.disabled = loading;
        const spinner = btn.querySelector('.spinner-border');
        if (spinner) spinner.classList.toggle('d-none', !loading);
    }

    // ── Create mosque backup ──────────────────────────────────────────────
    document.getElementById('btn-backup-mosque').addEventListener('click', function () {
        setLoading(this, true);
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> {{ __('Creating...') }}';

        fetch('{{ route("masjid.mosque.backups.create.mosque", $mosque) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(b => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-database-add"></i> {{ __('Create Mosque Backup') }}';
                if (b.success) { toast(b.message); setTimeout(() => window.location.reload(), 1500); }
                else toast(b.message, 'error');
            })
            .catch(() => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-database-add"></i> {{ __('Create Mosque Backup') }}';
                toast('{{ __('Request failed.') }}', 'error');
            });
    });

    // ── Create company backup ─────────────────────────────────────────────
    document.getElementById('btn-backup-company').addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> {{ __('Creating...') }}';

        fetch('{{ route("masjid.mosque.backups.create.company", $mosque) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(b => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-database-add"></i> {{ __('Create Full Module Backup') }}';
                if (b.success) { toast(b.message); setTimeout(() => window.location.reload(), 1500); }
                else toast(b.message, 'error');
            })
            .catch(() => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-database-add"></i> {{ __('Create Full Module Backup') }}';
                toast('{{ __('Request failed.') }}', 'error');
            });
    });

    // ── Upload & Restore ──────────────────────────────────────────────────
    document.getElementById('btn-upload-restore').addEventListener('click', function () {
        const fileInput = document.getElementById('upload-backup-file');
        const confirmInput = document.getElementById('upload-confirm-name');
        const resultDiv = document.getElementById('restore-result');

        if (! fileInput.files.length) {
            toast('{{ __('Please select a backup file.') }}', 'error'); return;
        }
        if (confirmInput.value.trim() !== mosqueName) {
            toast('{{ __('Mosque name does not match. Restore cancelled.') }}', 'error'); return;
        }

        if (! confirm('{{ __('FINAL WARNING: This will permanently replace all mosque data. Are you absolutely sure?') }}')) return;

        const formData = new FormData();
        formData.append('backup_file', fileInput.files[0]);
        formData.append('confirm_name', confirmInput.value.trim());

        setLoading(this, true);
        resultDiv.innerHTML = '';

        fetch('{{ route("masjid.mosque.backups.upload", $mosque) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData,
        })
            .then(r => r.json().then(b => ({ status: r.status, b })))
            .then(({ status, b }) => {
                setLoading(this, false);
                if (b.success) {
                    let html = `<div class="alert alert-success"><strong>${b.message}</strong>`;
                    const stats = Array.isArray(b.stats) ? b.stats : [b.stats];
                    stats.forEach(s => {
                        html += `<div class="mt-2 small">
                            <i class="bi bi-building"></i> <strong>${s.mosque}</strong> —
                            ${s.members} {{ __('members') }},
                            ${s.seasons} {{ __('seasons') }},
                            ${s.payments} {{ __('payments') }}
                        </div>`;
                    });
                    html += `</div>`;
                    resultDiv.innerHTML = html;
                    toast(b.message);
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger">${b.message}</div>`;
                    toast(b.message, 'error');
                }
            })
            .catch(() => { setLoading(this, false); toast('{{ __('Upload failed.') }}', 'error'); });
    });

    // ── Restore from stored backup ────────────────────────────────────────
    document.querySelector('#backups-list')?.addEventListener('click', e => {
        const restoreBtn = e.target.closest('.btn-restore-stored');
        if (restoreBtn) {
            restoreFilename = restoreBtn.dataset.filename;
            document.getElementById('restore-stored-filename').textContent = restoreFilename;
            document.getElementById('restore-stored-confirm').value = '';
            restoreStoredModal.show();
        }

        const deleteBtn = e.target.closest('.btn-delete-backup');
        if (deleteBtn) {
            if (! confirm('{{ __('Delete this backup file?') }}')) return;
            const filename = deleteBtn.dataset.filename;
            fetch(`/app/masjid/{{ $mosque->id }}/backups/${encodeURIComponent(filename)}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(r => r.json())
                .then(b => { if (b.success) { toast(b.message); deleteBtn.closest('tr').remove(); } else toast(b.message, 'error'); });
        }
    });

    document.getElementById('confirm-restore-stored-btn').addEventListener('click', function () {
        const confirmVal = document.getElementById('restore-stored-confirm').value.trim();
        if (confirmVal !== mosqueName) {
            toast('{{ __('Mosque name does not match.') }}', 'error'); return;
        }

        setLoading(this, true);

        fetch(`/app/masjid/{{ $mosque->id }}/backups/restore/${encodeURIComponent(restoreFilename)}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ confirm_name: confirmVal }),
        })
            .then(r => r.json())
            .then(b => {
                setLoading(this, false);
                restoreStoredModal.hide();
                if (b.success) {
                    toast(b.message);
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    toast(b.message, 'error');
                }
            })
            .catch(() => { setLoading(this, false); toast('{{ __('Restore failed.') }}', 'error'); });
    });
})();
</script>
@endpush
@endsection