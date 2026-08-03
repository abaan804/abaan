<?php

namespace Modules\FamilyTree\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FamilyReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected array $headingsMap = [
        'births' => ['#', 'Full Name', 'Date of Birth', 'Place of Birth', 'Age', 'Gender', 'Father'],
        'deaths' => ['#', 'Full Name', 'Date of Birth', 'Date of Death', 'Age at Death', 'Burial Place', 'Father'],
        'marriages' => ['#', 'Husband', 'Wife', 'Marriage Date', 'Marriage Place', 'Type', 'Status', 'Divorce Date', 'Children'],
    ];

    public function __construct(
        protected string $type,
        protected Collection $data
    ) {
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headingsMap[$this->type] ?? ['#', 'Value'];
    }

    protected static int $i = 0;

    public function map($row): array
    {
        static $i = 0;
        $i++;

        return match ($this->type) {
            'births' => [
                $i,
                $row->full_name . ($row->life_status === 'deceased' ? ' †' : ''),
                $row->date_of_birth?->format('Y-m-d') ?? '',
                $row->place_of_birth ?? '',
                $row->age ?? '',
                ucfirst($row->gender),
                $row->father_display_name,
            ],
            'deaths' => [
                $i,
                $row->full_name . ' †',
                $row->date_of_birth?->format('Y-m-d') ?? '',
                $row->date_of_death?->format('Y-m-d') ?? '',
                ($row->date_of_birth && $row->date_of_death)
                    ? $row->date_of_birth->diffInYears($row->date_of_death) . ' yrs'
                    : '',
                $row->burial_place ?? '',
                $row->father_display_name,
            ],
            'marriages' => [
                $i,
                $row->husband?->full_name ?? '',
                $row->wife?->full_name ?? '',
                $row->marriage_date?->format('Y-m-d') ?? '',
                $row->marriage_place ?? '',
                ucfirst($row->marriage_type),
                ucfirst($row->status),
                $row->divorce_date?->format('Y-m-d') ?? '',
                $row->children()->count(),
            ],
            default => [$i, ''],
        };
    }

    public function title(): string
    {
        return ucfirst($this->type);
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}