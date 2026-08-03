<form method="POST" action="{{ route('password.update') }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
        <input id="current_password" type="password" name="current_password"
               class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
        @error('current_password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">{{ __('New Password') }}</label>
        <input id="password" type="password" name="password"
               class="form-control @error('password', 'updatePassword') is-invalid @enderror">
        @error('password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
        <input id="password_confirmation" type="password" name="password_confirmation"
               class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror">
        @error('password_confirmation', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @if (session('status') === 'password-updated')
        <div class="alert alert-success">{{ __('Password updated successfully.') }}</div>
    @endif

    <button type="submit" class="btn btn-primary">{{ __('Update Password') }}</button>
</form>