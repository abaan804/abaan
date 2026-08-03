<?php

namespace Modules\VideoDownloader\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DownloadHistoryExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(protected Collection $downloads)
    {
    }

    public function collection(): Collection
    {
        return $this->downloads;
    }

    public function headings(): array
    {
        return [
            '#',
            __('Title'),
            __('Platform'),
            __('Quality'),
            __('Format'),
            __('Status'),
            __('File Size'),
            __('User'),
            __('Submitted'),
            __('Completed'),
        ];
    }

    public function map($dl): array
    {
        static $i = 0;
        $i++;
        return [
            $i,
            $dl->video_title ?? $dl->original_url,
            ucfirst($dl->platform ?? '—'),
            $dl->selected_quality ?? '—',
            strtoupper($dl->selected_format_ext ?? '—'),
            ucfirst($dl->status),
            $dl->formatted_file_size,
            $dl->user?->name ?? '—',
            $dl->created_at?->format('Y-m-d H:i') ?? '',
            $dl->completed_at?->format('Y-m-d H:i') ?? '',
        ];
    }

    public function title(): string
    {
        return __('Download History');
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}