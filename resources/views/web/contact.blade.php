<x-site-layout>
    @section('title', __('Contact Us'))

    <section class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="eyebrow mb-3">{{ __('Get in Touch') }}</div>
                <h1 class="font-display mb-3" style="font-size: 2.6rem;">
                    {{ $sections->get('intro')?->translatedTitle() ?? __("We'd love to hear from you") }}
                </h1>
                <p class="fs-5 mx-auto" style="max-width: 620px; color: var(--slate);">
                    {{ $sections->get('intro')?->translatedContent() ?? __('Questions about pricing, modules, or onboarding? Send us a message and our team will respond shortly.') }}
                </p>
            </div>

            <div class="row g-5 justify-content-center">
                <div class="col-12 col-lg-5">
                    <div class="p-4 rounded-4 h-100" style="background:#fff; border: 1px solid var(--line);">
                        <h5 class="font-display mb-4">{{ __('Contact Details') }}</h5>

                        <div class="d-flex gap-3 mb-3">
                            <div class="feature-icon-box flex-shrink-0"><i class="bi bi-envelope"></i></div>
                            <div>
                                <div class="small text-muted">{{ __('Email') }}</div>
                                <div>{{ $sections->get('address')?->translatedContent() ? '' : 'support@abaan.test' }}</div>
                            </div>
                        </div>

                        @if ($sections->get('address')?->translatedContent())
                            <div class="d-flex gap-3">
                                <div class="feature-icon-box flex-shrink-0"><i class="bi bi-geo-alt"></i></div>
                                <div>
                                    <div class="small text-muted">{{ __('Address') }}</div>
                                    <div>{{ $sections->get('address')->translatedContent() }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="p-4 rounded-4" style="background:#fff; border: 1px solid var(--line);">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form method="POST" action="{{ route('contact.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" value="{{ old('name') }}"
                                           class="form-control @error('name') is-invalid @enderror" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email" value="{{ old('email') }}"
                                           class="form-control @error('email') is-invalid @enderror" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">{{ __('Phone') }}</label>
                                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">{{ __('Subject') }}</label>
                                    <input type="text" name="subject" value="{{ old('subject') }}" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Message') }} <span class="text-danger">*</span></label>
                                    <textarea name="message" rows="5" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                                    @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-amber btn-lg px-5">{{ __('Send Message') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-site-layout>