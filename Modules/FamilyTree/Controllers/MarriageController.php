<?php

namespace Modules\FamilyTree\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMarriage;
use Modules\FamilyTree\Models\FtMember;
use Modules\FamilyTree\Repositories\FtMarriageRepository;
use Modules\FamilyTree\Repositories\FtMemberRepository;
use Modules\FamilyTree\Requests\StoreMarriageRequest;
use Modules\FamilyTree\Requests\UpdateMarriageRequest;

class MarriageController extends Controller
{
    public function __construct(
        protected FtMarriageRepository $marriageRepo,
        protected FtMemberRepository $memberRepo
    ) {
    }

    public function index(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.manage-relationships')
            && $request->user()->company_id === $family->company_id, 403);

        $males = FtMember::where('family_id', $family->id)->where('gender', 'male')->orderBy('full_name')->get();
        $females = FtMember::where('family_id', $family->id)->where('gender', 'female')->orderBy('full_name')->get();

        return view('familytree::marriages.index', compact('family', 'males', 'females'));
    }

    public function table(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.manage-relationships'), 403);

        $marriages = $this->marriageRepo->forFamily($family->id);

        if ($status = $request->get('status')) {
            $marriages = $marriages->where('status', $status)->values();
        }

        return view('familytree::marriages._table', compact('marriages', 'family'));
    }

    public function json(Request $request, FtFamily $family, FtMarriage $marriage): JsonResponse
    {
        abort_unless($request->user()->company_id === $family->company_id, 403);
        return response()->json(['data' => $marriage]);
    }

    public function store(StoreMarriageRequest $request, FtFamily $family): JsonResponse
    {
        abort_unless($request->user()->company_id === $family->company_id, 403);

        if ($this->marriageRepo->alreadyMarried($request->husband_id, $request->wife_id)) {
            return response()->json([
                'success' => false,
                'message' => __('This couple already has an active marriage record.'),
            ], 422);
        }

        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $marriage = FtMarriage::create($data);

        // Update both members' marital_status
        FtMember::find($request->husband_id)?->update(['marital_status' => 'married']);
        FtMember::find($request->wife_id)?->update(['marital_status' => 'married']);

        return response()->json([
            'success' => true,
            'message' => __('Marriage record created.'),
            'data' => $marriage,
        ]);
    }

    public function update(UpdateMarriageRequest $request, FtFamily $family, FtMarriage $marriage): JsonResponse
    {
        abort_unless($request->user()->company_id === $family->company_id, 403);

        $marriage->update(array_merge($request->validated(), ['updated_by' => $request->user()->id]));

        // Update marital_status if marriage ended
        if (in_array($request->status, ['divorced', 'widowed'])) {
            $endStatus = $request->status;
            FtMember::find($marriage->husband_id)?->update(['marital_status' => $endStatus]);
            FtMember::find($marriage->wife_id)?->update(['marital_status' => $endStatus]);
        }

        return response()->json([
            'success' => true,
            'message' => __('Marriage record updated.'),
            'data' => $marriage,
        ]);
    }

    public function destroy(Request $request, FtFamily $family, FtMarriage $marriage): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-relationships')
            && $request->user()->company_id === $family->company_id, 403);

        $marriage->delete();

        return response()->json(['success' => true, 'message' => __('Marriage record deleted.')]);
    }
}