<?php

namespace Modules\FamilyTree\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\FamilyTree\Models\FtFamily;

class FtFamilyRepository
{
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = FtFamily::where('company_id', $companyId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
            );
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->withCount('members')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allForCompany(int $companyId): Collection
    {
        return FtFamily::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function findForCompany(int $familyId, int $companyId): FtFamily
    {
        return FtFamily::where('company_id', $companyId)->findOrFail($familyId);
    }

    public function dashboardStats(int $companyId): array
    {
        $families = FtFamily::where('company_id', $companyId)->pluck('id');

        return [
            'total_families' => $families->count(),
        ];
    }
}