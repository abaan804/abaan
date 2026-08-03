<?php

namespace Modules\FamilyTree\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMember;
use Modules\FamilyTree\Repositories\FtMemberRepository;
use Modules\FamilyTree\Services\FamilyTreeNotificationService;

class NotificationController extends Controller
{
    public function __construct(
        protected FamilyTreeNotificationService $notificationService,
        protected FtMemberRepository $memberRepo
    ) {
    }

    public function index(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-dashboard')
            && $request->user()->company_id === $family->company_id, 403);

        $upcomingBirthdays = $this->memberRepo->upcomingBirthdays(
            $request->user()->company_id, 30
        )->filter(fn ($m) => $m->family_id === $family->id);

        return view('familytree::notifications.index', compact('family', 'upcomingBirthdays'));
    }

    public function sendBirthday(Request $request, FtFamily $family): JsonResponse
    {
        abort_unless($request->user()->company_id === $family->company_id, 403);

        $request->validate(['member_id' => 'required|exists:ft_members,id']);

        $member = FtMember::findOrFail($request->member_id);
        $this->notificationService->sendBirthdayReminder($member);

        return response()->json([
            'success' => true,
            'message' => __('Birthday reminder sent to :name.', ['name' => $member->full_name]),
        ]);
    }

    public function sendAllBirthdays(Request $request, FtFamily $family): JsonResponse
    {
        abort_unless($request->user()->company_id === $family->company_id, 403);

        $this->notificationService->sendUpcomingBirthdays(
            $request->user()->company_id
        );

        return response()->json([
            'success' => true,
            'message' => __('Birthday reminders dispatched for upcoming birthdays.'),
        ]);
    }
}