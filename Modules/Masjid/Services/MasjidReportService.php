<?php

namespace Modules\Masjid\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidPayment;
use Modules\Masjid\Models\MasjidSeasonMember;

class MasjidReportService
{
    /**
     * Shared filtered payment query — base for all payment-based reports.
     */
    public function filteredPayments(MasjidMosque $mosque, array $filters = []): Builder
    {
        $query = MasjidPayment::where('mosque_id', $mosque->id)
            ->with(['member', 'season', 'receivedBy']);

        if (! empty($filters['date_from'])) {
            $query->whereDate('payment_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('payment_date', '<=', $filters['date_to']);
        }
        if (! empty($filters['member_id'])) {
            $query->where('member_id', $filters['member_id']);
        }
        if (! empty($filters['season_id'])) {
            $query->where('season_id', $filters['season_id']);
        }
        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        return $query;
    }

    /**
     * Season-level collection summary.
     */
    public function seasonSummary(MasjidMosque $mosque, int $seasonId): array
    {
        $assignments = MasjidSeasonMember::where('mosque_id', $mosque->id)
            ->where('season_id', $seasonId)
            ->get();

        return [
            'total_members' => $assignments->count(),
            'total_due' => round((float) $assignments->sum('amount_due'), 2),
            'total_paid' => round((float) $assignments->sum('amount_paid'), 2),
            'total_outstanding' => round((float) ($assignments->sum('amount_due') - $assignments->sum('amount_paid')), 2),
            'pending' => $assignments->where('status', 'pending')->count(),
            'partial' => $assignments->where('status', 'partial')->count(),
            'paid' => $assignments->where('status', 'paid')->count(),
            'overpaid' => $assignments->where('status', 'overpaid')->count(),
        ];
    }

    /**
     * Monthly collection totals for a given year — powers the dashboard graph.
     */
    public function monthlyCollection(MasjidMosque $mosque, int $year): Collection
    {
        return MasjidPayment::where('mosque_id', $mosque->id)
            ->whereYear('payment_date', $year)
            ->selectRaw('MONTH(payment_date) as month, SUM(amount_paid) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Payment method breakdown — powers the payment method summary report.
     */
    public function paymentMethodSummary(MasjidMosque $mosque, array $filters = []): Collection
    {
        return $this->filteredPayments($mosque, $filters)
            ->select('payment_method')
            ->selectRaw('SUM(amount_paid) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get();
    }

    /**
     * Member account statement — full history for a single member.
     */
    public function memberStatement(MasjidMosque $mosque, int $memberId): array
    {
        $assignments = MasjidSeasonMember::where('mosque_id', $mosque->id)
            ->where('member_id', $memberId)
            ->with(['season', 'payments.receivedBy'])
            ->orderByDesc('created_at')
            ->get();

        return [
            'assignments' => $assignments,
            'total_due' => round((float) $assignments->sum('amount_due'), 2),
            'total_paid' => round((float) $assignments->sum('amount_paid'), 2),
            'balance' => round((float) ($assignments->sum('amount_due') - $assignments->sum('amount_paid')), 2),
        ];
    }
}