@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Mosque Profile'))
@section('masjid-content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('masjid.mosque.profile.update', $mosque) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius:14px;">
                <div class="card-header bg-white border-0"><strong>{{ __('Logo') }}</strong></div>
                <div class="card-body text-center">
                    @if ($mosque->logo)
                        <img src="{{ asset('storage/' . $mosque->logo) }}" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:100px;height:100px;background:rgba(27,107,69,.1);">
                            <i class="bi bi-building" style="font-size:2.5rem;color:var(--mj-primary);"></i>
                        </div>
                    @endif
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    <div class="form-text">{{ __('Square image recommended. Max 2MB.') }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0" style="border-radius:14px;">
                <div class="card-header bg-white border-0"><strong>{{ __('Basic Information') }}</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Village Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="village_name" class="form-control @error('village_name') is-invalid @enderror" value="{{ old('village_name', $mosque->village_name) }}" required>
                            @error('village_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Mosque Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="mosque_name" class="form-control @error('mosque_name') is-invalid @enderror" value="{{ old('mosque_name', $mosque->mosque_name) }}" required>
                            @error('mosque_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Scholar Name') }}</label>
                            <input type="text" name="scholar_name" class="form-control" value="{{ old('scholar_name', $mosque->scholar_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Scholar Contact') }}</label>
                            <input type="text" name="scholar_contact" class="form-control" value="{{ old('scholar_contact', $mosque->scholar_contact) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Scholar Email') }}</label>
                            <input type="email" name="scholar_email" class="form-control" value="{{ old('scholar_email', $mosque->scholar_email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Committee Name') }}</label>
                            <input type="text" name="committee_name" class="form-control" value="{{ old('committee_name', $mosque->committee_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Mosque Contact') }}</label>
                            <input type="text" name="mosque_contact" class="form-control" value="{{ old('mosque_contact', $mosque->mosque_contact) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ $mosque->status === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ $mosque->status === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius:14px;">
                <div class="card-header bg-white border-0"><strong>{{ __('Location') }}</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Address') }}</label>
                            <textarea name="address" rows="2" class="form-control">{{ old('address', $mosque->address) }}</textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('City') }}</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $mosque->city) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Province') }}</label>
                            <input type="text" name="province" class="form-control" value="{{ old('province', $mosque->province) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Country') }}</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', $mosque->country) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Postal Code') }}</label>
                            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $mosque->postal_code) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Google Map Link') }}</label>
                            <input type="url" name="map_link" class="form-control" value="{{ old('map_link', $mosque->map_link) }}" placeholder="https://maps.google.com/...">
                            @if ($mosque->map_link)
                                <a href="{{ $mosque->map_link }}" target="_blank" class="form-text">{{ __('View on map') }} <i class="bi bi-box-arrow-up-right"></i></a>
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $mosque->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> {{ __('Save Profile') }}
        </button>
        <a href="{{ route('masjid.mosque.dashboard', $mosque) }}" class="btn btn-outline-secondary ms-2">{{ __('Cancel') }}</a>
    </div>
</form>

@endsection