<?php

namespace Modules\Masjid\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeason;

class MasjidSeasonRepository
{
    public function paginate(MasjidMosque $mosque, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MasjidSeason::where('mosque_id', $mosque->id);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['frequency'])) {
            $query->where('frequency', $filters['frequency']);
        }

        return $query->orderByDesc('start_date')->paginate($perPage)->withQueryString();
    }

    public function activeSeasons(MasjidMosque $mosque): Collection
    {
        return MasjidSeason::where('mosque_id', $mosque->id)
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->get();
    }

    public function openAutoAssignSeasons(MasjidMosque $mosque): Collection
    {
        return MasjidSeason::where('mosque_id', $mosque->id)
            ->where('status', 'active')
            ->where('auto_assign', true)
            ->get();
    }

    public function findForMosque(int $seasonId, MasjidMosque $mosque): MasjidSeason
    {
        return MasjidSeason::where('mosque_id', $mosque->id)
            ->findOrFail($seasonId);
    }
}