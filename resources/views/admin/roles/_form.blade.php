@csrf

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><strong>{{ __('Role Name') }}</strong></div>
    <div class="card-body">
        <input type="text" name="name" value="{{ old('name', $role->name) }}"
               class="form-control @error('name') is-invalid @enderror"
               placeholder="e.g. support-agent" {{ in_array($role->name, ['super-admin','company-owner','company-staff']) ? 'readonly' : '' }} required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if (in_array($role->name, ['super-admin','company-owner','company-staff']))
            <small class="text-muted">{{ __('Core system role names cannot be changed.') }}</small>
        @endif
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white"><strong>{{ __('Permissions') }}</strong></div>
    <div class="card-body">
        <div class="row">
            @foreach ($permissions as $permission)
                <div class="col-12 col-md-6 mb-2">
                    <div class="form-check">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                               class="form-check-input" id="perm-{{ $permission->id }}"
                               {{ in_array($permission->name, old('permissions', $assignedPermissionNames)) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm-{{ $permission->id }}">
                            {{ ucfirst($permission->name) }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary w-100 mt-4">
    {{ $role->exists ? __('Update Role') : __('Create Role') }}
</button>