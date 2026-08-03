<x-guest-layout>
    <h4 class="text-center mb-4">{{ __('Create your Abaan account') }}</h4>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Your Name') }}</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror" required autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="company_name" class="form-label">{{ __('Company Name') }}</label>
            <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}"
                   class="form-control @error('company_name') is-invalid @enderror" required>
            @error('company_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror" required>
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

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">{{ __('Register') }}</button>

        <p class="text-center mt-3 mb-0">
            <a href="{{ route('login') }}" class="text-decoration-none">{{ __('Already registered? Log in') }}</a>
        </p>
    </form>
</x-guest-layout>