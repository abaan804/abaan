<x-guest-layout>
    <h4 class="text-center mb-4">{{ __('Log in to Abaan') }}</h4>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">{{ __('Remember me') }}</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">{{ __('Log in') }}</button>

        <div class="text-center mt-3">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-decoration-none d-block mb-2">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
            <a href="{{ route('register') }}" class="text-decoration-none">
                {{ __("Don't have an account? Register") }}
            </a>
        </div>
    </form>
</x-guest-layout>