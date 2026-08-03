<?php

namespace Modules\FamilyTree\Repositories;

use Illuminate\Support\Collection;
use Modules\FamilyTree\Models\FtMarriage;
use Modules\FamilyTree\Models\FtMember;

class FtMarriageRepository
{
    public function forMember(FtMember $member): Collection
    {
        return FtMarriage::where(function ($q) use ($member) {
            $q->where('husband_id', $member->id)
              ->orWhere('wife_id', $member->id);
        })
        ->with(['husband', 'wife'])
        ->orderByDesc('marriage_date')
        ->get();
    }

    public function activeForMember(FtMember $member): Collection
    {
        return FtMarriage::where(function ($q) use ($member) {
            $q->where('husband_id', $member->id)
              ->orWhere('wife_id', $member->id);
        })
        ->where('status', 'active')
        ->with(['husband', 'wife'])
        ->get();
    }

    public function forFamily(int $familyId): Collection
    {
        $memberIds = FtMember::where('family_id', $familyId)->pluck('id');

        return FtMarriage::whereIn('husband_id', $memberIds)
            ->orWhereIn('wife_id', $memberIds)
            ->with(['husband', 'wife'])
            ->orderByDesc('marriage_date')
            ->get()
            ->unique('id')
            ->values();
    }

    public function alreadyMarried(int $husbandId, int $wifeId): bool
    {
        return FtMarriage::where('husband_id', $husbandId)
            ->where('wife_id', $wifeId)
            ->where('status', 'active')
            ->exists();
    }
}