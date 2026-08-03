<?php

namespace Modules\FamilyTree\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMember;
use Modules\FamilyTree\Repositories\FtMemberRepository;
use Modules\FamilyTree\Services\FamilyTreeRelationshipService;
use Modules\FamilyTree\Services\FamilyTreeVisualizationService;

class TreeController extends Controller
{
    public function __construct(
        protected FamilyTreeVisualizationService $vizService,
        protected FamilyTreeRelationshipService $relationshipService,
        protected FtMemberRepository $memberRepo
    ) {
    }

    public function index(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-tree')
            && $request->user()->company_id === $family->company_id, 403);

        $members = FtMember::where('family_id', $family->id)
            ->orderBy('full_name')->get();

        $roots = $this->memberRepo->roots($family->id);
        
        return view('familytree::tree.index', compact('family', 'members', 'roots'));
    }

    public function fullTree(Request $request, FtFamily $family): JsonResponse
    {
        abort_unless($request->user()->can('familytree.view-tree')
            && $request->user()->company_id === $family->company_id, 403);

        $maxDepth = (int) $request->get('depth', 5);
        $tree = $this->vizService->fullTree($family, $maxDepth);

        return response()->json(['success' => true, 'data' => $tree]);
    }

    public function descendantTree(Request $request, FtFamily $family, FtMember $member): JsonResponse
    {
        abort_unless($request->user()->can('familytree.view-tree')
            && $member->family_id === $family->id, 403);

        $maxDepth = (int) $request->get('depth', 6);
        $tree = $this->vizService->descendantTree($member, $maxDepth);

        return response()->json(['success' => true, 'data' => $tree]);
    }

    public function ancestorTree(Request $request, FtFamily $family, FtMember $member): JsonResponse
    {
        abort_unless($request->user()->can('familytree.view-tree')
            && $member->family_id === $family->id, 403);

        $maxDepth = (int) $request->get('depth', 4);
        $tree = $this->vizService->ancestorTree($member, $maxDepth);

        return response()->json(['success' => true, 'data' => $tree]);
    }

    /**
     * AJAX: Returns a rich hover card for a member node in the tree.
     */
    public function memberCard(Request $request, FtFamily $family, FtMember $member): JsonResponse
    {
        abort_unless($request->user()->can('familytree.view-tree')
            && $member->family_id === $family->id, 403);

        $member->load(['father', 'mother', 'husbandMarriages.wife', 'wifeMarriages.husband']);

        $recentEvent = $member->events()->active()->orderByDesc('event_date')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $member->id,
                'full_name' => $member->full_name,
                'gender' => $member->gender,
                'age' => $member->age,
                'date_of_birth' => $member->date_of_birth?->format('d M Y'),
                'date_of_death' => $member->date_of_death?->format('d M Y'),
                'life_status' => $member->life_status,
                'marital_status' => $member->marital_status,
                'occupation' => $member->occupation,
                'contact_number' => $member->contact_number,
                'current_address' => $member->current_address,
                'father_name' => $member->father_display_name,
                'spouses' => $member->spouses()->map->only(['id', 'full_name', 'gender']),
                'children_count' => $member->children()->count(),
                'photo' => $member->profile_photo
                    ? asset('storage/' . $member->profile_photo)
                    : asset('images/familytree/default-' . $member->gender . '.png'),
                'recent_event' => $recentEvent ? [
                    'title' => $recentEvent->display_title,
                    'date' => $recentEvent->event_date->format('d M Y'),
                ] : null,
                'profile_url' => route('familytree.family.members.show', [$family->id, $member->id]),
            ],
        ]);
    }
}