<?php

namespace Modules\Masjid\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DonationsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(protected Collection $donations)
    {
    }

    public function collection(): Collection
    {
        return $this->donations;
    }

    public function headings(): array
    {
        return [
            '#',
            __('Type'),
            __('Donor Name'),
            __('Mobile'),
            __('Address'),
            __('Purpose'),
            __('Day / Description'),
            __('Season'),
            __('Receipt No'),
            __('Date'),
            __('Amount'),
        ];
    }

    public function map($d): array
    {
        static $i = 0;
        $i++;
        return [
            $i,
            $d->type === 'named' ? __('Named') : __('Anonymous'),
            $d->donor_name ?? '—',
            $d->donor_mobile ?? '—',
            $d->donor_address ?? '—',
            $d->purpose ?? '—',
            $d->day_description ?? '—',
            $d->season?->name ?? '—',
            $d->receipt_no ?? '—',
            $d->donation_date?->format('Y-m-d') ?? '',
            $d->amount,
        ];
    }

    public function title(): string
    {
        return __('Donations');
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}