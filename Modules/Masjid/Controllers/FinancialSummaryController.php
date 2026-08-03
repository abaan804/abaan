<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidDonation;
use Modules\Masjid\Models\MasjidExpense;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidPayment;
use Modules\Masjid\Models\MasjidSeason;

class FinancialSummaryController extends Controller
{
    public function index(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.view-reports')
            && $mosque->company_id === $request->user()->company_id, 403);

        $seasonId  = $request->get('season_id');
        $dateFrom  = $request->get('date_from');
        $dateTo    = $request->get('date_to');
        $seasons   = MasjidSeason::where('mosque_id', $mosque->id)
            ->orderByDesc('start_date')->get();

        // ── Season Collections ────────────────────────────────────────────────
        $paymentsQuery = MasjidPayment::where('mosque_id', $mosque->id);
        if ($seasonId) $paymentsQuery->where('season_id', $seasonId);
        if ($dateFrom) $paymentsQuery->whereDate('payment_date', '>=', $dateFrom);
        if ($dateTo)   $paymentsQuery->whereDate('payment_date', '<=', $dateTo);
        $totalPayments = $paymentsQuery->sum('amount_paid');

        // ── Donations ─────────────────────────────────────────────────────────
        $donationsQuery = MasjidDonation::where('mosque_id', $mosque->id);
        if ($seasonId) $donationsQuery->where('season_id', $seasonId);
        if ($dateFrom) $donationsQuery->dateFrom($dateFrom);
        if ($dateTo)   $donationsQuery->dateTo($dateTo);
        $totalDonations = $donationsQuery->sum('amount');

        $namedDonations     = (clone $donationsQuery)->where('type', 'named')->sum('amount');
        $anonymousDonations = (clone $donationsQuery)->where('type', 'anonymous')->sum('amount');

        // ── Expenses ──────────────────────────────────────────────────────────
        $expensesQuery = MasjidExpense::where('mosque_id', $mosque->id);
        if ($seasonId) $expensesQuery->where('season_id', $seasonId);
        if ($dateFrom) $expensesQuery->dateFrom($dateFrom);
        if ($dateTo)   $expensesQuery->dateTo($dateTo);
        $totalExpenses = $expensesQuery->sum('amount');

        // ── Expense breakdown by category ─────────────────────────────────────
        $expenseByCategory = MasjidExpense::where('mosque_id', $mosque->id)
            ->when($seasonId, fn ($q) => $q->where('season_id', $seasonId))
            ->when($dateFrom, fn ($q) => $q->dateFrom($dateFrom))
            ->when($dateTo,   fn ($q) => $q->dateTo($dateTo))
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // ── Net Balance ───────────────────────────────────────────────────────
        $netBalance = ($totalPayments + $totalDonations) - $totalExpenses;

        // ── Recent transactions (last 10 each) ────────────────────────────────
        $recentPayments  = MasjidPayment::where('mosque_id', $mosque->id)
            ->when($seasonId, fn ($q) => $q->where('season_id', $seasonId))
            ->with('member')
            ->orderByDesc('payment_date')
            ->take(5)->get();

        $recentDonations = MasjidDonation::where('mosque_id', $mosque->id)
            ->when($seasonId, fn ($q) => $q->where('season_id', $seasonId))
            ->orderByDesc('donation_date')
            ->take(5)->get();

        $recentExpenses  = MasjidExpense::where('mosque_id', $mosque->id)
            ->when($seasonId, fn ($q) => $q->where('season_id', $seasonId))
            ->orderByDesc('expense_date')
            ->take(5)->get();

        $selectedSeason = $seasonId
            ? $seasons->firstWhere('id', $seasonId)
            : null;

        return view('masjid::financial.index', compact(
            'mosque', 'seasons', 'selectedSeason',
            'totalPayments', 'totalDonations', 'totalExpenses', 'netBalance',
            'namedDonations', 'anonymousDonations',
            'expenseByCategory',
            'recentPayments', 'recentDonations', 'recentExpenses',
            'seasonId', 'dateFrom', 'dateTo'
        ));
    }
}