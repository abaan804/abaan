<?php

namespace Modules\Masjid\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Services\MasjidReportService;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(
        protected MasjidMosque $mosque,
        protected array $filters = []
    ) {
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return app(MasjidReportService::class)
            ->filteredPayments($this->mosque, $this->filters)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            __('Receipt No'),
            __('Member'),
            __('Mobile'),
            __('Season'),
            __('Payment Date'),
            __('Method'),
            __('Reference No'),
            __('Amount'),
            __('Received By'),
            __('Notes'),
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->receipt_no ?? '',
            $payment->member?->name ?? '',
            $payment->member?->mobile ?? '',
            $payment->season?->name ?? '',
            $payment->payment_date?->format('Y-m-d') ?? '',
            ucfirst($payment->payment_method),
            $payment->reference_no ?? '',
            $payment->amount_paid,
            $payment->receivedBy?->name ?? '',
            $payment->notes ?? '',
        ];
    }

    public function title(): string
    {
        return __('Payments');
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}