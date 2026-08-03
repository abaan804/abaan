<x-site-layout>
    @section('title', __('Services'))

    <section class="py-5">
        <div class="container py-4 text-center">
            <div class="eyebrow mb-3">{{ __('Services') }}</div>
            <h1 class="font-display mb-3" style="font-size: 2.6rem;">
                {{ $sections->get('intro')?->translatedTitle() ?? __('More than software — a partner in setup') }}
            </h1>
            <p class="fs-5 mx-auto" style="max-width: 680px; color: var(--slate);">
                {{ $sections->get('intro')?->translatedContent() ?? __('From onboarding to data migration, our team helps you get every module running the way your business actually operates.') }}
            </p>
        </div>
    </section>

    <section class="py-5 site-hairline">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="feature-icon-box mb-3"><i class="bi bi-rocket-takeoff"></i></div>
                    <h5 class="font-display">{{ __('Guided Onboarding') }}</h5>
                    <p class="text-muted">{{ __('Step-by-step setup support so your team is productive from day one.') }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <div class="feature-icon-box mb-3"><i class="bi bi-database-up"></i></div>
                    <h5 class="font-display">{{ __('Data Migration') }}</h5>
                    <p class="text-muted">{{ __('Bringing records from spreadsheets or other systems into Abaan, cleanly.') }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <div class="feature-icon-box mb-3"><i class="bi bi-headset"></i></div>
                    <h5 class="font-display">{{ __('Ongoing Support') }}</h5>
                    <p class="text-muted">{{ __('Direct access to a support team that understands your business, not just the software.') }}</p>
                </div>
            </div>
        </div>
    </section>
</x-site-layout>