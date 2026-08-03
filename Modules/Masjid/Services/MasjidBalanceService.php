<?php

namespace Modules\Masjid\Services;

use Illuminate\Support\Collection;
use Modules\Masjid\Models\MasjidMember;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeasonMember;

class MasjidBalanceService
{
    /**
     * Full balance summary for a member across all seasons.
     */
    public function memberSummary(MasjidMember $member): array
    {
        $assignments = MasjidSeasonMember::where('member_id', $member->id)
            ->with('season')
            ->get();

        $totalDue = $assignments->sum('amount_due');
        $totalPaid = $assignments->sum('amount_paid');
        $balance = $totalDue - $totalPaid;

        return [
            'total_due' => round((float) $totalDue, 2),
            'total_paid' => round((float) $totalPaid, 2),
            'balance' => round((float) $balance, 2),
            'status' => $this->overallStatus($totalDue, $totalPaid),
            'seasons' => $assignments,
        ];
    }

    /**
     * Balance for a member within a specific season.
     */
    public function seasonBalance(MasjidMember $member, int $seasonId): array
    {
        $assignment = MasjidSeasonMember::where('member_id', $member->id)
            ->where('season_id', $seasonId)
            ->first();

        if (! $assignment) {
            return ['amount_due' => 0, 'amount_paid' => 0, 'balance' => 0, 'status' => 'not_assigned'];
        }

        return [
            'amount_due' => (float) $assignment->amount_due,
            'amount_paid' => (float) $assignment->amount_paid,
            'balance' => $assignment->balance(),
            'status' => $assignment->status,
        ];
    }

    /**
     * Mosque-wide dashboard totals.
     */
    public function mosqueTotals(MasjidMosque $mosque): array
    {
        $assignments = MasjidSeasonMember::where('mosque_id', $mosque->id)->get();

        $totalDue = $assignments->sum('amount_due');
        $totalPaid = $assignments->sum('amount_paid');

        return [
            'total_due' => round((float) $totalDue, 2),
            'total_collected' => round((float) $totalPaid, 2),
            'total_outstanding' => round((float) ($totalDue - $totalPaid), 2),
            'pending_count' => $assignments->whereIn('status', ['pending', 'partial'])->count(),
            'paid_count' => $assignments->where('status', 'paid')->count(),
            'overpaid_count' => $assignments->where('status', 'overpaid')->count(),
        ];
    }

    /**
     * All members with pending/partial balances in a mosque.
     */
    public function pendingMembers(MasjidMosque $mosque): Collection
    {
        return MasjidSeasonMember::where('mosque_id', $mosque->id)
            ->whereIn('status', ['pending', 'partial'])
            ->with(['member', 'season'])
            ->orderByDesc('amount_due')
            ->get();
    }

    /**
     * All members who have overpaid in a mosque.
     */
    public function overpaidMembers(MasjidMosque $mosque): Collection
    {
        return MasjidSeasonMember::where('mosque_id', $mosque->id)
            ->where('status', 'overpaid')
            ->with(['member', 'season'])
            ->get();
    }

    protected function overallStatus(float $totalDue, float $totalPaid): string
    {
        if ($totalPaid <= 0) return 'pending';
        if ($totalPaid >= $totalDue) return $totalPaid > $totalDue ? 'overpaid' : 'paid';
        return 'partial';
    }
}