<x-site-layout>
    @section('title', __('FAQ'))

    <section class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="eyebrow mb-3">{{ __('Frequently Asked Questions') }}</div>
                <h1 class="font-display" style="font-size: 2.6rem;">{{ __('Got questions? We have answers.') }}</h1>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    @forelse ($faqs as $category => $items)
                        <h5 class="font-display mb-3 mt-4">{{ ucfirst($category) }}</h5>
                        <div class="accordion mb-4" id="accordion-{{ Str::slug($category) }}">
                            @foreach ($items as $faq)
                                <div class="accordion-item border-0 mb-2" style="border-radius: 10px; overflow: hidden;">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $loop->parent->first && $loop->first ? '' : 'collapsed' }}"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#faq-{{ $faq->id }}">
                                            {{ $faq->translatedQuestion() ?? $faq->question_en }}
                                        </button>
                                    </h2>
                                    <div id="faq-{{ $faq->id }}"
                                         class="accordion-collapse collapse {{ $loop->parent->first && $loop->first ? 'show' : '' }}"
                                         data-bs-parent="#accordion-{{ Str::slug($category) }}">
                                        <div class="accordion-body text-muted">
                                            {{ $faq->translatedAnswer() ?? $faq->answer_en }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">{{ __('No FAQs available yet.') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted">{{ __("Still have questions?") }}</p>
                <a href="{{ route('contact') }}" class="btn btn-ink-outline">{{ __('Contact Us') }}</a>
            </div>
        </div>
    </section>
</x-site-layout>