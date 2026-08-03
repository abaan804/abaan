@extends('layouts.admin')
@section('title', __('Packages'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ __('Packages') }}</h4>
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> {{ __('New Package') }}
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        @forelse ($packages as $package)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                    <div class="card-body">
                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $package->name }}</h5>
                                <div class="h4 mb-0 text-primary">
                                    {{ setting('currency_symbol', 'Rs.') }}{{ $package->formatted_price }}
                                    <span class="text-muted fs-6 fw-normal">/ {{ __('month') }}</span>
                                </div>
                            </div>
                            <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $package->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </div>

                        {{-- Trial Badge --}}
                        @if ($package->trial_days > 0)
                            <div class="mb-3">
                                <span class="badge bg-info text-dark">
                                    <i class="bi bi-hourglass-split"></i>
                                    {{ $package->trial_days }} {{ __('day trial') }}
                                </span>
                            </div>
                        @else
                            <div class="mb-3">
                                <span class="badge bg-secondary">{{ __('No trial') }}</span>
                            </div>
                        @endif

                        {{-- Modules --}}
                        <div class="mb-3">
                            <div class="small text-muted fw-semibold mb-2">{{ __('Included Modules') }}</div>
                            @if ($package->moduleDefinitions->isNotEmpty())
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($package->moduleDefinitions as $m)
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi {{ $m->icon }}"></i> {{ $m->name_en }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted small">{{ __('No modules assigned') }}</span>
                            @endif
                        </div>

                        @if ($package->description)
                            <p class="small text-muted mb-3">{{ $package->description }}</p>
                        @endif

                        {{-- Footer --}}
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="small text-muted">
                                <i class="bi bi-people"></i>
                                {{ $package->subscriptions_count }} {{ __('subscriptions') }}
                            </span>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.packages.edit', $package) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if ($package->subscriptions_count === 0)
                                    <form method="POST"
                                          action="{{ route('admin.packages.destroy', $package) }}"
                                          onsubmit="return confirm('{{ __('Delete this package?') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-box-seam" style="font-size:3rem;"></i>
                <p class="mt-3">{{ __('No packages created yet.') }}</p>
                <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
                    {{ __('Create First Package') }}
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection