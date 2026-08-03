<?php

namespace Modules\FamilyTree\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMember;
use Modules\FamilyTree\Repositories\FtMemberRepository;
use Modules\FamilyTree\Requests\StoreMemberRequest;
use Modules\FamilyTree\Requests\UpdateMemberRequest;
use Modules\FamilyTree\Services\FamilyTreeMemberService;
use Modules\FamilyTree\Services\FamilyTreeRelationshipService;

class MemberController extends Controller
{
    public function __construct(
        protected FamilyTreeMemberService $memberService,
        protected FamilyTreeRelationshipService $relationshipService,
        protected FtMemberRepository $memberRepo
    ) {
    }

    public function index(Request $request, FtFamily $family): View
    {
        $this->authorizeFamily($request, $family, 'familytree.manage-members');
        return view('familytree::members.index', compact('family'));
    }

    public function table(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.manage-members'), 403);

        $members = $this->memberRepo->paginate(
            $family,
            $request->only(['search', 'gender', 'life_status', 'marital_status', 'sort', 'dir'])
        );
        
        return view('familytree::members._table', compact('members', 'family'));
    }

    public function show(Request $request, FtFamily $family, FtMember $member): View
    {
        abort_unless($request->user()->can('familytree.manage-members')
            && $member->family_id === $family->id, 403);

        $member->load(['father', 'mother', 'events', 'documents',
            'husbandMarriages.wife', 'wifeMarriages.husband']);

        $summary = $this->relationshipService->fullRelationshipSummary($member);

        return view('familytree::members.show', compact('family', 'member', 'summary'));
    }

    public function json(Request $request, FtFamily $family, FtMember $member): JsonResponse
    {
        abort_unless($member->family_id === $family->id
            && $request->user()->company_id === $family->company_id, 403);

        return response()->json(['data' => $member]);
    }

    public function store(StoreMemberRequest $request, FtFamily $family): JsonResponse
    {
        $this->authorizeFamily($request, $family, 'familytree.manage-members');

        $data = $request->safe()->except('profile_photo');

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')
                ->store('familytree/members', 'public');
        }

        $member = $this->memberService->create($family, $data);

        return response()->json([
            'success' => true,
            'message' => __('Member added successfully.'),
            'data' => $member,
        ]);
    }

    public function update(UpdateMemberRequest $request, FtFamily $family, FtMember $member): JsonResponse
    {
        abort_unless($member->family_id === $family->id, 403);

        $data = $request->safe()->except('profile_photo');
        $data['updated_by'] = $request->user()->id;

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')
                ->store('familytree/members', 'public');
        }

        $member = $this->memberService->update($family,$member, $data);

        return response()->json([
            'success' => true,
            'message' => __('Member updated successfully.'),
            'data' => $member,
        ]);
    }

    public function destroy(Request $request, FtFamily $family, FtMember $member): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-members')
            && $member->family_id === $family->id, 403);

        if ($member->childrenAsFather()->exists() || $member->childrenAsMother()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot delete a member who is linked as a parent. Unlink their children first.'),
            ], 422);
        }

        $this->memberService->delete($member);

        return response()->json(['success' => true, 'message' => __('Member deleted.')]);
    }

    public function linkFather(Request $request, FtFamily $family, FtMember $member): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-members')
            && $member->family_id === $family->id, 403);

        $request->validate([
            'father_id' => 'required|exists:ft_members,id',
        ]);

        $father = FtMember::findOrFail($request->father_id);

        if ($father->id === $member->id) {
            return response()->json(['success' => false, 'message' => __('A member cannot be their own father.')], 422);
        }

        if ($father->gender !== 'male') {
            return response()->json(['success' => false, 'message' => __('The selected member is not male and cannot be set as father.')], 422);
        }

        $this->memberService->linkFather($member, $father);

        return response()->json([
            'success' => true,
            'message' => __(':name linked as father.', ['name' => $father->full_name]),
        ]);
    }

    public function linkMother(Request $request, FtFamily $family, FtMember $member): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-members')
            && $member->family_id === $family->id, 403);

        $request->validate([
            'mother_id' => 'required|exists:ft_members,id',
        ]);

        $mother = FtMember::findOrFail($request->mother_id);

        if ($mother->id === $member->id) {
            return response()->json(['success' => false, 'message' => __('A member cannot be their own mother.')], 422);
        }

        $this->memberService->linkMother($member, $mother);

        return response()->json([
            'success' => true,
            'message' => __(':name linked as mother.', ['name' => $mother->full_name]),
        ]);
    }

    public function unlinkParent(Request $request, FtFamily $family, FtMember $member): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-members')
            && $member->family_id === $family->id, 403);

        $request->validate(['parent' => 'required|in:father,mother']);

        $field = $request->parent === 'father' ? 'father_id' : 'mother_id';
        $member->update([$field => null]);

        return response()->json([
            'success' => true,
            'message' => __(':parent unlinked successfully.', ['parent' => ucfirst($request->parent)]),
        ]);
    }

    protected function authorizeFamily(Request $request, FtFamily $family, string $permission): void
    {
        abort_unless(
            $request->user()->can($permission)
            && $request->user()->company_id === $family->company_id,
            403
        );
    }
}