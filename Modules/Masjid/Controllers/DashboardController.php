<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Repositories\MasjidPaymentRepository;
use Modules\Masjid\Services\MasjidBalanceService;
use Modules\Masjid\Services\MasjidReportService;

class DashboardController extends Controller
{
    public function __construct(
        protected MasjidBalanceService $balanceService,
        protected MasjidReportService $reportService,
        protected MasjidPaymentRepository $paymentRepo
    ) {
    }

    /**
     * Mosque selection landing — shows list of company's mosques.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('masjid.view-dashboard'), 403);

        $mosques = MasjidMosque::where('company_id', $request->user()->company_id)
            ->where('status', 'active')
            ->withCount('members')
            ->get();

        return view('masjid::dashboard.index', compact('mosques'));
    }

    /**
     * Per-mosque dashboard.
     */
    public function dashboard(Request $request, MasjidMosque $mosque): View
    {
        $this->authorizeMosque($request, $mosque);

        $totals = $this->balanceService->mosqueTotals($mosque);
        $todayCollection = $this->paymentRepo->todayTotal($mosque);
        $monthCollection = $this->paymentRepo->monthTotal($mosque);

        $recentPayments = $mosque->payments()
            ->with(['member', 'season'])
            ->latest('payment_date')->latest('id')
            ->take(10)->get();

        $pendingMembers = $this->balanceService->pendingMembers($mosque)->take(5);
        $overpaidMembers = $this->balanceService->overpaidMembers($mosque);

        $monthlyChart = $this->reportService->monthlyCollection($mosque, now()->year);

        $activeSeasons = $mosque->seasons()
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->take(5)->get();

        return view('masjid::dashboard.dashboard', compact(
            'mosque', 'totals', 'todayCollection', 'monthCollection',
            'recentPayments', 'pendingMembers', 'overpaidMembers',
            'monthlyChart', 'activeSeasons'
        ));
    }

    protected function authorizeMosque(Request $request, MasjidMosque $mosque): void
    {
        abort_unless(
            $request->user()->can('masjid.view-dashboard')
            && $mosque->company_id === $request->user()->company_id,
            403
        );
    }
}