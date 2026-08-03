<x-site-layout>
    @section('title', __('Blog'))

    <section class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="eyebrow mb-3">{{ __('From the Blog') }}</div>
                <h1 class="font-display" style="font-size: 2.6rem;">{{ __('News, tips, and product updates') }}</h1>
            </div>

            <div class="row g-4">
                @forelse ($posts as $post)
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; overflow: hidden;">
                                @if ($post->featured_image)
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" class="card-img-top" style="height: 190px; object-fit: cover;">
                                @else
                                    <div style="height: 190px; background: var(--ink);"></div>
                                @endif
                                <div class="card-body p-4">
                                    <div class="small text-muted mb-2">{{ formatDate($post->published_at) }}</div>
                                    <h5 class="font-display" style="color: var(--ink);">{{ $post->title_en }}</h5>
                                    <p class="text-muted small mb-0">{{ Str::limit($post->excerpt_en, 110) }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">{{ __('No blog posts published yet — check back soon.') }}</div>
                @endforelse
            </div>

            @if ($posts->hasPages())
                <div class="mt-5 d-flex justify-content-center">{{ $posts->links() }}</div>
            @endif
        </div>
    </section>
</x-site-layout>