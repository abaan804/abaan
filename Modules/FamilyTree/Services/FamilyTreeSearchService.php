<?php

namespace Modules\FamilyTree\Services;

use Illuminate\Support\Collection;
use Modules\FamilyTree\Models\FtEvent;
use Modules\FamilyTree\Models\FtMember;

class FamilyTreeSearchService
{
    public function search(int $companyId, string $query, array $filters = []): array
    {
        if (mb_strlen($query) < 2) {
            return ['members' => collect(), 'events' => collect()];
        }

        $members = $this->searchMembers($companyId, $query, $filters);
        $events = mb_strlen($query) >= 3 ? $this->searchEvents($companyId, $query) : collect();

        return compact('members', 'events');
    }

    protected function searchMembers(int $companyId, string $query, array $filters = []): Collection
    {
        $q = FtMember::with('father')->where('company_id', $companyId)
            ->where(fn ($q) => $q
                ->where('full_name', 'like', "%{$query}%")
                ->orWhere('father_name_text', 'like', "%{$query}%")
                ->orWhere('cnic', 'like', "%{$query}%")
                ->orWhere('contact_number', 'like', "%{$query}%")
                ->orWhere('occupation', 'like', "%{$query}%")
                ->orWhere('place_of_birth', 'like', "%{$query}%")
                ->orWhere('current_address', 'like', "%{$query}%")
            );

        if (! empty($filters['gender'])) {
            $q->where('gender', $filters['gender']);
        }
        if (! empty($filters['life_status'])) {
            $q->where('life_status', $filters['life_status']);
        }
        if (! empty($filters['marital_status'])) {
            $q->where('marital_status', $filters['marital_status']);
        }
        if (! empty($filters['family_id'])) {
            $q->where('family_id', $filters['family_id']);
        }

        return $q->with('family')->orderBy('full_name')->take(30)->get();
    }

    protected function searchEvents(int $companyId, string $query): Collection
    {
        return FtEvent::where('company_id', $companyId)
            ->where(fn ($q) => $q
                ->where('event_title', 'like', "%{$query}%")
                ->orWhere('location', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
            )
            ->with(['member', 'family'])
            ->orderByDesc('event_date')
            ->take(10)
            ->get();
    }

    /**
     * Quick-search for the global search dropdown
     * (same pattern as EasyKhata's SearchController).
     */
    public function quickSearch(int $companyId, string $query): array
    {
        if (mb_strlen($query) < 2) return ['results' => []];

        $members = FtMember::where('company_id', $companyId)
            ->where(fn ($q) => $q
                ->where('full_name', 'like', "%{$query}%")
                ->orWhere('cnic', 'like', "%{$query}%")
                ->orWhere('contact_number', 'like', "%{$query}%")
            )
            ->with('family')
            ->take(8)->get();

        $results = $members->map(fn ($m) => [
            'group' => __('Members'),
            'icon' => 'bi-person-vcard',
            'title' => $m->full_name,
            'subtitle' => ($m->family?->name ?? '') . ' · ' . ($m->occupation ?? $m->gender),
            'url' => route('familytree.family.members.show', [$m->family_id, $m->id]),
        ])->toArray();

        return ['results' => $results];
    }
}