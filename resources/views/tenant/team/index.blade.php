<x-app-layout>
    @section('title', 'Team')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Team') }}</h3>
        @can('manage company users')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="bi bi-person-plus"></i> {{ __('Add Team Member') }}
            </button>
        @endcan
    </div>
    @if (session('revealed_password'))
        <div class="alert alert-warning">
            <strong>{{ __('Temporary password for') }} {{ session('revealed_for') }}:</strong>
            <code class="fs-5 ms-2">{{ session('revealed_password') }}</code>
            <div class="small mt-1">{{ __('Share this with them securely. It will not be shown again.') }}</div>
        </div>
    @endif
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Joined') }}</th>
                        @can('manage company users')
                            <th class="text-end">{{ __('Actions') }}</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $member)
                        <tr>
                            <td>
                                {{ $member->name }}
                                @if ($member->id === auth()->id())
                                    <span class="badge bg-light text-dark border ms-1">{{ __('You') }}</span>
                                @endif
                            </td>
                            <td>{{ $member->email }}</td>
                            <td>
                                @foreach ($member->roles as $role)
                                    <span class="badge bg-light text-dark border">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-{{ $member->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($member->status) }}
                                </span>
                            </td>
                            <td>{{ formatDate($member->created_at) }}</td>
                            @can('manage company users')
                                <td class="text-end">
                                    @unless ($member->hasRole('company-owner'))
                                        <form method="POST" action="{{ route('team.toggle-status', $member) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                {{ $member->status === 'active' ? __('Deactivate') : __('Activate') }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-manage-permissions"
                                                data-id="{{ $member->id }}" data-name="{{ $member->name }}">
                                            <i class="bi bi-shield-lock"></i>
                                        </button>

                                        <form method="POST" action="{{ route('team.reset-password', $member) }}" class="d-inline"
                                            onsubmit="return confirm('{{ __('Generate a new password for this user?') }}');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-key"></i> {{ __('Reset Password') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('team.destroy', $member) }}" class="d-inline"
                                              onsubmit="return confirm('{{ __('Remove this team member?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endunless
                                </td>
                            @endcan
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

<div class="modal fade" id="permissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="permissions-form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Permissions for') }} <span id="permissions-user-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="permissions-body">
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Permissions') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

    @can('manage company users')
        <div class="modal fade" id="addMemberModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('team.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Add Team Member') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Name') }}</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <p class="text-muted small mt-3 mb-0">
                                {{ __('A temporary password will be generated and emailed to them.') }}
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Send Invitation') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @push('scripts')
<script>
(function () {
    const permissionsModal = new bootstrap.Modal(document.getElementById('permissionsModal'));
    const permissionsBody = document.getElementById('permissions-body');
    const permissionsForm = document.getElementById('permissions-form');
    let currentUserId = null;

    document.querySelectorAll('.btn-manage-permissions').forEach(btn => {
        btn.addEventListener('click', function () {
            currentUserId = this.dataset.id;
            document.getElementById('permissions-user-name').textContent = this.dataset.name;
            permissionsForm.action = `/team/${currentUserId}/permissions`;

            permissionsBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>`;
            permissionsModal.show();

            fetch(`/team/${currentUserId}/permissions`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(({ groups }) => {
                    if (groups.length === 0) {
                        permissionsBody.innerHTML = `<p class="text-muted text-center mb-0">{{ __('No module permissions are available yet.') }}</p>`;
                        return;
                    }

                    let html = '';
                    groups.forEach(group => {
                        html += `<div class="mb-4"><h6 class="mb-3">${group.label}</h6><div class="row g-2">`;
                        group.permissions.forEach(perm => {
                            html += `
                                <div class="col-12 col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" name="permissions[]" value="${perm.name}"
                                               class="form-check-input" id="perm-${perm.name}" ${perm.granted ? 'checked' : ''}>
                                        <label class="form-check-label" for="perm-${perm.name}">${perm.label}</label>
                                    </div>
                                </div>`;
                        });
                        html += `</div></div>`;
                    });
                    permissionsBody.innerHTML = html;
                })
                .catch(() => {
                    permissionsBody.innerHTML = `<p class="text-danger text-center mb-0">{{ __('Failed to load permissions.') }}</p>`;
                });
        });
    });

    // Standard form submit (full page reload) — permissions changes are infrequent,
    // so a normal POST/redirect is simpler here than full AJAX, consistent with how
    // the rest of Team Management (Step K) already works.
})();
</script>
@endpush
</x-app-layout>
