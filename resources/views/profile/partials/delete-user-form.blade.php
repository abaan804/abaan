<p class="text-muted small">
    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please download any data or information that you wish to retain before deleting your account.') }}
</p>

<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
    {{ __('Delete Account') }}
</button>

<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Confirm Account Deletion') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to delete your account? This action cannot be undone.') }}</p>
                    <label for="password_delete" class="form-label">{{ __('Password') }}</label>
                    <input id="password_delete" type="password" name="password"
                           class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                           placeholder="{{ __('Enter your password to confirm') }}">
                    @error('password', 'userDeletion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('Delete Account') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>