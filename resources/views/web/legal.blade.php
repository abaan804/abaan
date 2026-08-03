<x-site-layout>
    @section('title', $pageTitle)

    <section class="py-5">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="eyebrow mb-2">{{ __('Legal') }}</div>
                    <h1 class="font-display mb-2" style="font-size: 2.4rem;">{{ $pageTitle }}</h1>

                    @if ($section?->updated_at)
                        <p class="text-muted small mb-5">
                            {{ __('Last updated') }}: {{ formatDate($section->updated_at) }}
                        </p>
                    @else
                        <p class="text-muted small mb-5">&nbsp;</p>
                    @endif

                    <div class="fs-6" style="color: var(--slate); line-height: 1.9;">
                        @if ($section?->translatedContent())
                            {!! nl2br(e($section->translatedContent())) !!}
                        @else
                            <p class="text-muted">
                                {{ __('This page has not been published yet. Please check back soon, or contact us if you have questions.') }}
                            </p>
                            <a href="{{ route('contact') }}" class="btn btn-ink-outline mt-2">{{ __('Contact Us') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-site-layout>