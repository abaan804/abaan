<x-site-layout>
    @section('title', __('Solutions'))

    <section class="py-5">
        <div class="container py-4 text-center">
            <div class="eyebrow mb-3">{{ __('Solutions') }}</div>
            <h1 class="font-display mb-3" style="font-size: 2.6rem;">
                {{ $sections->get('intro')?->translatedTitle() ?? __('One platform, every kind of business') }}
            </h1>
            <p class="fs-5 mx-auto" style="max-width: 680px; color: var(--slate);">
                {{ $sections->get('intro')?->translatedContent() ?? __('Pick the modules that match how you operate — and add more as you grow.') }}
            </p>
        </div>
    </section>

    <section class="py-5 site-hairline">
        <div class="container py-4">
            <div class="row g-4">
                @foreach ($modules as $module)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-4 h-100 rounded-4 position-relative" style="background:#fff; border: 1px solid var(--line);">
                            @if ($module->status !== 'active')
                                <span class="badge bg-secondary position-absolute top-0 end-0 m-3">{{ __('Coming Soon') }}</span>
                            @endif
                            <div class="feature-icon-box mb-3"><i class="bi {{ $module->icon ?? 'bi-puzzle' }}"></i></div>
                            <h5 class="font-display">{{ $module->translated('name') }}</h5>
                            <p class="text-muted mb-0">
                                {{ $module->translated('description') ?? __('Details coming soon.') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container py-5 text-center" style="background-color: var(--ink); border-radius: 20px; color: #fff;">
            <h2 class="font-display mb-3" style="font-size: 2rem; color: #fff;">{{ __('Not sure which modules you need?') }}</h2>
            <p class="mb-4 opacity-75">{{ __('Talk to us and we will help you map your business to the right setup.') }}</p>
            <a href="{{ route('contact') }}" class="btn btn-amber btn-lg px-5">{{ __('Contact Us') }}</a>
        </div>
    </section>
</x-site-layout>