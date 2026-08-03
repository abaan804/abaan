@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Search'))
@section('ft-content')

<div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
    <div class="card-body">
        <form method="GET" action="{{ route('familytree.global-search') }}">
            <div class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="q" value="{{ $query }}"
                           class="form-control form-control-lg"
                           placeholder="{{ __('Search by name, CNIC, contact, occupation...') }}"
                           autofocus>
                </div>
                <div class="col-6 col-md-2">
                    <select name="gender" class="form-select form-select-lg">
                        <option value="">{{ __('All Genders') }}</option>
                        <option value="male"   {{ ($filters['gender'] ?? '') === 'male'   ? 'selected' : '' }}>{{ __('Male') }}</option>
                        <option value="female" {{ ($filters['gender'] ?? '') === 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="life_status" class="form-select form-select-lg">
                        <option value="">{{ __('All') }}</option>
                        <option value="living"   {{ ($filters['life_status'] ?? '') === 'living'   ? 'selected' : '' }}>{{ __('Living') }}</option>
                        <option value="deceased" {{ ($filters['life_status'] ?? '') === 'deceased' ? 'selected' : '' }}>{{ __('Deceased') }}</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="family_id" class="form-select form-select-lg">
                        <option value="">{{ __('All Families') }}</option>
                        @foreach ($families as $fam)
                            <option value="{{ $fam->id }}"
                                {{ ($filters['family_id'] ?? '') == $fam->id ? 'selected' : '' }}>
                                {{ $fam->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-1">
                    <button type="submit" class="btn btn-primary w-100" style="height:48px;">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if ($query)
    {{-- Members results --}}
    <h6 class="mb-3 text-muted">
        <i class="bi bi-people"></i>
        {{ __('Members') }}
        <span class="badge bg-primary ms-1">{{ $results['members']->count() }}</span>
    </h6>

    @if ($results['members']->isNotEmpty())
        <div class="row g-3 mb-4">
            @foreach ($results['members'] as $member)
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="{{ route('familytree.family.members.show', [$member->family_id, $member]) }}"
                       class="text-decoration-none">
                        <div class="card shadow-sm border-0 h-100" style="border-radius:12px;">
                            <div class="card-body d-flex align-items-center gap-3">
                                @if ($member->profile_photo)
                                    <img src="{{ asset('storage/' . $member->profile_photo) }}"
                                         class="ft-avatar ft-avatar-{{ $member->gender }}"
                                         style="width:48px;height:48px;">
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:48px;height:48px;background:{{ $member->gender === 'female' ? 'rgba(142,68,173,.1)' : 'rgba(26,82,118,.1)' }};">
                                        <i class="bi bi-person fs-4"
                                           style="color:{{ $member->gender === 'female' ? 'var(--ft-female)' : 'var(--ft-primary)' }};"></i>
                                    </div>
                                @endif
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-bold text-dark">{{ $member->full_name }}</div>
                                    <div class="small text-muted">
                                        {{ __('S/O') }} {{ $member->father_display_name }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $member->family?->name ?? '' }}
                                        @if ($member->occupation) · {{ $member->occupation }} @endif
                                    </div>
                                    <div class="d-flex gap-1 mt-1">
                                        <span class="ft-badge-{{ $member->gender }}">{{ ucfirst($member->gender) }}</span>
                                        <span class="ft-badge-{{ $member->life_status }}">{{ ucfirst($member->life_status) }}</span>
                                        @if ($member->age !== null)
                                            <span class="badge bg-light text-dark border">{{ $member->age }} {{ __('yrs') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-light border mb-4">
            <i class="bi bi-search"></i> {{ __('No members found for') }} "<strong>{{ $query }}</strong>"
        </div>
    @endif

    {{-- Events results --}}
    @if ($results['events']->isNotEmpty())
        <h6 class="mb-3 text-muted">
            <i class="bi bi-calendar-event"></i>
            {{ __('Events') }}
            <span class="badge bg-secondary ms-1">{{ $results['events']->count() }}</span>
        </h6>
        <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle ft-table">
                    <thead>
                        <tr>
                            <th>{{ __('Member') }}</th>
                            <th>{{ __('Family') }}</th>
                            <th>{{ __('Event') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Location') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results['events'] as $event)
                            <tr>
                                <td data-label="{{ __('Member') }}" class="ft-cell-name">
                                    {{ $event->member?->full_name ?? '—' }}
                                </td>
                                <td data-label="{{ __('Family') }}">{{ $event->family?->name ?? '—' }}</td>
                                <td data-label="{{ __('Event') }}">
                                    <span class="badge bg-light text-dark border">{{ $event->display_title }}</span>
                                </td>
                                <td data-label="{{ __('Date') }}">{{ formatDate($event->event_date) }}</td>
                                <td data-label="{{ __('Location') }}">{{ $event->location ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@else
    <div class="text-center py-5 text-muted">
        <i class="bi bi-search" style="font-size:3rem;"></i>
        <p class="mt-3">{{ __('Enter a name, CNIC, occupation or contact number to search across all families.') }}</p>
    </div>
@endif

@endsection