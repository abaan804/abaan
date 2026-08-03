<div data-count="{{ $marriages->count() }}" class="d-none" id="r-count-data"></div>

@forelse ($marriages as $marriage)
    <tr>
        <td data-label="{{ __('Husband') }}" class="ft-cell-name" style="color:var(--ft-primary);">
            {{ $marriage->husband?->full_name ?? '—' }}
        </td>
        <td data-label="{{ __('Wife') }}" style="color:var(--ft-female);">
            {{ $marriage->wife?->full_name ?? '—' }}
        </td>
        <td data-label="{{ __('Date') }}">
            {{ $marriage->marriage_date ? formatDate($marriage->marriage_date) : '—' }}
        </td>
        <td data-label="{{ __('Type') }}">
            <span class="badge bg-light text-dark border">{{ ucfirst($marriage->marriage_type) }}</span>
        </td>
        <td data-label="{{ __('Status') }}">
            <span class="badge bg-{{ $marriage->status === 'active' ? 'success' : ($marriage->status === 'divorced' ? 'danger' : 'secondary') }}">
                {{ ucfirst($marriage->status) }}
            </span>
        </td>
        <td data-label="{{ __('Children') }}">{{ $marriage->children()->count() }}</td>
    </tr>
@empty
    <tr class="ft-row-empty">
        <td colspan="6">
            @include('familytree::partials.empty-state', [
                'icon' => 'bi-heart', 'title' => __('No marriage records found'),
            ])
        </td>
    </tr>
@endforelse