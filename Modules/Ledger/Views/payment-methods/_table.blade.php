@forelse ($methods as $method)
    <tr data-method-id="{{ $method->id }}">
        <td data-label="{{ __('Name') }}" class="ledger-cell-name">{{ $method->name }}</td>
        <td data-label="{{ __('Status') }}">
            <span class="badge bg-{{ $method->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($method->status) }}</span>
        </td>
        <td class="ledger-cell-actions">
            @unless ($method->is_default)
                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-method" data-id="{{ $method->id }}">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-method" data-id="{{ $method->id }}" data-name="{{ $method->name }}">
                    <i class="bi bi-trash"></i>
                </button>
            @else
                <span class="badge bg-light text-dark border">{{ __('Default') }}</span>
            @endunless
        </td>
    </tr>
@empty
    <tr class="ledger-row-empty">
        <td colspan="3">
            @include('ledger::partials.empty-state', [
                'icon' => 'bi-credit-card',
                'title' => __('No payment methods found'),
                'description' => __('Add a payment method like Cash, Bank, or Cheque.'),
            ])
        </td>
    </tr>
@endforelse

@if ($methods->hasPages())
    <tr class="ledger-row-empty">
        <td colspan="3">
            <div class="d-flex justify-content-center py-2" id="methods-pagination">{{ $methods->links() }}</div>
        </td>
    </tr>
@endif