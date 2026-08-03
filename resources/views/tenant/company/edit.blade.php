<x-app-layout>
    @section('title', 'Company Profile')

    <h3 class="mb-4">{{ __('Company Profile') }}</h3>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            @if (! auth()->user()->can('manage company profile'))
                <div class="alert alert-secondary">
                    {{ __('You have view-only access. Only the company owner can edit these details.') }}
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 d-flex align-items-center gap-3 mb-2">
                                @if ($company->logo)
                                    <img src="{{ asset('storage/' . $company->logo) }}" class="rounded border" style="width: 70px; height: 70px; object-fit: cover;">
                                @else
                                    <div class="rounded border d-flex align-items-center justify-content-center bg-light" style="width: 70px; height: 70px;">
                                        <i class="bi bi-building fs-3 text-muted"></i>
                                    </div>
                                @endif
                                @can('manage company profile')
                                    <div class="flex-grow-1">
                                        <label class="form-label small">{{ __('Company Logo') }}</label>
                                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror">
                                        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                @endcan
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ __('Company Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $company->name) }}"
                                       class="form-control @error('name') is-invalid @enderror"
                                       @disabled(! auth()->user()->can('manage company profile')) required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input type="email" name="email" value="{{ old('email', $company->email) }}"
                                       class="form-control @error('email') is-invalid @enderror"
                                       @disabled(! auth()->user()->can('manage company profile'))>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ __('Phone') }}</label>
                                <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       @disabled(! auth()->user()->can('manage company profile'))>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ __('Slug') }}</label>
                                <input type="text" value="{{ $company->slug }}" class="form-control" disabled>
                                <small class="text-muted">{{ __('Used internally — cannot be changed.') }}</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('Address') }}</label>
                                <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror"
                                          @disabled(! auth()->user()->can('manage company profile'))>{{ old('address', $company->address) }}</textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @can('manage company profile')
                            <button type="submit" class="btn btn-primary mt-4">{{ __('Save Changes') }}</button>
                        @endcan
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>