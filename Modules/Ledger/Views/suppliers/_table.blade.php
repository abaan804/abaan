@forelse ($suppliers as $supplier)
    <tr data-supplier-id="{{ $supplier->id }}">
        <td data-label="{{ __('Name') }}" class="ledger-cell-name">
            <div class="d-flex align-items-center gap-2">
                @if ($supplier->photo)
                    <img src="{{ asset('storage/' . $supplier->photo) }}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class="bi bi-truck text-muted"></i>
                    </div>
                @endif
                <a href="{{ route('ledger.suppliers.show', [$supplier, 'standalone' => 1]) }}" class="text-decoration-none">{{ $supplier->name }}</a>
            </div>
        </td>
        <td data-label="{{ __('Mobile') }}">{{ $supplier->mobile ?? '—' }}</td>
        <td data-label="{{ __('City') }}">{{ $supplier->city ?? '—' }}</td>
        <td data-label="{{ __('Status') }}">
            <span class="badge bg-{{ $supplier->status === 'active' ? 'success' : 'secondary' }}">
                {{ ucfirst($supplier->status) }}
            </span>
        </td>
        <td class="ledger-cell-actions">
            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-supplier" data-id="{{ $supplier->id }}">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-supplier" data-id="{{ $supplier->id }}" data-name="{{ $supplier->name }}">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
@empty
    <tr class="ledger-row-empty">
        <td colspan="5">
            @include('ledger::partials.empty-state', [
                'icon' => 'bi-truck',
                'title' => __('No suppliers found'),
                'description' => __('Add your first supplier to get started.'),
            ])
        </td>
    </tr>
@endforelse

@if ($suppliers->hasPages())
    <tr class="ledger-row-empty">
        <td colspan="5">
            <div class="d-flex justify-content-center py-2" id="suppliers-pagination">
                {{ $suppliers->links() }}
            </div>
        </td>
    </tr>
@endif