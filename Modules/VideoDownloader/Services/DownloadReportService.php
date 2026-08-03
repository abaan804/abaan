<?php

namespace Modules\VideoDownloader\Services;

use Illuminate\Support\Collection;
use Modules\VideoDownloader\Models\VdDownload;

class DownloadReportService
{
    public function history(int $companyId, array $filters = []): Collection
    {
        $query = VdDownload::where('company_id', $companyId)
            ->with('user');

        if (! empty($filters['status']))      $query->where('status', $filters['status']);
        if (! empty($filters['platform']))    $query->where('platform', $filters['platform']);
        if (! empty($filters['format_ext']))  $query->where('selected_format_ext', $filters['format_ext']);
        if (! empty($filters['date_from']))   $query->dateFrom($filters['date_from']);
        if (! empty($filters['date_to']))     $query->dateTo($filters['date_to']);
        if (! empty($filters['search']))      $query->search($filters['search']);

        return $query->orderByDesc('created_at')->get();
    }

    public function dailyStats(int $companyId, int $days = 30): Collection
    {
        return VdDownload::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "failed"    THEN 1 ELSE 0 END) as failed')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();
    }

    public function usageStats(int $companyId): array
    {
        $base = VdDownload::where('company_id', $companyId);

        return [
            'total_downloads'    => (clone $base)->count(),
            'successful'         => (clone $base)->completed()->count(),
            'failed'             => (clone $base)->failed()->count(),
            'total_storage_bytes'=> (clone $base)->completed()->sum('file_size'),
            'avg_file_size'      => (clone $base)->completed()->avg('file_size'),
            'most_used_platform' => (clone $base)->selectRaw('platform, COUNT(*) as c')
                ->groupBy('platform')->orderByDesc('c')->value('platform'),
            'most_used_format'   => (clone $base)->selectRaw('selected_format_ext, COUNT(*) as c')
                ->groupBy('selected_format_ext')->orderByDesc('c')->value('selected_format_ext'),
            'audio_only_count'   => (clone $base)->audioOnly()->count(),
        ];
    }
}