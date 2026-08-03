<?php

namespace Modules\Ledger\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Ledger\Models\LedgerCategory;
use Modules\Ledger\Requests\StoreCategoryRequest;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-categories'), 403);

        return view('ledger::categories.index');
    }

    public function table(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-categories'), 403);

        $query = LedgerCategory::query();

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $categories = $query->orderBy('type')->orderBy('name')->paginate(15)->withQueryString();

        return view('ledger::categories._table', compact('categories'));
    }

    public function json(LedgerCategory $category): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-categories'), 403);

        return response()->json(['data' => $category]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = LedgerCategory::create($request->validated());

        return response()->json(['success' => true, 'message' => __('Category created.'), 'data' => $category]);
    }

    public function update(StoreCategoryRequest $request, LedgerCategory $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json(['success' => true, 'message' => __('Category updated.'), 'data' => $category]);
    }

    public function destroy(LedgerCategory $category): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-categories'), 403);

        if ($category->transactions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot delete a category in use. Set it to Inactive instead.'),
            ], 422);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => __('Category deleted.')]);
    }
}