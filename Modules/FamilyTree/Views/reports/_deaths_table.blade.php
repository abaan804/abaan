<div data-count="{{ $members->count() }}" class="d-none" id="r-count-data"></div>

@forelse ($members as $member)
    @php
        $ageAtDeath = ($member->date_of_birth && $member->date_of_death)
            ? $member->date_of_birth->diffInYears($member->date_of_death)
            : null;
    @endphp
    <tr>
        <td data-label="{{ __('Name') }}" class="ft-cell-name">
            <a href="{{ route('familytree.family.members.show', [$family, $member]) }}"
               class="text-decoration-none fw-semibold">
                {{ $member->full_name }} <span class="text-muted">†</span>
            </a>
        </td>
        <td data-label="{{ __('Date of Birth') }}">
            {{ $member->date_of_birth ? formatDate($member->date_of_birth) : '—' }}
        </td>
        <td data-label="{{ __('Date of Death') }}">{{ formatDate($member->date_of_death) }}</td>
        <td data-label="{{ __('Age') }}">
            {{ $ageAtDeath !== null ? $ageAtDeath . ' ' . __('yrs') : '—' }}
        </td>
        <td data-label="{{ __('Burial Place') }}">{{ $member->burial_place ?? '—' }}</td>
        <td data-label="{{ __('Father') }}">{{ $member->father_display_name }}</td>
    </tr>
@empty
    <tr class="ft-row-empty">
        <td colspan="6">
            @include('familytree::partials.empty-state', [
                'icon' => 'bi-moon-stars', 'title' => __('No death records found'),
            ])
        </td>
    </tr>
@endforelse