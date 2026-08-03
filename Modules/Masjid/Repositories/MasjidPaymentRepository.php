<?php

namespace Modules\Masjid\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidPayment;

class MasjidPaymentRepository
{
    public function paginate(MasjidMosque $mosque, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MasjidPayment::where('mosque_id', $mosque->id)
            ->with(['member', 'season', 'receivedBy']);

        if (! empty($filters['member_id'])) {
            $query->where('member_id', $filters['member_id']);
        }

        if (! empty($filters['season_id'])) {
            $query->where('season_id', $filters['season_id']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('payment_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('payment_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('member', fn ($m) => $m->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->orderByDesc('payment_date')->orderByDesc('id')
            ->paginate($perPage)->withQueryString();
    }

    public function todayTotal(MasjidMosque $mosque): float
    {
        return (float) MasjidPayment::where('mosque_id', $mosque->id)
            ->whereDate('payment_date', today())
            ->sum('amount_paid');
    }

    public function monthTotal(MasjidMosque $mosque): float
    {
        return (float) MasjidPayment::where('mosque_id', $mosque->id)
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount_paid');
    }
}