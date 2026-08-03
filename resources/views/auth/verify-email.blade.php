<x-guest-layout>
    <h4 class="text-center mb-4">{{ __('Verify Your Email') }}</h4>

    <p class="text-muted small mb-4">
        {{ __('Thanks for signing up! Please check your email for a verification link before continuing.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <div class="d-flex justify-content-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">{{ __('Resend Verification Email') }}</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">{{ __('Log Out') }}</button>
        </form>
    </div>
</x-guest-layout>