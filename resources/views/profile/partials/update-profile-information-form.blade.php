@if (session('status') === 'profile-updated')
    <div class="alert alert-success">{{ __('Profile updated successfully.') }}</div>
@endif

<form method="POST" action="{{ route('profile.update') }}">
    @csrf
    @method('PATCH')

    <div class="mb-3">
        <label for="name" class="form-label">{{ __('Name') }}</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
               class="form-control @error('name') is-invalid @enderror" required autofocus>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}"
               class="form-control @error('email') is-invalid @enderror" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-2 small">
                {{ __('Your email address is unverified.') }}
                <button type="submit" form="resend-verification-form" class="btn btn-link btn-sm p-0 align-baseline">
                    {{ __('Resend verification email.') }}
                </button>
            </div>
            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success mt-2 mb-0">{{ __('A new verification link has been sent.') }}</div>
            @endif
        @endif
    </div>

    <div class="mb-3">
        <label for="locale" class="form-label">{{ __('Preferred Language') }}</label>
        <select id="locale" name="locale" class="form-select @error('locale') is-invalid @enderror">
            @foreach (config('abaan.supported_locales') as $code => $info)
                <option value="{{ $code }}" {{ old('locale', $user->locale) === $code ? 'selected' : '' }}>
                    {{ $info['flag'] }} {{ $info['label'] }}
                </option>
            @endforeach
        </select>
        @error('locale') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
</form>

{{-- Moved outside the main form, linked via the `form="resend-verification-form"` attribute above --}}
<form id="resend-verification-form" method="POST" action="{{ route('verification.send') }}" class="d-none">
    @csrf
</form>