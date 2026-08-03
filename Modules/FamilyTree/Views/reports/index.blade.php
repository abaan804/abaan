@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Reports') . ' — ' . $family->name)
@section('ft-content')

<div class="row g-3">
    @php
        $reports = [
            ['icon' => 'bi-people', 'title' => __('Members Report'), 'desc' => __('Full list with filters, PDF and Excel export'), 'route' => route('familytree.family.reports.members', $family)],
            ['icon' => 'bi-cake2', 'title' => __('Birth Report'), 'desc' => __('Members by date of birth or year'), 'route' => route('familytree.family.reports.births', $family)],
            ['icon' => 'bi-moon-stars', 'title' => __('Death Report'), 'desc' => __('Deceased members with dates'), 'route' => route('familytree.family.reports.deaths', $family)],
            ['icon' => 'bi-heart', 'title' => __('Marriage Report'), 'desc' => __('All marriage records with status'), 'route' => route('familytree.family.reports.marriages', $family)],
            ['icon' => 'bi-calendar-event', 'title' => __('Events Report'), 'desc' => __('Life events by type and date range'), 'route' => route('familytree.family.reports.events', $family)],
            ['icon' => 'bi-exclamation-circle', 'title' => __('Missing Information'), 'desc' => __('Members with incomplete records'), 'route' => route('familytree.family.reports.missing', $family)],
        ];
    @endphp

    @foreach ($reports as $report)
        <div class="col-12 col-md-6 col-lg-4">
            <a href="{{ $report['route'] }}?standalone=1" class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100" style="border-radius:14px;">
                    <div class="card-body">
                        <i class="bi {{ $report['icon'] }} fs-2" style="color:var(--ft-primary);"></i>
                        <h6 class="mt-3 fw-bold" style="color:var(--ft-primary);">{{ $report['title'] }}</h6>
                        <p class="text-muted small mb-0">{{ $report['desc'] }}</p>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

@endsection