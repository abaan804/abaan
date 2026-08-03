@forelse ($customers as $customer)
    <tr data-customer-id="{{ $customer->id }}">
        <td data-label="{{ __('Name') }}" class="ledger-cell-name">
            <div class="d-flex align-items-center gap-2">
                @if ($customer->photo)
                    <img src="{{ asset('storage/' . $customer->photo) }}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class="bi bi-person text-muted"></i>
                    </div>
                @endif
                <a href="{{ route('ledger.customers.show', [$customer, 'standalone' => 1]) }}" class="text-decoration-none">{{ $customer->name }}</a>
            </div>
        </td>
        <td data-label="{{ __('Mobile') }}">{{ $customer->mobile ?? '—' }}</td>
        <td data-label="{{ __('City') }}">{{ $customer->city ?? '—' }}</td>
        <td data-label="{{ __('Status') }}">
            <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'secondary' }}">
                {{ ucfirst($customer->status) }}
            </span>
        </td>
        <td class="ledger-cell-actions">
            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-customer" data-id="{{ $customer->id }}">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-customer" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
@empty
    <tr class="ledger-row-empty">
        <td colspan="5">
            @include('ledger::partials.empty-state', [
                'icon' => 'bi-people',
                'title' => __('No customers found'),
                'description' => __('Add your first customer to get started.'),
            ])
        </td>
    </tr>
@endforelse

@if ($customers->hasPages())
    <tr class="ledger-row-empty">
        <td colspan="5">
            <div class="d-flex justify-content-center py-2" id="customers-pagination">
                {{ $customers->links() }}
            </div>
        </td>
    </tr>
@endif