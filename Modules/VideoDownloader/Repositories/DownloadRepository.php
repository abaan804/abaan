<?php

namespace Modules\VideoDownloader\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\VideoDownloader\Models\VdDownload;

class DownloadRepository
{
    public function paginate(
        int   $companyId,
        array $filters  = [],
        int   $perPage  = 20
    ): LengthAwarePaginator {
        $query = VdDownload::where('company_id', $companyId);

        // User filter — staff see only their own
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }

        if (! empty($filters['format_ext'])) {
            $query->where('selected_format_ext', $filters['format_ext']);
        }

        if (! empty($filters['date_from'])) {
            $query->dateFrom($filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->dateTo($filters['date_to']);
        }

        if (! empty($filters['audio_only'])) {
            $query->audioOnly();
        }

        $sort = $filters['sort'] ?? 'created_at';
        $dir  = $filters['dir']  ?? 'desc';
        $allowedSorts = ['created_at', 'video_title', 'status', 'completed_at', 'file_size'];
        if (! in_array($sort, $allowedSorts)) $sort = 'created_at';

        return $query->with(['user'])
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForCompany(int $downloadId, int $companyId): VdDownload
    {
        return VdDownload::where('company_id', $companyId)->findOrFail($downloadId);
    }

    public function findForUser(int $downloadId, int $companyId, int $userId): VdDownload
    {
        return VdDownload::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->findOrFail($downloadId);
    }

    // ── Dashboard stats ───────────────────────────────────────────────────────

    public function stats(int $companyId): array
    {
        $base = VdDownload::where('company_id', $companyId);

        return [
            'total'       => (clone $base)->count(),
            'completed'   => (clone $base)->completed()->count(),
            'failed'      => (clone $base)->failed()->count(),
            'pending'     => (clone $base)->pending()->count(),
            'processing'  => (clone $base)->processing()->count(),
            'cancelled'   => (clone $base)->cancelled()->count(),
            'storage_used'=> (clone $base)->completed()->sum('file_size'),
            'today'       => (clone $base)->whereDate('created_at', today())->count(),
            'this_week'   => (clone $base)->where('created_at', '>=', now()->startOfWeek())->count(),
        ];
    }

    public function recentForDashboard(int $companyId, int $limit = 10): Collection
    {
        return VdDownload::where('company_id', $companyId)
            ->with('user')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    public function platformBreakdown(int $companyId): Collection
    {
        return VdDownload::where('company_id', $companyId)
            ->selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->orderByDesc('count')
            ->get();
    }

    public function formatBreakdown(int $companyId): Collection
    {
        return VdDownload::where('company_id', $companyId)
            ->whereNotNull('selected_format_ext')
            ->selectRaw('selected_format_ext, COUNT(*) as count')
            ->groupBy('selected_format_ext')
            ->orderByDesc('count')
            ->get();
    }

    public function countActive(int $companyId): int
    {
        return VdDownload::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'processing'])
            ->count();
    }

    public function totalStorageUsed(int $companyId): int
    {
        return (int) VdDownload::where('company_id', $companyId)
            ->completed()
            ->sum('file_size');
    }
}