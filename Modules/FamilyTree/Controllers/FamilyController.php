<?php

namespace Modules\FamilyTree\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Repositories\FtFamilyRepository;
use Modules\FamilyTree\Requests\StoreFamilyRequest;
use Modules\FamilyTree\Requests\UpdateFamilyRequest;

class FamilyController extends Controller
{
    public function __construct(protected FtFamilyRepository $familyRepo)
    {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('familytree.manage-families'), 403);
        return view('familytree::families.index');
    }

    public function table(Request $request): View
    {
        abort_unless($request->user()->can('familytree.manage-families'), 403);
        $families = $this->familyRepo->paginate(
            $request->user()->company_id,
            $request->only(['search', 'status'])
        );
        return view('familytree::families._table', compact('families'));
    }

    public function json(Request $request, FtFamily $family): JsonResponse
    {
        abort_unless($request->user()->company_id === $family->company_id, 403);
        return response()->json(['data' => $family]);
    }

    public function store(StoreFamilyRequest $request): JsonResponse
    {
        $data = $request->safe()->except('photo');
        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('familytree/families', 'public');
        }

        $family = FtFamily::create($data);

        return response()->json([
            'success' => true,
            'message' => __('Family created successfully.'),
            'data' => $family,
        ]);
    }

    public function update(UpdateFamilyRequest $request, FtFamily $family): JsonResponse
    {
        abort_unless($request->user()->company_id === $family->company_id, 403);

        $data = $request->safe()->except('photo');
        $data['updated_by'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('familytree/families', 'public');
        }

        $family->update($data);

        return response()->json([
            'success' => true,
            'message' => __('Family updated successfully.'),
            'data' => $family,
        ]);
    }

    public function destroy(Request $request, FtFamily $family): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-families')
            && $request->user()->company_id === $family->company_id, 403);

        if ($family->members()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot delete a family with existing members. Remove all members first or set status to Inactive.'),
            ], 422);
        }

        $family->delete();

        return response()->json(['success' => true, 'message' => __('Family deleted.')]);
    }
}