<?php

namespace Modules\Masjid\Services;

use Illuminate\Support\Facades\DB;
use Modules\Masjid\Models\MasjidActivityLog;
use Modules\Masjid\Models\MasjidMember;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeason;
use Modules\Masjid\Models\MasjidSeasonMember;
use Modules\Masjid\Repositories\MasjidMemberRepository;
use Modules\Masjid\Repositories\MasjidSeasonRepository;

class MasjidSeasonService
{
    public function __construct(
        protected MasjidMemberRepository $memberRepo,
        protected MasjidSeasonRepository $seasonRepo
    ) {
    }

    /**
     * Create a season and, if auto_assign=true, assign all active members immediately.
     */
    public function create(MasjidMosque $mosque, array $data): MasjidSeason
    {
        return DB::transaction(function () use ($mosque, $data) {
            $data['company_id'] = $mosque->company_id;
            $data['mosque_id'] = $mosque->id;
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $season = MasjidSeason::create($data);

            if ($season->auto_assign) {
                $this->assignAllActiveMembers($mosque, $season);
            }

            $this->log($mosque, 'season.created', $season);

            return $season;
        });
    }

    /**
     * Update a season. If contribution_amount changes, offer re-sync (caller decides).
     */
    public function update(MasjidSeason $season, array $data): MasjidSeason
    {
        return DB::transaction(function () use ($season, $data) {
            $before = $season->only(['name', 'contribution_amount', 'status']);
            $data['updated_by'] = auth()->id();
            $season->update($data);

            $this->log($season->mosque, 'season.updated', $season, ['before' => $before]);

            return $season;
        });
    }

    /**
     * Assign all active mosque members to a season (skipping already-assigned ones).
     */
    public function assignAllActiveMembers(MasjidMosque $mosque, MasjidSeason $season): int
    {
        $members = $this->memberRepo->activeMembers($mosque);
        $assigned = 0;

        foreach ($members as $member) {
            $this->assignMember($mosque, $season, $member);
            $assigned++;
        }

        return $assigned;
    }

    /**
     * Assign a single member to a season (idempotent — skips if already assigned).
     */
    public function assignMember(MasjidMosque $mosque, MasjidSeason $season, MasjidMember $member): MasjidSeasonMember
    {
        return MasjidSeasonMember::firstOrCreate(
            ['season_id' => $season->id, 'member_id' => $member->id],
            [
                'company_id' => $mosque->company_id,
                'mosque_id' => $mosque->id,
                'amount_due' => $season->contribution_amount,
                'amount_paid' => 0,
                'status' => MasjidSeasonMember::STATUS_PENDING,
            ]
        );
    }

    /**
     * Remove a member's assignment from a season.
     * Only allowed if no payments exist for this assignment.
     */
    public function unassignMember(MasjidSeasonMember $seasonMember): bool
    {
        if ($seasonMember->payments()->exists()) {
            return false;
        }

        $seasonMember->delete();

        return true;
    }

    /**
     * Auto-assign a newly-joined member to all open auto-assign seasons in their mosque.
     * Called by MemberController after member creation.
     */
    public function assignMemberToOpenSeasons(MasjidMosque $mosque, MasjidMember $member): int
    {
        $seasons = $this->seasonRepo->openAutoAssignSeasons($mosque);
        $assigned = 0;

        foreach ($seasons as $season) {
            $this->assignMember($mosque, $season, $member);
            $assigned++;
        }

        return $assigned;
    }

    /**
     * Sync amount_due on all pending season_members when contribution_amount changes.
     * Only updates members who haven't paid anything yet (amount_paid = 0).
     */
    public function syncContributionAmount(MasjidSeason $season): int
    {
        return MasjidSeasonMember::where('season_id', $season->id)
            ->where('amount_paid', 0)
            ->where('status', MasjidSeasonMember::STATUS_PENDING)
            ->update(['amount_due' => $season->contribution_amount]);
    }

    protected function log(MasjidMosque $mosque, string $action, $subject, array $properties = []): void
    {
        MasjidActivityLog::create([
            'company_id' => $mosque->company_id,
            'mosque_id' => $mosque->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}