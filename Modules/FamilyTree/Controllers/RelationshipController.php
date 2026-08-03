<?php

namespace Modules\FamilyTree\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMember;
use Modules\FamilyTree\Models\FtRelationship;
use Modules\FamilyTree\Repositories\FtMemberRepository;
use Modules\FamilyTree\Requests\StoreRelationshipRequest;
use Modules\FamilyTree\Services\FamilyTreeRelationshipService;

class RelationshipController extends Controller
{
    public function __construct(
        protected FamilyTreeRelationshipService $relationshipService,
        protected FtMemberRepository $memberRepo
    ) {
    }

    public function summary(Request $request, FtFamily $family, FtMember $member): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-members')
            && $member->family_id === $family->id, 403);

        $summary = $this->relationshipService->fullRelationshipSummary($member);

        return response()->json([
            'success' => true,
            'data' => [
                'father' => $summary['father'] ? ['id' => $summary['father']->id, 'name' => $summary['father']->full_name] : null,
                'mother' => $summary['mother'] ? ['id' => $summary['mother']->id, 'name' => $summary['mother']->full_name] : null,
                'spouses_count' => $summary['spouses']->count(),
                'children_count' => $summary['children']->count(),
                'brothers_count' => $summary['brothers']->count(),
                'sisters_count' => $summary['sisters']->count(),
                'grandparents_count' => count($summary['grandparents']),
                'cousins_count' => $summary['cousins']->count(),
            ],
        ]);
    }

    public function store(StoreRelationshipRequest $request, FtFamily $family, FtMember $member): JsonResponse
    {
        abort_unless($request->user()->company_id === $family->company_id
            && $member->family_id === $family->id, 403);

        $relationship = FtRelationship::create([
            'company_id' => $family->company_id,
            'member_id' => $member->id,
            'related_member_id' => $request->related_member_id,
            'relationship_type' => $request->relationship_type,
            'label' => $request->label,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Relationship added.'),
            'data' => $relationship,
        ]);
    }

    public function destroy(Request $request, FtFamily $family, FtRelationship $relationship): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-relationships')
            && $request->user()->company_id === $family->company_id, 403);

        $relationship->delete();

        return response()->json(['success' => true, 'message' => __('Relationship removed.')]);
    }

    public function findPath(Request $request, FtFamily $family): JsonResponse
    {
        abort_unless($request->user()->can('familytree.view-tree')
            && $request->user()->company_id === $family->company_id, 403);

        $request->validate([
            'member_a_id' => 'required|exists:ft_members,id',
            'member_b_id' => 'required|exists:ft_members,id|different:member_a_id',
        ]);

        $memberA = FtMember::findOrFail($request->member_a_id);
        $memberB = FtMember::findOrFail($request->member_b_id);

        $path = $this->relationshipService->findRelationshipPath($memberA, $memberB);
        $description = $this->relationshipService->describeRelationship($memberA, $memberB);

        return response()->json([
            'success' => true,
            'path' => collect($path)->map(fn ($step) => [
                'name' => $step['member']->full_name,
                'label' => $step['label'],
                'gender' => $step['member']->gender,
                'photo' => $step['member']->profile_photo
                    ? asset('storage/' . $step['member']->profile_photo)
                    : null,
            ]),
            'description' => $description,
            'connected' => ! empty($path),
        ]);
    }
}