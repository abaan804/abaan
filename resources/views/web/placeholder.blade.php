<x-site-layout>
    @section('title', $label)

    <section class="py-5">
        <div class="container py-5 text-center">
            <i class="bi bi-cone-striped" style="font-size: 3rem; color: var(--amber);"></i>
            <h2 class="font-display mt-3">{{ $label }}</h2>
            <p class="text-muted">{{ __('This page is coming together — check back soon.') }}</p>
            <a href="{{ route('home') }}" class="btn btn-ink-outline mt-3">{{ __('Back to Home') }}</a>
        </div>
    </section>
</x-site-layout>