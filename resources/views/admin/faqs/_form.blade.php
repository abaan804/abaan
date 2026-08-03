@csrf
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><strong>{{ __('Question (Multi-language)') }}</strong></div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">{{ __('English') }} <span class="text-danger">*</span></label>
            <input type="text" name="question_en" value="{{ old('question_en', $faq->question_en) }}" class="form-control @error('question_en') is-invalid @enderror" required>
            @error('question_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('Urdu') }}</label>
            <input type="text" name="question_ur" value="{{ old('question_ur', $faq->question_ur) }}" class="form-control" dir="rtl" style="font-family: 'NotoNastaliqUrdu', serif;">
        </div>
        <div class="mb-0">
            <label class="form-label">{{ __('Arabic') }}</label>
            <input type="text" name="question_ar" value="{{ old('question_ar', $faq->question_ar) }}" class="form-control" dir="rtl">
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><strong>{{ __('Answer (Multi-language)') }}</strong></div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">{{ __('English') }} <span class="text-danger">*</span></label>
            <textarea name="answer_en" rows="3" class="form-control @error('answer_en') is-invalid @enderror" required>{{ old('answer_en', $faq->answer_en) }}</textarea>
            @error('answer_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('Urdu') }}</label>
            <textarea name="answer_ur" rows="3" class="form-control" dir="rtl" style="font-family: 'NotoNastaliqUrdu', serif;">{{ old('answer_ur', $faq->answer_ur) }}</textarea>
        </div>
        <div class="mb-0">
            <label class="form-label">{{ __('Arabic') }}</label>
            <textarea name="answer_ar" rows="3" class="form-control" dir="rtl">{{ old('answer_ar', $faq->answer_ar) }}</textarea>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body row g-3">
        <div class="col-12 col-md-4">
            <label class="form-label">{{ __('Category') }}</label>
            <input type="text" name="category" value="{{ old('category', $faq->category) }}" class="form-control" placeholder="general, billing...">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">{{ __('Sort Order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $faq->sort_order ?? 0) }}" class="form-control">
        </div>
        <div class="col-12 col-md-4 d-flex align-items-end">
            <div class="form-check form-switch">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $faq->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">{{ __('Active') }}</label>
            </div>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary w-100 mt-4">
    {{ $faq->exists ? __('Update FAQ') : __('Create FAQ') }}
</button>