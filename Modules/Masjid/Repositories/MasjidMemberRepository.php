<?php

namespace Modules\Masjid\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Masjid\Models\MasjidMember;
use Modules\Masjid\Models\MasjidMosque;

class MasjidMemberRepository
{
    public function paginate(MasjidMosque $mosque, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MasjidMember::where('mosque_id', $mosque->id);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('cnic', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sort = $filters['sort'] ?? 'name';
        $dir = $filters['dir'] ?? 'asc';
        $query->orderBy($sort, $dir);

        return $query->paginate($perPage)->withQueryString();
    }

    public function activeMembers(MasjidMosque $mosque): Collection
    {
        return MasjidMember::where('mosque_id', $mosque->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function findForMosque(int $memberId, MasjidMosque $mosque): MasjidMember
    {
        return MasjidMember::where('mosque_id', $mosque->id)
            ->findOrFail($memberId);
    }
}