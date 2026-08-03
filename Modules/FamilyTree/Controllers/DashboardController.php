<?php

namespace Modules\FamilyTree\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FamilyTree\Models\FtEvent;
use Modules\FamilyTree\Repositories\FtFamilyRepository;
use Modules\FamilyTree\Repositories\FtMemberRepository;

class DashboardController extends Controller
{
    public function __construct(
        protected FtFamilyRepository $familyRepo,
        protected FtMemberRepository $memberRepo
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('familytree.view-dashboard'), 403);

        $companyId = $request->user()->company_id;
        $families = $this->familyRepo->allForCompany($companyId);
        $memberStats = $this->memberRepo->dashboardStats($companyId);
        $familyStats = $this->familyRepo->dashboardStats($companyId);
        $upcomingBirthdays = $this->memberRepo->upcomingBirthdays($companyId, 30);

        $recentEvents = FtEvent::where('company_id', $companyId)
            ->where('status', 'active')
            ->with(['member', 'family'])
            ->orderByDesc('event_date')
            ->take(8)
            ->get();

        return view('familytree::dashboard.index', compact(
            'families', 'memberStats', 'familyStats',
            'upcomingBirthdays', 'recentEvents'
        ));
    }
}