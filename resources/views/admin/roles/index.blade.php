<x-admin-layout>
    @section('title', 'Roles & Permissions')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Roles & Permissions') }}</h3>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> {{ __('New Role') }}
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Permissions') }}</th>
                        <th>{{ __('Users') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>
                                <strong>{{ $role->name }}</strong>
                                @if (in_array($role->name, ['super-admin', 'company-owner', 'company-staff']))
                                    <span class="badge bg-light text-dark border ms-1">{{ __('System') }}</span>
                                @endif
                            </td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if (! in_array($role->name, ['super-admin', 'company-owner', 'company-staff']))
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline"
                                          onsubmit="return confirm('{{ __('Delete this role?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>