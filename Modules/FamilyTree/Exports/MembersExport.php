<?php

namespace Modules\FamilyTree\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Services\FamilyTreeReportService;

class MembersExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(protected FtFamily $family, protected array $filters = [])
    {
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return app(FamilyTreeReportService::class)->membersReport($this->family, $this->filters);
    }

    public function headings(): array
    {
        return [
            '#',
            __('Full Name'),
            __('Father'),
            __('Mother'),
            __('Gender'),
            __('Date of Birth'),
            __('Age'),
            __('Place of Birth'),
            __('Life Status'),
            __('Date of Death'),
            __('Marital Status'),
            __('CNIC'),
            __('Passport'),
            __('Contact'),
            __('WhatsApp'),
            __('Email'),
            __('Occupation'),
            __('Education'),
            __('Blood Group'),
            __('Religion'),
            __('Nationality'),
            __('Current Address'),
        ];
    }

    protected static int $rowNum = 0;

    public function map($m): array
    {
        static $i = 0;
        $i++;
        return [
            $i,
            $m->full_name . ($m->life_status === 'deceased' ? ' †' : ''),
            $m->father_display_name,
            $m->mother_display_name,
            ucfirst($m->gender),
            $m->date_of_birth?->format('Y-m-d') ?? '',
            $m->age ?? '',
            $m->place_of_birth ?? '',
            ucfirst($m->life_status),
            $m->date_of_death?->format('Y-m-d') ?? '',
            ucfirst($m->marital_status),
            $m->cnic ?? '',
            $m->passport_number ?? '',
            $m->contact_number ?? '',
            $m->whatsapp_number ?? '',
            $m->email ?? '',
            $m->occupation ?? '',
            $m->education ?? '',
            $m->blood_group ?? '',
            $m->religion ?? '',
            $m->nationality ?? '',
            $m->current_address ?? '',
        ];
    }

    public function title(): string
    {
        return __('Members');
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}