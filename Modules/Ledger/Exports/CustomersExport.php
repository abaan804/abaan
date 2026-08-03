<?php

namespace Modules\Ledger\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Ledger\Models\LedgerCustomer;
use Modules\Ledger\Services\LedgerBalanceService;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    protected LedgerBalanceService $balanceService;

    public function __construct(protected int $companyId, protected array $filters = [])
    {
        $this->balanceService = app(LedgerBalanceService::class);
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $query = LedgerCustomer::where('company_id', $this->companyId);

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%"));
        }

        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [__('Name'), __('Mobile'), __('Email'), __('City'), __('Address'), __('Opening Balance'), __('Current Balance'), __('Status')];
    }

    public function map($customer): array
    {
        return [
            $customer->name,
            $customer->mobile,
            $customer->email,
            $customer->city,
            $customer->address,
            $customer->opening_balance,
            $this->balanceService->customerBalance($customer),
            ucfirst($customer->status),
        ];
    }
}