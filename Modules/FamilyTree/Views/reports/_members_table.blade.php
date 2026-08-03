<div data-total="{{ $members->count() }}"
     data-male="{{ $members->where('gender','male')->count() }}"
     data-female="{{ $members->where('gender','female')->count() }}"
     class="d-none" id="r-totals-data"></div>

@forelse ($members as $member)
    <tr>
        <td data-label="{{ __('Name') }}" class="ft-cell-name">
            <a href="{{ route('familytree.family.members.show', [$family, $member]) }}"
               class="text-decoration-none fw-semibold">
                {{ $member->full_name }}
                @if ($member->life_status === 'deceased') <span class="text-muted">†</span> @endif
            </a>
        </td>
        <td data-label="{{ __('Father') }}">{{ $member->father_display_name }}</td>
        <td data-label="{{ __('Gender') }}">
            <span class="ft-badge-{{ $member->gender }}">{{ ucfirst($member->gender) }}</span>
        </td>
        <td data-label="{{ __('DOB / Age') }}">
            @if ($member->date_of_birth)
                {{ formatDate($member->date_of_birth) }}
                @if ($member->age !== null)
                    <span class="text-muted small">({{ $member->age }})</span>
                @endif
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td data-label="{{ __('Status') }}">
            <span class="ft-badge-{{ $member->life_status }}">{{ ucfirst($member->life_status) }}</span>
        </td>
        <td data-label="{{ __('Marital') }}">{{ ucfirst($member->marital_status) }}</td>
        <td data-label="{{ __('Contact') }}">{{ $member->contact_number ?? '—' }}</td>
        <td data-label="{{ __('Occupation') }}">{{ $member->occupation ?? '—' }}</td>
    </tr>
@empty
    <tr class="ft-row-empty">
        <td colspan="8">
            @include('familytree::partials.empty-state', [
                'icon' => 'bi-people', 'title' => __('No members match the filters'),
            ])
        </td>
    </tr>
@endforelse