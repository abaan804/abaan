<x-site-layout>
    @section('title', $post->title_en)
    @section('meta_description', Str::limit($post->excerpt_en ?? strip_tags($post->content_en), 160))

    <article class="py-5">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="mb-4">
                        <a href="{{ route('blog.index') }}" class="text-decoration-none small">
                            <i class="bi bi-arrow-left"></i> {{ __('Back to Blog') }}
                        </a>
                    </div>

                    <div class="eyebrow mb-2">{{ formatDate($post->published_at) }}</div>
                    <h1 class="font-display mb-4" style="font-size: 2.4rem;">{{ $post->title_en }}</h1>

                    @if ($post->featured_image)
                        <img src="{{ asset('storage/' . $post->featured_image) }}" class="img-fluid rounded-4 mb-4 w-100" style="max-height: 420px; object-fit: cover;">
                    @endif

                    <div class="fs-5" style="color: var(--slate); line-height: 1.8;">
                        {!! nl2br(e($post->content_en)) !!}
                    </div>

                    @if ($post->author)
                        <div class="d-flex align-items-center gap-2 mt-5 pt-4 site-hairline">
                            <i class="bi bi-person-circle fs-3"></i>
                            <span class="text-muted">{{ __('Written by') }} <strong>{{ $post->author->name }}</strong></span>
                        </div>
                    @endif
                </div>
            </div>

            @if ($related->isNotEmpty())
                <div class="row justify-content-center mt-5 pt-4 site-hairline">
                    <div class="col-12 col-lg-10">
                        <h5 class="font-display mb-4">{{ __('More posts') }}</h5>
                        <div class="row g-4">
                            @foreach ($related as $rel)
                                <div class="col-12 col-md-4">
                                    <a href="{{ route('blog.show', $rel->slug) }}" class="text-decoration-none">
                                        <div class="small text-muted mb-1">{{ formatDate($rel->published_at) }}</div>
                                        <h6 class="font-display" style="color: var(--ink);">{{ $rel->title_en }}</h6>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </article>
</x-site-layout>