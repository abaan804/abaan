<x-admin-layout>
    @section('title', 'Website Content')

    <h3 class="mb-4">{{ __('Website Content') }}</h3>

    <div class="row">
        <div class="col-12 col-lg-3">
            <div class="card shadow-sm">
                <div class="list-group list-group-flush">
                    @foreach ($pages as $key => $label)
                        <a href="{{ route('admin.content.index', ['page' => $key]) }}"
                           class="list-group-item list-group-item-action {{ $activePage === $key ? 'active' : '' }}">
                            {{ __($label) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-9">
            @forelse ($sections as $section)
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong>{{ ucfirst(str_replace('_', ' ', $section->section_key)) }}</strong>
                        <span class="badge bg-{{ $section->is_active ? 'success' : 'secondary' }}">
                            {{ $section->is_active ? __('Active') : __('Hidden') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.content.update', $section) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small">{{ __('Title (EN)') }}</label>
                                    <input type="text" name="title_en" value="{{ $section->title_en }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small">{{ __('Title (UR)') }}</label>
                                    <input type="text" name="title_ur" value="{{ $section->title_ur }}" class="form-control form-control-sm" dir="rtl" style="font-family: 'NotoNastaliqUrdu', serif;">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small">{{ __('Title (AR)') }}</label>
                                    <input type="text" name="title_ar" value="{{ $section->title_ar }}" class="form-control form-control-sm" dir="rtl">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label small">{{ __('Content (EN)') }}</label>
                                    <textarea name="content_en" rows="3" class="form-control form-control-sm">{{ $section->content_en }}</textarea>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small">{{ __('Content (UR)') }}</label>
                                    <textarea name="content_ur" rows="3" class="form-control form-control-sm" dir="rtl" style="font-family: 'NotoNastaliqUrdu', serif;">{{ $section->content_ur }}</textarea>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small">{{ __('Content (AR)') }}</label>
                                    <textarea name="content_ar" rows="3" class="form-control form-control-sm" dir="rtl">{{ $section->content_ar }}</textarea>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label small">{{ __('Image') }}</label>
                                    @if ($section->image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $section->image) }}" style="height: 60px;" class="rounded border">
                                        </div>
                                    @endif
                                    <input type="file" name="image" class="form-control form-control-sm">
                                </div>
                                <div class="col-12 col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                               {{ $section->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label">{{ __('Visible on site') }}</label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-sm btn-primary mt-3">{{ __('Save Section') }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">{{ __('No sections registered for this page yet.') }}</div>
            @endforelse
        </div>
    </div>
</x-admin-layout>