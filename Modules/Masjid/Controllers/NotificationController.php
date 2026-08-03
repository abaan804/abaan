<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeasonMember;
use Modules\Masjid\Services\MasjidBalanceService;
use Modules\Masjid\Services\MasjidNotificationService;

class NotificationController extends Controller
{
    public function __construct(
        protected MasjidNotificationService $notificationService,
        protected MasjidBalanceService $balanceService
    ) {
    }

    public function index(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.send-notifications')
            && $mosque->company_id === $request->user()->company_id, 403);

        $seasons = $mosque->seasons()->where('status', 'active')->get();
        $logs = $mosque->notificationLogs()->with('member')->latest()->take(50)->get();

        return view('masjid::notifications.index', compact('mosque', 'seasons', 'logs'));
    }

    public function sendAllReminders(Request $request, MasjidMosque $mosque): JsonResponse
    {
        abort_unless($request->user()->can('masjid.send-notifications'), 403);

        $request->validate(['season_id' => 'required|exists:masjid_seasons,id']);

        $assignments = MasjidSeasonMember::where('mosque_id', $mosque->id)
            ->where('season_id', $request->season_id)
            ->whereIn('status', ['pending', 'partial'])
            ->with(['member', 'season'])
            ->get();

        $dispatched = 0;
        foreach ($assignments as $assignment) {
            $member = $assignment->member;
            if (! $member) continue;
            if (! $member->email && ! $member->mobile) continue;

            $this->notificationService->sendBalanceReminder(
                $mosque,
                $member,
                $assignment->season,
                $assignment->balance()
            );

            $dispatched++;
        }

        return response()->json([
            'success' => true,
            'message' => __(':count reminder(s) queued for sending.', ['count' => $dispatched]),
        ]);
    }

    // Updated sendReminder() — single member:
    public function sendReminder(Request $request, MasjidMosque $mosque): JsonResponse
    {
        abort_unless($request->user()->can('masjid.send-notifications'), 403);

        $request->validate([
            'season_member_id' => 'required|exists:masjid_season_members,id',
        ]);

        $assignment = MasjidSeasonMember::with(['member', 'season'])
            ->findOrFail($request->season_member_id);

        abort_unless($assignment->mosque_id === $mosque->id, 403);

        $this->notificationService->sendBalanceReminder(
            $mosque,
            $assignment->member,
            $assignment->season,
            $assignment->balance()
        );

        return response()->json([
            'success' => true,
            'message' => __('Reminder queued for :name.', ['name' => $assignment->member->name]),
        ]);
    }
}