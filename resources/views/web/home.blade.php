<x-site-layout>
    @section('title', __('Home'))

    {{-- HERO --}}
    <section class="py-5 py-lg-6">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-12 col-lg-6">
                    <div class="eyebrow mb-3">{{ __('Multi-Tenant Business Platform') }}</div>
                    <h1 class="font-display mb-4" style="font-size: 3rem; line-height: 1.1;">
                        {{ $sections->get('hero')?->translatedTitle() ?? __('Every part of your business. One platform.') }}
                    </h1>
                    <p class="fs-5 mb-4" style="color: var(--slate);">
                        {{ $sections->get('hero')?->translatedContent() ?? __('Abaan brings your ledger, point of sale, HR, and more under one roof — in your language, on your terms.') }}
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ route('register') }}" class="btn btn-amber btn-lg px-4">{{ __('Start Free Trial') }}</a>
                        <a href="{{ route('solutions') }}" class="btn btn-ink-outline btn-lg px-4">{{ __('Explore Modules') }}</a>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="module-stack">
                        @php
                            $positions = ['t1', 't2', 't3', 't4', 't5', 't6'];
                        @endphp
                        @foreach ($modules->take(6) as $i => $module)
                            <div class="module-tile {{ $positions[$i] ?? '' }}">
                                <i class="bi {{ $module->icon ?? 'bi-puzzle' }}"></i>
                                <div class="tile-label">{{ $module->translated('name') }}</div>
                                @if ($module->status !== 'active')
                                    <div class="small text-muted mt-1">{{ __('Coming soon') }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- INTRO --}}
    <section class="py-5 site-hairline">
        <div class="container py-4">
            <div class="row justify-content-center text-center">
                <div class="col-12 col-lg-8">
                    <div class="eyebrow mb-3">{{ __('Why Abaan') }}</div>
                    <h2 class="font-display mb-3" style="font-size: 2.2rem;">
                        {{ $sections->get('intro')?->translatedTitle() ?? __('Built for businesses that wear many hats') }}
                    </h2>
                    <p class="fs-5" style="color: var(--slate);">
                        {{ $sections->get('intro')?->translatedContent() ?? __('Whether you run a shop, a school, or a clinic — Abaan adapts to how you work, not the other way around.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES HIGHLIGHT --}}
    <section class="py-5">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="feature-icon-box mb-3"><i class="bi bi-translate"></i></div>
                    <h5 class="font-display">{{ __('Speaks your language') }}</h5>
                    <p class="text-muted">{{ __('Full support for English, Urdu, and Arabic — with proper right-to-left layouts, not just translated text.') }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <div class="feature-icon-box mb-3"><i class="bi bi-puzzle"></i></div>
                    <h5 class="font-display">{{ __('Modular by design') }}</h5>
                    <p class="text-muted">{{ __('Turn on only the modules your business needs today, and add more as you grow.') }}</p>
                </div>
                <div class="col-12 col-md-4">
                    <div class="feature-icon-box mb-3"><i class="bi bi-shield-check"></i></div>
                    <h5 class="font-display">{{ __('Built on trust') }}</h5>
                    <p class="text-muted">{{ __('Role-based access, activity logs, and company-level data isolation, from day one.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- PACKAGES PREVIEW --}}
    @if ($packages->isNotEmpty())
    <section class="py-5 site-hairline">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="eyebrow mb-3">{{ __('Pricing') }}</div>
                <h2 class="font-display" style="font-size: 2.2rem;">{{ __('Simple plans, room to grow') }}</h2>
            </div>
            <div class="row g-4 justify-content-center">
                @foreach ($packages as $package)
                    <div class="col-12 col-md-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                            <div class="card-body p-4">
                                <h5 class="font-display">{{ $package->translated('name') }}</h5>
                                <div class="my-3">
                                    <span class="font-display" style="font-size: 2rem;">{{ formatCurrency($package->price_monthly) }}</span>
                                    <span class="text-muted">/{{ __('mo') }}</span>
                                </div>
                                <p class="text-muted small">{{ $package->translated('description') }}</p>
                                <a href="{{ route('register') }}" class="btn btn-ink-outline w-100 mt-3">{{ __('Get Started') }}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('pricing') }}" class="text-decoration-none">{{ __('Compare all plans') }} →</a>
            </div>
        </div>
    </section>
    @endif

    {{-- CTA --}}
    <section class="py-5">
        <div class="container py-5 text-center" style="background-color: var(--ink); border-radius: 20px; color: #fff;">
            <h2 class="font-display mb-3" style="font-size: 2rem; color: #fff;">
                {{ $sections->get('cta')?->translatedTitle() ?? __('Ready to bring it all together?') }}
            </h2>
            <p class="mb-4 opacity-75">
                {{ $sections->get('cta')?->translatedContent() ?? __('Start your free trial today — no credit card required.') }}
            </p>
            <a href="{{ route('register') }}" class="btn btn-amber btn-lg px-5">{{ __('Start Free Trial') }}</a>
        </div>
    </section>
</x-site-layout>