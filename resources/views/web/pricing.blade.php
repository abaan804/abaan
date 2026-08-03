<x-site-layout>
    @section('title', __('Pricing'))

    <section class="py-5">
        <div class="container py-4 text-center">
            <div class="eyebrow mb-3">{{ __('Pricing') }}</div>
            <h1 class="font-display mb-3" style="font-size: 2.6rem;">
                {{ $sections->get('intro')?->translatedTitle() ?? __('Simple pricing, room to grow') }}
            </h1>
            <p class="fs-5 mx-auto mb-4" style="max-width: 680px; color: var(--slate);">
                {{ $sections->get('intro')?->translatedContent() ?? __('Start free. Upgrade whenever your business needs more.') }}
            </p>

            <div class="d-inline-flex align-items-center gap-3 p-2 rounded-pill" style="background:#fff; border: 1px solid var(--line);">
                <span class="px-3 small fw-semibold" id="label-monthly" style="color: var(--ink);">{{ __('Monthly') }}</span>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" id="billingToggle" style="width: 2.5rem; height: 1.4rem;">
                </div>
                <span class="px-3 small text-muted" id="label-yearly">{{ __('Yearly') }} <span class="badge bg-success ms-1">{{ __('Save') }}</span></span>
            </div>
        </div>
    </section>

    <section class="py-4 site-hairline">
        <div class="container py-4">
            <div class="row g-4 justify-content-center">
                @foreach ($packages as $package)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="font-display">{{ $package->translated('name') }}</h5>
                                <p class="text-muted small">{{ $package->translated('description') }}</p>

                                <div class="my-3">
                                    <span class="font-display price-monthly" style="font-size: 2.4rem;">{{ formatCurrency($package->price_monthly) }}</span>
                                    <span class="font-display price-yearly d-none" style="font-size: 2.4rem;">{{ formatCurrency($package->price_yearly) }}</span>
                                    <span class="text-muted period-label">/{{ __('mo') }}</span>
                                </div>

                                <ul class="list-unstyled small flex-grow-1">
                                    @forelse ($package->features as $feature)
                                        <li class="mb-2">
                                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                                            {{ $feature->feature_label_en }}: <strong>{{ $feature->value }}</strong>
                                        </li>
                                    @empty
                                        <li class="text-muted">{{ __('Core platform access') }}</li>
                                    @endforelse
                                </ul>

                                <a href="{{ route('register') }}" class="btn btn-ink-outline w-100 mt-2">{{ __('Get Started') }}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
    (function () {
        const toggle = document.getElementById('billingToggle');
        const monthlyPrices = document.querySelectorAll('.price-monthly');
        const yearlyPrices = document.querySelectorAll('.price-yearly');
        const periodLabels = document.querySelectorAll('.period-label');
        const monthlyLabel = document.getElementById('label-monthly');
        const yearlyLabel = document.getElementById('label-yearly');

        toggle.addEventListener('change', function () {
            const isYearly = toggle.checked;
            monthlyPrices.forEach(el => el.classList.toggle('d-none', isYearly));
            yearlyPrices.forEach(el => el.classList.toggle('d-none', !isYearly));
            periodLabels.forEach(el => el.textContent = isYearly ? '/{{ __('yr') }}' : '/{{ __('mo') }}');
            monthlyLabel.style.color = isYearly ? '' : 'var(--ink)';
            monthlyLabel.classList.toggle('text-muted', isYearly);
            monthlyLabel.classList.toggle('fw-semibold', !isYearly);
            yearlyLabel.style.color = isYearly ? 'var(--ink)' : '';
            yearlyLabel.classList.toggle('text-muted', !isYearly);
            yearlyLabel.classList.toggle('fw-semibold', isYearly);
        });
    })();
    </script>
    @endpush
</x-site-layout>