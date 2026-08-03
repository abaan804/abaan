<x-guest-layout>
    <div class="text-center">
        <i class="bi bi-person-x text-danger" style="font-size: 3rem;"></i>
        <h4 class="mt-3 mb-2">{{ __('Account Deactivated') }}</h4>
        <p class="text-muted">
            {{ __('Your account has been deactivated. Please contact your company administrator for assistance.') }}
        </p>
        <a href="{{ route('login') }}" class="btn btn-outline-primary mt-2">{{ __('Back to Login') }}</a>
    </div>
</x-guest-layout>