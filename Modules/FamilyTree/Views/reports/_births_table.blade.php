<div data-count="{{ $members->count() }}" class="d-none" id="r-count-data"></div>

@forelse ($members as $member)
    <tr>
        <td data-label="{{ __('Name') }}" class="ft-cell-name">
            <a href="{{ route('familytree.family.members.show', [$family, $member]) }}"
               class="text-decoration-none fw-semibold">{{ $member->full_name }}</a>
        </td>
        <td data-label="{{ __('Date of Birth') }}">{{ formatDate($member->date_of_birth) }}</td>
        <td data-label="{{ __('Place') }}">{{ $member->place_of_birth ?? '—' }}</td>
        <td data-label="{{ __('Age') }}">
            {{ $member->age !== null ? $member->age . ' ' . __('yrs') : '—' }}
        </td>
        <td data-label="{{ __('Gender') }}">
            <span class="ft-badge-{{ $member->gender }}">{{ ucfirst($member->gender) }}</span>
        </td>
        <td data-label="{{ __('Father') }}">{{ $member->father_display_name }}</td>
    </tr>
@empty
    <tr class="ft-row-empty">
        <td colspan="6">
            @include('familytree::partials.empty-state', [
                'icon' => 'bi-cake2', 'title' => __('No birth records found'),
            ])
        </td>
    </tr>
@endforelse