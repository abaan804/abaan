@forelse ($categories as $category)
    <tr data-category-id="{{ $category->id }}">
        <td data-label="{{ __('Name') }}" class="ledger-cell-name">{{ $category->name }}</td>
        <td data-label="{{ __('Type') }}">
            <span class="badge ledger-badge-{{ $category->type }}">{{ ucfirst($category->type) }}</span>
        </td>
        <td data-label="{{ __('Status') }}">
            <span class="badge bg-{{ $category->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($category->status) }}</span>
        </td>
        <td class="ledger-cell-actions">
            @unless ($category->is_default)
                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-category" data-id="{{ $category->id }}">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-category" data-id="{{ $category->id }}" data-name="{{ $category->name }}">
                    <i class="bi bi-trash"></i>
                </button>
            @else
                <span class="badge bg-light text-dark border">{{ __('Default') }}</span>
            @endunless
        </td>
    </tr>
@empty
    <tr class="ledger-row-empty">
        <td colspan="4">
            @include('ledger::partials.empty-state', [
                'icon' => 'bi-tags',
                'title' => __('No categories found'),
                'description' => __('Add an income or expense category to get started.'),
            ])
        </td>
    </tr>
@endforelse

@if ($categories->hasPages())
    <tr class="ledger-row-empty">
        <td colspan="4">
            <div class="d-flex justify-content-center py-2" id="categories-pagination">{{ $categories->links('pagination::bootstrap-5') }}</div>
        </td>
    </tr>
@endif