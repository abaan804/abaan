<?php

namespace Modules\FamilyTree\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Repositories\FtFamilyRepository;
use Modules\FamilyTree\Services\FamilyTreeSearchService;

class SearchController extends Controller
{
    public function __construct(
        protected FamilyTreeSearchService $searchService,
        protected FtFamilyRepository $familyRepo
    ) {
    }

    public function search(Request $request, FtFamily $family): JsonResponse
    {
        abort_unless($request->user()->can('familytree.manage-members')
            && $request->user()->company_id === $family->company_id, 403);

        $query = trim((string) $request->get('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $result = $this->searchService->quickSearch($request->user()->company_id, $query);

        return response()->json($result);
    }

    public function globalSearch(Request $request): View|JsonResponse
    {
        abort_unless($request->user()->can('familytree.view-dashboard'), 403);
         
        $query = trim((string) $request->get('q', ''));
        $filters = $request->only(['gender', 'life_status', 'marital_status', 'family_id']);

        if ($request->expectsJson() || $request->wantsJson()) {
            $results = $this->searchService->search($request->user()->company_id, $query, $filters);
            return response()->json(['success' => true, 'data' => $results]);
        }

        $families = $this->familyRepo->allForCompany($request->user()->company_id);
        $results = mb_strlen($query) >= 2
            ? $this->searchService->search($request->user()->company_id, $query, $filters)
            : ['members' => collect(), 'events' => collect()];
        $standalone = 1;
        
        return view('familytree::search.index', compact('query', 'results', 'families', 'filters'));
        // return view('familytree::search.index', compact(
        //             'query',
        //             'results',
        //             'families',
        //             'filters',
        //             'standalone'
        //         ))->with('standalone', 1);
    }
}