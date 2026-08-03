@csrf
<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Title (Multi-language)') }}</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('English') }} <span class="text-danger">*</span></label>
                    <input type="text" name="title_en" value="{{ old('title_en', $blog->title_en) }}" class="form-control @error('title_en') is-invalid @enderror" required>
                    @error('title_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Urdu') }}</label>
                    <input type="text" name="title_ur" value="{{ old('title_ur', $blog->title_ur) }}" class="form-control" dir="rtl" style="font-family: 'NotoNastaliqUrdu', serif;">
                </div>
                <div class="mb-0">
                    <label class="form-label">{{ __('Arabic') }}</label>
                    <input type="text" name="title_ar" value="{{ old('title_ar', $blog->title_ar) }}" class="form-control" dir="rtl">
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Excerpt') }}</strong></div>
            <div class="card-body">
                <textarea name="excerpt_en" rows="2" class="form-control mb-2" placeholder="{{ __('English excerpt') }}">{{ old('excerpt_en', $blog->excerpt_en) }}</textarea>
                <textarea name="excerpt_ur" rows="2" class="form-control mb-2" dir="rtl" style="font-family: 'NotoNastaliqUrdu', serif;" placeholder="{{ __('Urdu excerpt') }}">{{ old('excerpt_ur', $blog->excerpt_ur) }}</textarea>
                <textarea name="excerpt_ar" rows="2" class="form-control" dir="rtl" placeholder="{{ __('Arabic excerpt') }}">{{ old('excerpt_ar', $blog->excerpt_ar) }}</textarea>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>{{ __('Content') }}</strong></div>
            <div class="card-body">
                <label class="form-label small">{{ __('English') }}</label>
                <textarea name="content_en" rows="8" class="form-control mb-3">{{ old('content_en', $blog->content_en) }}</textarea>
                <label class="form-label small">{{ __('Urdu') }}</label>
                <textarea name="content_ur" rows="8" class="form-control mb-3" dir="rtl" style="font-family: 'NotoNastaliqUrdu', serif;">{{ old('content_ur', $blog->content_ur) }}</textarea>
                <label class="form-label small">{{ __('Arabic') }}</label>
                <textarea name="content_ar" rows="8" class="form-control" dir="rtl">{{ old('content_ar', $blog->content_ar) }}</textarea>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Publish') }}</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="draft" {{ old('status', $blog->status) === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                        <option value="published" {{ old('status', $blog->status) === 'published' ? 'selected' : '' }}>{{ __('Published') }}</option>
                    </select>
                </div>
                @if ($blog->exists)
                    <p class="text-muted small mb-0">{{ __('Slug') }}: <code>{{ $blog->slug }}</code></p>
                @else
                    <p class="text-muted small mb-0">{{ __('Slug will be generated automatically from the English title.') }}</p>
                @endif
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Featured Image') }}</strong></div>
            <div class="card-body">
                @if ($blog->featured_image)
                    <img src="{{ asset('storage/' . $blog->featured_image) }}" class="img-fluid rounded mb-2">
                @endif
                <input type="file" name="featured_image" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            {{ $blog->exists ? __('Update Post') : __('Create Post') }}
        </button>
    </div>
</div>