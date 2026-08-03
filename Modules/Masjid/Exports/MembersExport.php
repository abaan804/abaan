<?php

namespace Modules\Masjid\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Masjid\Models\MasjidMember;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Services\MasjidBalanceService;

class MembersExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected MasjidBalanceService $balanceService;

    public function __construct(
        protected MasjidMosque $mosque,
        protected array $filters = []
    ) {
        $this->balanceService = app(MasjidBalanceService::class);
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $query = MasjidMember::where('mosque_id', $this->mosque->id);

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('cnic', 'like', "%{$search}%")
            );
        }

        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            __('Name'),
            __('Father Name'),
            __('CNIC'),
            __('Mobile'),
            __('WhatsApp'),
            __('Email'),
            __('City'),
            __('Occupation'),
            __('Joining Date'),
            __('Status'),
            __('Total Due'),
            __('Total Paid'),
            __('Balance'),
        ];
    }

    public function map($member): array
    {
        $summary = $this->balanceService->memberSummary($member);

        return [
            $member->name,
            $member->father_name ?? '',
            $member->cnic ?? '',
            $member->mobile,
            $member->whatsapp ?? '',
            $member->email ?? '',
            $member->city ?? '',
            $member->occupation ?? '',
            $member->joining_date?->format('Y-m-d') ?? '',
            ucfirst($member->status),
            $summary['total_due'],
            $summary['total_paid'],
            $summary['balance'],
        ];
    }

    public function title(): string
    {
        return __('Members');
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}