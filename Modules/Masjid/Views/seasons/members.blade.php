@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', $season->name)
@section('masjid-content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="text-muted small">
            {{ formatDate($season->start_date) }} — {{ formatDate($season->end_date) }}
            &nbsp;·&nbsp; {{ formatCurrency($season->contribution_amount) }} {{ __('per member') }}
        </div>
        <span class="badge bg-{{ $season->status === 'active' ? 'success' : ($season->status === 'completed' ? 'secondary' : 'warning') }}">
            {{ ucfirst($season->status) }}
        </span>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('masjid.mosque.seasons.index', [$mosque,'standalone' => 1]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
        <a href="{{ route('masjid.mosque.reports.season', [$mosque, $season,'standalone' => 1]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-bar-chart"></i> {{ __('Report') }}
        </a>
        @can('masjid.manage-seasons')
            <button type="button" class="btn btn-outline-primary" id="btn-assign-all" title="{{ __('Assign all active members') }}">
                <i class="bi bi-people-fill"></i> {{ __('Assign All') }}
            </button>
            <button type="button" class="btn btn-outline-warning" id="btn-sync-amount" title="{{ __('Sync contribution amount to all pending members') }}">
                <i class="bi bi-arrow-repeat"></i> {{ __('Sync Amount') }}
            </button>
            <button type="button" class="btn btn-primary" id="btn-assign-member">
                <i class="bi bi-person-plus"></i> {{ __('Assign Member') }}
            </button>
        @endcan
    </div>
</div>

<div id="season-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080;"></div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle mj-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Member') }}</th>
                    <th>{{ __('Amount Due') }}</th>
                    <th>{{ __('Amount Paid') }}</th>
                    <th>{{ __('Balance') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="season-members-table-body">
                <tr><td colspan="6" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Assign Member Modal --}}
@can('masjid.manage-seasons')
<div class="modal fade" id="assignMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Assign Member to Season') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">{{ __('Select Member') }}</label>
                <select id="assign-member-select" class="form-select">
                    <option value="">{{ __('— Choose —') }}</option>
                    @foreach ($allMembers as $m)
                        <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->mobile }})</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="confirm-assign-member-btn">{{ __('Assign') }}</button>
            </div>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl = '{{ route("masjid.mosque.seasons.members.table", [$mosque, $season]) }}';
    const tbody = document.getElementById('season-members-table-body');

    function toast(msg, type = 'success') {
        const container = document.getElementById('season-toast-container');
        const el = document.createElement('div');
        el.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        container.appendChild(el);
        new bootstrap.Toast(el, { delay: 4000 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    let currentPage = 1;

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm" style="color:var(--mj-primary);"></div></td></tr>`;
        fetch(`${tableUrl}?page=${page}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => { tbody.innerHTML = html; });
    }

    tbody.addEventListener('click', e => {
        const link = e.target.closest('#season-members-pagination a');
        if (link) { e.preventDefault(); loadTable(new URL(link.href).searchParams.get('page') || 1); }

        const removeBtn = e.target.closest('.btn-unassign-member');
        if (removeBtn) {
            if (!confirm('{{ __('Remove this member from the season?') }}')) return;
            fetch(`/app/masjid/{{ $mosque->id }}/seasons/{{ $season->id }}/unassign/${removeBtn.dataset.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(r => r.json().then(b => ({ ok: r.ok, b })))
                .then(({ ok, b }) => {
                    if (ok && b.success) { toast(b.message); loadTable(currentPage); }
                    else toast(b.message ?? '{{ __('Failed.') }}', 'error');
                });
        }
    });

    document.getElementById('btn-assign-all')?.addEventListener('click', () => {
        if (!confirm('{{ __('Assign all active members to this season?') }}')) return;
        fetch(`{{ route('masjid.mosque.seasons.assign-all', [$mosque, $season]) }}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json()).then(b => { toast(b.message, b.success ? 'success' : 'error'); loadTable(1); });
    });

    document.getElementById('btn-sync-amount')?.addEventListener('click', () => {
        if (!confirm('{{ __('Sync contribution amount to all pending (unpaid) members?') }}')) return;
        fetch(`{{ route('masjid.mosque.seasons.sync-amount', [$mosque, $season]) }}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json()).then(b => { toast(b.message, b.success ? 'success' : 'error'); loadTable(currentPage); });
    });

    const assignModal = document.getElementById('assignMemberModal')
        ? new bootstrap.Modal(document.getElementById('assignMemberModal'))
        : null;

    document.getElementById('btn-assign-member')?.addEventListener('click', () => assignModal?.show());

    document.getElementById('confirm-assign-member-btn')?.addEventListener('click', () => {
        const memberId = document.getElementById('assign-member-select').value;
        if (!memberId) { alert('{{ __('Please select a member.') }}'); return; }

        fetch(`/app/masjid/{{ $mosque->id }}/seasons/{{ $season->id }}/assign/${memberId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json().then(b => ({ ok: r.ok, b })))
            .then(({ ok, b }) => {
                assignModal?.hide();
                toast(b.message, ok && b.success ? 'success' : 'error');
                if (ok && b.success) loadTable(1);
            });
    });

    loadTable(1);
})();
</script>
@endpush
@endsection