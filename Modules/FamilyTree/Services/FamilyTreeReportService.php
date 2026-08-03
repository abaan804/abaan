<?php

namespace Modules\FamilyTree\Services;

use Illuminate\Support\Collection;
use Modules\FamilyTree\Models\FtEvent;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMarriage;
use Modules\FamilyTree\Models\FtMember;

class FamilyTreeReportService
{
    public function membersReport(FtFamily $family, array $filters = []): Collection
    {
        $query = FtMember::where('family_id', $family->id);

        if (! empty($filters['gender'])) $query->where('gender', $filters['gender']);
        if (! empty($filters['life_status'])) $query->where('life_status', $filters['life_status']);
        if (! empty($filters['marital_status'])) $query->where('marital_status', $filters['marital_status']);
        if (! empty($filters['occupation'])) $query->where('occupation', 'like', '%' . $filters['occupation'] . '%');
        if (! empty($filters['blood_group'])) $query->where('blood_group', $filters['blood_group']);

        return $query->with(['father', 'mother'])->orderBy('full_name')->get();
    }

    public function birthReport(FtFamily $family, array $filters = []): Collection
    {
        $query = FtMember::where('family_id', $family->id)
            ->whereNotNull('date_of_birth');

        if (! empty($filters['year'])) {
            $query->whereYear('date_of_birth', $filters['year']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('date_of_birth', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('date_of_birth', '<=', $filters['date_to']);
        }

        return $query->orderBy('date_of_birth')->get();
    }

    public function deathReport(FtFamily $family, array $filters = []): Collection
    {
        $query = FtMember::where('family_id', $family->id)
            ->where('life_status', 'deceased')
            ->whereNotNull('date_of_death');

        if (! empty($filters['year'])) {
            $query->whereYear('date_of_death', $filters['year']);
        }

        return $query->orderBy('date_of_death')->get();
    }

    public function marriageReport(FtFamily $family, array $filters = []): Collection
    {
        $memberIds = FtMember::where('family_id', $family->id)->pluck('id');

        $query = FtMarriage::whereIn('husband_id', $memberIds)
            ->with(['husband', 'wife']);

        if (! empty($filters['status'])) $query->where('status', $filters['status']);
        if (! empty($filters['year'])) $query->whereYear('marriage_date', $filters['year']);

        return $query->orderByDesc('marriage_date')->get();
    }

    public function eventsReport(FtFamily $family, array $filters = []): Collection
    {
        $query = FtEvent::where('family_id', $family->id)
            ->where('status', 'active')
            ->with(['member']);

        if (! empty($filters['event_type'])) $query->where('event_type', $filters['event_type']);
        if (! empty($filters['date_from'])) $query->whereDate('event_date', '>=', $filters['date_from']);
        if (! empty($filters['date_to'])) $query->whereDate('event_date', '<=', $filters['date_to']);

        return $query->orderByDesc('event_date')->get();
    }

    public function ageDistribution(FtFamily $family): array
    {
        $members = FtMember::where('family_id', $family->id)
            ->where('life_status', 'living')
            ->whereNotNull('date_of_birth')
            ->get();

        $buckets = ['0-10' => 0, '11-20' => 0, '21-30' => 0, '31-40' => 0, '41-50' => 0, '51-60' => 0, '61+' => 0];

        foreach ($members as $m) {
            $age = $m->age ?? 0;
            match (true) {
                $age <= 10 => $buckets['0-10']++,
                $age <= 20 => $buckets['11-20']++,
                $age <= 30 => $buckets['21-30']++,
                $age <= 40 => $buckets['31-40']++,
                $age <= 50 => $buckets['41-50']++,
                $age <= 60 => $buckets['51-60']++,
                default => $buckets['61+']++,
            };
        }

        return $buckets;
    }

    public function missingInfoReport(FtFamily $family): Collection
    {
        return FtMember::where('family_id', $family->id)
            ->where(fn ($q) => $q
                ->whereNull('date_of_birth')
                ->orWhereNull('contact_number')
                ->orWhereNull('cnic')
                ->orWhereNull('father_id')
            )
            ->orderBy('full_name')
            ->get()
            ->map(fn ($m) => [
                'member' => $m,
                'missing' => array_filter([
                    'date_of_birth' => ! $m->date_of_birth ? __('Date of Birth') : null,
                    'contact_number' => ! $m->contact_number ? __('Contact Number') : null,
                    'cnic' => ! $m->cnic ? __('CNIC') : null,
                    'father' => ! $m->father_id ? __('Father Link') : null,
                ]),
            ]);
    }
}