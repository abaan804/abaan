<x-site-layout>
    @section('title', __('Features'))

    <section class="py-5">
        <div class="container py-4 text-center">
            <div class="eyebrow mb-3">{{ __('Platform Features') }}</div>
            <h1 class="font-display mb-3" style="font-size: 2.6rem;">
                {{ $sections->get('intro')?->translatedTitle() ?? __('Everything a growing business needs, built in') }}
            </h1>
        </div>
    </section>

    <section class="py-5 site-hairline">
        <div class="container py-4">
            <div class="row g-4">
                @php
                    $features = [
                        ['icon' => 'bi-translate', 'title' => __('Multi-language by default'), 'desc' => __('English, Urdu, and Arabic — with true RTL layouts, not just flipped text.')],
                        ['icon' => 'bi-buildings', 'title' => __('Multi-company support'), 'desc' => __('Run multiple companies under one account, each with isolated data.')],
                        ['icon' => 'bi-puzzle', 'title' => __('Modular architecture'), 'desc' => __('Enable only the modules you need — Ledger, POS, HR, School, Clinic, CRM.')],
                        ['icon' => 'bi-person-badge', 'title' => __('Role-based access'), 'desc' => __('Control exactly who can see and do what, down to individual permissions.')],
                        ['icon' => 'bi-clock-history', 'title' => __('Activity logs'), 'desc' => __('A full audit trail of important actions across your account.')],
                        ['icon' => 'bi-credit-card-2-front', 'title' => __('Flexible billing'), 'desc' => __('Monthly or yearly plans, with manual or online payment options.')],
                    ];
                @endphp
                @foreach ($features as $feature)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-4 h-100 rounded-4" style="background:#fff; border: 1px solid var(--line);">
                            <div class="feature-icon-box mb-3"><i class="bi {{ $feature['icon'] }}"></i></div>
                            <h5 class="font-display">{{ $feature['title'] }}</h5>
                            <p class="text-muted mb-0">{{ $feature['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-site-layout>