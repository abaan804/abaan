<?php

namespace Modules\FamilyTree\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Services\FamilyTreeReportService;

class EventsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(
        protected FtFamily $family,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return app(FamilyTreeReportService::class)->eventsReport($this->family, $this->filters);
    }

    public function headings(): array
    {
        return [
            '#',
            __('Member'),
            __('Event Type'),
            __('Event Title'),
            __('Date'),
            __('Location'),
            __('Description'),
        ];
    }

    public function map($event): array
    {
        static $i = 0;
        $i++;
        return [
            $i,
            $event->member?->full_name ?? '',
            ucfirst(str_replace('_', ' ', $event->event_type)),
            $event->event_title ?? '',
            $event->event_date?->format('Y-m-d') ?? '',
            $event->location ?? '',
            $event->description ?? '',
        ];
    }

    public function title(): string
    {
        return __('Events');
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}