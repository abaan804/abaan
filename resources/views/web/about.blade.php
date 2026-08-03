<x-site-layout>
    @section('title', __('About Us'))

    <section class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="eyebrow mb-3">{{ __('About Abaan') }}</div>
                <h1 class="font-display" style="font-size: 2.6rem;">
                    {{ $sections->get('intro')?->translatedTitle() ?? __('Software that fits how you already work') }}
                </h1>
                <p class="fs-5 mx-auto mt-3" style="max-width: 680px; color: var(--slate);">
                    {{ $sections->get('intro')?->translatedContent() ?? __('Abaan started with a simple observation: most businesses juggle several disconnected tools just to keep the lights on. We built one platform instead.') }}
                </p>
            </div>
        </div>
    </section>

    <section class="py-5 site-hairline">
        <div class="container py-4">
            <div class="row g-5 align-items-center">
                <div class="col-12 col-lg-6">
                    <div class="eyebrow mb-2">{{ __('Our Mission') }}</div>
                    <h2 class="font-display mb-3" style="font-size: 2rem;">
                        {{ $sections->get('mission')?->translatedTitle() ?? __('Give every business its own toolkit') }}
                    </h2>
                    <p style="color: var(--slate);">
                        {{ $sections->get('mission')?->translatedContent() ?? __('Whether you run a single shop or a growing group of companies, Abaan adapts — turn on the modules you need, in the language your team works in.') }}
                    </p>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="p-4 rounded-4" style="background: #fff; border: 1px solid var(--line);">
                        <div class="row g-4 text-center">
                            <div class="col-6">
                                <div class="font-display" style="font-size: 2rem;">3</div>
                                <div class="small text-muted">{{ __('Languages supported') }}</div>
                            </div>
                            <div class="col-6">
                                <div class="font-display" style="font-size: 2rem;">6</div>
                                <div class="small text-muted">{{ __('Business modules') }}</div>
                            </div>
                            <div class="col-6">
                                <div class="font-display" style="font-size: 2rem;">1</div>
                                <div class="small text-muted">{{ __('Platform to manage it all') }}</div>
                            </div>
                            <div class="col-6">
                                <div class="font-display" style="font-size: 2rem;">24/7</div>
                                <div class="small text-muted">{{ __('Access, anywhere') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($sections->get('team')?->content_en || $sections->get('team')?->content_ur || $sections->get('team')?->content_ar)
    <section class="py-5">
        <div class="container py-4 text-center">
            <div class="eyebrow mb-2">{{ __('Our Team') }}</div>
            <h2 class="font-display mb-3" style="font-size: 2rem;">{{ $sections->get('team')->translatedTitle() }}</h2>
            <p class="mx-auto" style="max-width: 680px; color: var(--slate);">{{ $sections->get('team')->translatedContent() }}</p>
        </div>
    </section>
    @endif

    <section class="py-5">
        <div class="container py-5 text-center" style="background-color: var(--ink); border-radius: 20px; color: #fff;">
            <h2 class="font-display mb-3" style="font-size: 2rem; color: #fff;">{{ __('See it for yourself') }}</h2>
            <a href="{{ route('register') }}" class="btn btn-amber btn-lg px-5">{{ __('Start Free Trial') }}</a>
        </div>
    </section>
</x-site-layout>