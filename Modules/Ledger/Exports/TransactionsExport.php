<?php

namespace Modules\Ledger\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Ledger\Services\LedgerReportService;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected int $companyId, protected array $filters = [])
    {
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return app(LedgerReportService::class)
            ->filteredQuery($this->companyId, $this->filters)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return [__('Date'), __('Type'), __('Customer'), __('Supplier'), __('Category'), __('Payment Method'), __('Amount'), __('Reference No'), __('Notes')];
    }

    public function map($tx): array
    {
        return [
            $tx->transaction_date->format('Y-m-d'),
            ucfirst(str_replace('_', ' ', $tx->type)),
            $tx->customer?->name,
            $tx->supplier?->name,
            $tx->category?->name,
            $tx->paymentMethod?->name,
            $tx->amount,
            $tx->reference_no,
            $tx->notes,
        ];
    }
}