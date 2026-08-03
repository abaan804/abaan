@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Family Tree Manager'))
@section('ft-content')

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="ft-stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(26,82,118,.1);">
                <i class="bi bi-houses" style="color:var(--ft-primary);"></i>
            </div>
            <div class="text-muted small">{{ __('Families') }}</div>
            <div class="h4 mb-0">{{ $familyStats['total_families'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ft-stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(30,132,73,.1);">
                <i class="bi bi-people" style="color:var(--ft-green);"></i>
            </div>
            <div class="text-muted small">{{ __('Total Members') }}</div>
            <div class="h4 mb-0 text-success">{{ $memberStats['total_members'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ft-stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(26,82,118,.1);">
                <i class="bi bi-person-check" style="color:var(--ft-primary);"></i>
            </div>
            <div class="text-muted small">{{ __('Living') }}</div>
            <div class="h4 mb-0">{{ $memberStats['living'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ft-stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(86,101,115,.1);">
                <i class="bi bi-moon" style="color:#566573;"></i>
            </div>
            <div class="text-muted small">{{ __('Deceased') }}</div>
            <div class="h4 mb-0 text-secondary">{{ $memberStats['deceased'] }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="ft-stat-card p-3">
            <div class="text-muted small">{{ __('Male') }}</div>
            <div class="h5 mb-0" style="color:var(--ft-primary);">{{ $memberStats['male'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ft-stat-card p-3">
            <div class="text-muted small">{{ __('Female') }}</div>
            <div class="h5 mb-0" style="color:var(--ft-female);">{{ $memberStats['female'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ft-stat-card p-3">
            <div class="text-muted small">{{ __('Married') }}</div>
            <div class="h5 mb-0 text-success">{{ $memberStats['married'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ft-stat-card p-3">
            <div class="text-muted small">{{ __('Unmarried') }}</div>
            <div class="h5 mb-0">{{ $memberStats['unmarried'] }}</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Families Grid --}}
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <strong>{{ __('Your Families') }}</strong>
                @can('familytree.manage-families')
                    <a href="{{ route('familytree.families.index') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> {{ __('Manage') }}
                    </a>
                @endcan
            </div>
            <div class="card-body">
                @forelse ($families as $fam)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            @if ($fam->photo)
                                <img src="{{ asset('storage/' . $fam->photo) }}" class="rounded-circle"
                                     style="width:36px;height:36px;object-fit:cover;">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:36px;height:36px;background:rgba(26,82,118,.1);">
                                    <i class="bi bi-houses" style="color:var(--ft-primary);"></i>
                                </div>
                            @endif
                            <div>
                                <div class="fw-semibold small">{{ $fam->name }}</div>
                                <div class="text-muted small">{{ $fam->village ?? $fam->city ?? '—' }}</div>
                            </div>
                        </div>
                        <a href="{{ route('familytree.family.members.index', $fam) }}"
                           class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                    </div>
                @empty
                    @include('familytree::partials.empty-state', [
                        'icon' => 'bi-houses',
                        'title' => __('No families yet'),
                        'description' => __('Add your first family to get started.'),
                    ])
                @endforelse
            </div>
        </div>
    </div>

    {{-- Upcoming Birthdays --}}
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong><i class="bi bi-balloon-heart"></i> {{ __('Upcoming Birthdays (30 days)') }}</strong>
            </div>
            <div class="card-body">
                @forelse ($upcomingBirthdays->take(6) as $member)
                    @php
                        $birthday = $member->date_of_birth->setYear(now()->year);
                        if ($birthday->isPast()) $birthday = $birthday->addYear();
                        $daysLeft = (int) now()->diffInDays($birthday, false);
                    @endphp
                    <div class="ft-birthday-card p-2 mb-2 d-flex align-items-center gap-3">
                        @if ($member->profile_photo)
                            <img src="{{ asset('storage/' . $member->profile_photo) }}"
                                 class="ft-avatar" style="width:38px;height:38px;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:38px;height:38px;background:rgba(212,172,13,.15);">
                                <i class="bi bi-person" style="color:var(--ft-gold);"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $member->full_name }}</div>
                            <div class="text-muted small">
                                {{ $member->date_of_birth->format('d M') }}
                                @if ($member->age !== null) — {{ __('Turns') }} {{ $member->age + 1 }} @endif
                            </div>
                        </div>
                        <span class="badge {{ $daysLeft === 0 ? 'bg-success' : 'bg-light text-dark border' }}">
                            {{ $daysLeft === 0 ? __('Today!') : __(':d days', ['d' => $daysLeft]) }}
                        </span>
                    </div>
                @empty
                    <p class="text-muted small text-center mb-0">{{ __('No upcoming birthdays.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Recent Events --}}
<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="card-header bg-white border-0">
        <strong><i class="bi bi-calendar-event"></i> {{ __('Recent Events') }}</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ft-table">
            <thead>
                <tr>
                    <th>{{ __('Member') }}</th>
                    <th>{{ __('Family') }}</th>
                    <th>{{ __('Event') }}</th>
                    <th>{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentEvents as $event)
                    <tr>
                        <td data-label="{{ __('Member') }}" class="ft-cell-name">
                            {{ $event->member?->full_name ?? '—' }}
                        </td>
                        <td data-label="{{ __('Family') }}">{{ $event->family?->name ?? '—' }}</td>
                        <td data-label="{{ __('Event') }}">
                            <span class="badge bg-light text-dark border">{{ $event->display_title }}</span>
                        </td>
                        <td data-label="{{ __('Date') }}">{{ formatDate($event->event_date) }}</td>
                    </tr>
                @empty
                    <tr class="ft-row-empty">
                        <td colspan="4">
                            @include('familytree::partials.empty-state', [
                                'icon' => 'bi-calendar-x',
                                'title' => __('No events recorded yet'),
                            ])
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection