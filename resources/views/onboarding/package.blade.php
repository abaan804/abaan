<x-app-layout>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="text-center mb-5">
                <h2>{{ __('Choose your package') }}</h2>
                <p class="text-muted">{{ __('Start with a free trial — you can change packages anytime.') }}</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('onboarding.package.store') }}">
                @csrf
                <div class="row g-4">
                    @forelse ($packages as $package)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">{{ $package->translated('name') }}</h5>
                                    <p class="text-muted small">{{ $package->translated('description') }}</p>

                                    <div class="my-3">
                                        <span class="h3">${{ number_format($package->price_monthly, 2) }}</span>
                                        <span class="text-muted">/ {{ __('month') }}</span>
                                    </div>

                                    <ul class="list-unstyled small mb-4">
                                        @foreach ($package->features as $feature)
                                            <li class="mb-1">
                                                <i class="bi bi-check-circle text-success"></i>
                                                {{ $feature->feature_label_en }}: {{ $feature->value }}
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="mt-auto">
                                        <button type="submit" name="package_id" value="{{ $package->id }}"
                                                class="btn btn-outline-primary w-100">
                                            {{ __('Select this package') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning text-center">
                                {{ __('No packages are available right now. Please contact support.') }}
                            </div>
                        </div>
                    @endforelse
                </div>
            </form>
        </div>
    </div>
</x-app-layout>