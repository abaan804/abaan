<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidMember;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeason;
use Modules\Masjid\Models\MasjidSeasonMember;
use Modules\Masjid\Repositories\MasjidMemberRepository;
use Modules\Masjid\Repositories\MasjidSeasonRepository;
use Modules\Masjid\Requests\StoreSeasonRequest;
use Modules\Masjid\Requests\UpdateSeasonRequest;
use Modules\Masjid\Services\MasjidSeasonService;
use Modules\Masjid\Services\MasjidNotificationService;

class SeasonController extends Controller
{
    public function __construct(
        protected MasjidSeasonService $seasonService,
        protected MasjidSeasonRepository $seasonRepo,
        protected MasjidMemberRepository $memberRepo,
        protected MasjidNotificationService $notificationService
    ) {
    }

    public function index(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.manage-seasons')
            && $mosque->company_id === $request->user()->company_id, 403);

        return view('masjid::seasons.index', compact('mosque'));
    }

    public function table(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.manage-seasons'), 403);

        $seasons = $this->seasonRepo->paginate($mosque, $request->only(['status', 'frequency']));

        return view('masjid::seasons._table', compact('seasons', 'mosque'));
    }

    public function json(Request $request, MasjidMosque $mosque, MasjidSeason $season): JsonResponse
    {
        abort_unless($season->mosque_id === $mosque->id, 403);

        return response()->json(['data' => $season]);
    }

    public function store(StoreSeasonRequest $request, MasjidMosque $mosque): JsonResponse
    {
        $season = $this->seasonService->create($mosque, $request->validated());

        // Dispatch season-assigned notifications to all auto-assigned members
        if ($season->auto_assign) {
            $members = $this->memberRepo->activeMembers($mosque);
            $this->notificationService->sendSeasonAssignedToAll($mosque, $season, $members);
        }

        return response()->json([
            'success' => true,
            'message' => __('Season created and members assigned.'),
            'data' => $season,
        ]);
    }

    public function update(UpdateSeasonRequest $request, MasjidMosque $mosque, MasjidSeason $season): JsonResponse
    {
        abort_unless($season->mosque_id === $mosque->id, 403);

        $oldAmount = $season->contribution_amount;
        $season = $this->seasonService->update($season, $request->validated());

        $syncedCount = 0;
        if ($season->contribution_amount != $oldAmount) {
            $syncedCount = $this->seasonService->syncContributionAmount($season);
        }

        return response()->json([
            'success' => true,
            'message' => __('Season updated. :count pending members synced.', ['count' => $syncedCount]),
            'data' => $season,
        ]);
    }

    public function destroy(Request $request, MasjidMosque $mosque, MasjidSeason $season): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-seasons')
            && $season->mosque_id === $mosque->id, 403);

        if ($season->payments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot delete a season with recorded payments. Set status to Completed instead.'),
            ], 422);
        }

        $season->seasonMembers()->delete();
        $season->delete();

        return response()->json(['success' => true, 'message' => __('Season deleted.')]);
    }

    public function members(Request $request, MasjidMosque $mosque, MasjidSeason $season): View
    {
        abort_unless($request->user()->can('masjid.manage-seasons')
            && $season->mosque_id === $mosque->id, 403);

        $allMembers = $this->memberRepo->activeMembers($mosque);

        return view('masjid::seasons.members', compact('mosque', 'season', 'allMembers'));
    }

    public function membersTable(Request $request, MasjidMosque $mosque, MasjidSeason $season): View
    {
        abort_unless($request->user()->can('masjid.manage-seasons'), 403);

        $assignments = MasjidSeasonMember::where('season_id', $season->id)
            ->with('member')
            ->orderBy('status')
            ->paginate(20)->withQueryString();

        return view('masjid::seasons._members_table', compact('assignments', 'mosque', 'season'));
    }

    public function assignMember(Request $request, MasjidMosque $mosque, MasjidSeason $season, MasjidMember $member): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-seasons')
            && $season->mosque_id === $mosque->id
            && $member->mosque_id === $mosque->id, 403);

        $assignment = $this->seasonService->assignMember($mosque, $season, $member);

        // Notify the newly-assigned member
        $this->notificationService->sendSeasonAssigned($mosque, $member, $season);

        return response()->json([
            'success' => true,
            'message' => __(':name assigned to season.', ['name' => $member->name]),
            'data' => $assignment,
        ]);
    }

    public function unassignMember(Request $request, MasjidMosque $mosque, MasjidSeason $season, MasjidSeasonMember $seasonMember): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-seasons')
            && $seasonMember->mosque_id === $mosque->id, 403);

        if (! $this->seasonService->unassignMember($seasonMember)) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot remove this member — they have existing payments for this season.'),
            ], 422);
        }

        return response()->json(['success' => true, 'message' => __('Member removed from season.')]);
    }

    public function assignAll(Request $request, MasjidMosque $mosque, MasjidSeason $season): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-seasons')
            && $season->mosque_id === $mosque->id, 403);

        $count = $this->seasonService->assignAllActiveMembers($mosque, $season);

        return response()->json([
            'success' => true,
            'message' => __(':count members assigned.', ['count' => $count]),
        ]);
    }

    public function syncAmount(Request $request, MasjidMosque $mosque, MasjidSeason $season): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-seasons')
            && $season->mosque_id === $mosque->id, 403);

        $count = $this->seasonService->syncContributionAmount($season);

        return response()->json([
            'success' => true,
            'message' => __(':count pending members updated.', ['count' => $count]),
        ]);
    }
}