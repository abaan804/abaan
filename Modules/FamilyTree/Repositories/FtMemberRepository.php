<?php

namespace Modules\FamilyTree\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMember;

class FtMemberRepository
{
    public function paginate(FtFamily $family, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = FtMember::where('family_id', $family->id);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($q) => $q
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('father_name_text', 'like', "%{$search}%")
                ->orWhere('cnic', 'like', "%{$search}%")
                ->orWhere('contact_number', 'like', "%{$search}%")
                ->orWhere('occupation', 'like', "%{$search}%")
            );
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (! empty($filters['life_status'])) {
            $query->where('life_status', $filters['life_status']);
        }

        if (! empty($filters['marital_status'])) {
            $query->where('marital_status', $filters['marital_status']);
        }

        $sort = $filters['sort'] ?? 'full_name';
        $dir = $filters['dir'] ?? 'asc';
        $query->orderBy($sort, $dir);

        return $query->paginate($perPage)->withQueryString();
    }

    public function allForFamily(int $familyId): Collection
    {
        return FtMember::where('family_id', $familyId)
            ->orderBy('full_name')
            ->get();
    }

    public function roots(int $familyId): Collection
    {
        return FtMember::where('family_id', $familyId)
            ->whereNull('father_id')
            ->whereNull('mother_id')
            ->orderBy('full_name')
            ->get();
    }

    public function findForFamily(int $memberId, int $familyId): FtMember
    {
        return FtMember::where('family_id', $familyId)->findOrFail($memberId);
    }

    /**
     * Members with a birthday in the next N days — used for dashboard + reminders.
     */
    public function upcomingBirthdays(int $companyId, int $days = 30): Collection
    {
        return FtMember::where('company_id', $companyId)
            ->whereNotNull('date_of_birth')
            ->where('life_status', 'living')
            ->get()
            ->filter(function ($member) use ($days) {
                $birthday = $member->date_of_birth->setYear(now()->year);
                if ($birthday->isPast()) {
                    $birthday = $birthday->addYear();
                }
                return $birthday->diffInDays(now()) <= $days;
            })
            ->sortBy(fn ($m) => $m->date_of_birth->setYear(now()->year)->isPast()
                ? $m->date_of_birth->setYear(now()->year + 1)
                : $m->date_of_birth->setYear(now()->year))
            ->values();
    }

    /**
     * Children of a specific member — efficient DB query
     * rather than the in-memory model helper.
     */
    public function childrenOf(FtMember $member): Collection
    {
        return FtMember::where(function ($q) use ($member) {
            $q->where('father_id', $member->id)
              ->orWhere('mother_id', $member->id);
        })->orderBy('date_of_birth')->get();
    }

    /**
     * All descendants of a member — recursive BFS, up to a max depth.
     */
    public function descendants(FtMember $member, int $maxDepth = 10): Collection
    {
        $all = collect();
        $queue = collect([$member]);
        $depth = 0;

        while ($queue->isNotEmpty() && $depth < $maxDepth) {
            $nextQueue = collect();
            foreach ($queue as $current) {
                $children = $this->childrenOf($current);
                $all = $all->merge($children);
                $nextQueue = $nextQueue->merge($children);
            }
            $queue = $nextQueue;
            $depth++;
        }

        return $all->unique('id')->values();
    }

    /**
     * All ancestors of a member — walks up father_id/mother_id links.
     */
    public function ancestors(FtMember $member, int $maxDepth = 10): Collection
    {
        $all = collect();
        $queue = collect([$member]);
        $depth = 0;

        while ($queue->isNotEmpty() && $depth < $maxDepth) {
            $nextQueue = collect();
            foreach ($queue as $current) {
                if ($current->father_id) {
                    $father = FtMember::find($current->father_id);
                    if ($father && ! $all->contains('id', $father->id)) {
                        $all->push($father);
                        $nextQueue->push($father);
                    }
                }
                if ($current->mother_id) {
                    $mother = FtMember::find($current->mother_id);
                    if ($mother && ! $all->contains('id', $mother->id)) {
                        $all->push($mother);
                        $nextQueue->push($mother);
                    }
                }
            }
            $queue = $nextQueue;
            $depth++;
        }

        return $all->values();
    }

    public function dashboardStats(int $companyId): array
    {
        $base = FtMember::where('company_id', $companyId);

        return [
            'total_members' => (clone $base)->count(),
            'living' => (clone $base)->where('life_status', 'living')->count(),
            'deceased' => (clone $base)->where('life_status', 'deceased')->count(),
            'male' => (clone $base)->where('gender', 'male')->count(),
            'female' => (clone $base)->where('gender', 'female')->count(),
            'married' => (clone $base)->where('marital_status', 'married')->count(),
            'unmarried' => (clone $base)->where('marital_status', 'unmarried')->count(),
        ];
    }
}