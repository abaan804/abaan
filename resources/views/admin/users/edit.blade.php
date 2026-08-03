<x-admin-layout>
    @section('title', 'Edit User')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Edit User') }}: {{ $user->name }}</h3>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>{{ __('Profile') }}</strong></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('New Password') }} <small class="text-muted">({{ __('leave blank to keep current') }})</small></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">{{ __('Confirm New Password') }}</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>{{ __('Roles') }}</strong></div>
                    <div class="card-body">
                        @foreach ($roles as $role)
                            <div class="form-check mb-2">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                       class="form-check-input" id="role-{{ $role->id }}"
                                       {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">{{ __('Save Changes') }}</button>
    </form>
</x-admin-layout>