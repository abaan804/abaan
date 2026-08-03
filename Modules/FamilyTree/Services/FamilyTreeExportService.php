<?php

namespace Modules\FamilyTree\Services;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Modules\FamilyTree\Exports\MembersExport;
use Modules\FamilyTree\Exports\FamilyReportExport;
use Modules\FamilyTree\Models\FtFamily;

class FamilyTreeExportService
{
    public function exportMembers(FtFamily $family, array $filters, string $format): \Symfony\Component\HttpFoundation\Response
    {
        $export = new MembersExport($family, $filters);
        $filename = 'members-' . \Illuminate\Support\Str::slug($family->name) . '-' . now()->format('Y-m-d');

        return match ($format) {
            'csv' => Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }

    public function exportReport(string $reportType, FtFamily $family, Collection $data, string $format): \Symfony\Component\HttpFoundation\Response
    {
        $export = new FamilyReportExport($reportType, $data);
        $filename = "{$reportType}-" . \Illuminate\Support\Str::slug($family->name) . '-' . now()->format('Y-m-d');

        return match ($format) {
            'csv' => Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }
}