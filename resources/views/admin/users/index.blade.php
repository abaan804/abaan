<x-admin-layout>
    @section('title', 'Users')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Users') }}</h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSuperAdminModal">
            <i class="bi bi-plus-lg"></i> {{ __('New Super Admin') }}
        </button>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="{{ __('Search by name or email') }}">
                </div>
                <div class="col-12 col-md-3">
                    <select name="type" class="form-select">
                        <option value="">{{ __('All Users') }}</option>
                        <option value="super_admins" {{ request('type') === 'super_admins' ? 'selected' : '' }}>{{ __('Super Admins') }}</option>
                        <option value="company_users" {{ request('type') === 'company_users' ? 'selected' : '' }}>{{ __('Company Users') }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                </div>
                <div class="col-12 col-md-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">{{ __('Reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Company') }}</th>
                        <th>{{ __('Roles') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->company?->name ?? '—' }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge bg-light text-dark border">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No users found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer bg-white">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- New Super Admin Modal --}}
    <div class="modal fade" id="newSuperAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.store-super-admin') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Create Super Admin') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Password') }}</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">{{ __('Confirm Password') }}</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>