<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidExpense;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeason;

class ExpenseController extends Controller
{
    public function index(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.manage-payments')
            && $mosque->company_id === $request->user()->company_id, 403);

        $seasons    = MasjidSeason::where('mosque_id', $mosque->id)
            ->orderByDesc('start_date')->get();
        $categories = MasjidExpense::CATEGORIES;

        return view('masjid::expenses.index', compact('mosque', 'seasons', 'categories'));
    }

    public function table(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($mosque->company_id === $request->user()->company_id, 403);

        $query = MasjidExpense::where('mosque_id', $mosque->id)
            ->with(['season', 'createdBy']);

        if ($cat = $request->get('category')) {
            $query->where('category', $cat);
        }
        if ($seasonId = $request->get('season_id')) {
            $query->where('season_id', $seasonId);
        }
        if ($from = $request->get('date_from')) {
            $query->dateFrom($from);
        }
        if ($to = $request->get('date_to')) {
            $query->dateTo($to);
        }
        if ($search = $request->get('search')) {
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('paid_to', 'like', "%{$search}%")
                ->orWhere('receipt_no', 'like', "%{$search}%")
            );
        }

        $expenses = $query->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $total = MasjidExpense::where('mosque_id', $mosque->id)
            ->when($request->get('category'), fn ($q, $v) => $q->where('category', $v))
            ->when($request->get('season_id'), fn ($q, $v) => $q->where('season_id', $v))
            ->when($request->get('date_from'), fn ($q, $v) => $q->dateFrom($v))
            ->when($request->get('date_to'), fn ($q, $v) => $q->dateTo($v))
            ->sum('amount');

        return view('masjid::expenses._table', compact('expenses', 'mosque', 'total'));
    }

    public function json(Request $request, MasjidMosque $mosque, MasjidExpense $expense): JsonResponse
    {
        abort_unless($expense->mosque_id === $mosque->id, 403);
        return response()->json(['data' => $expense]);
    }

    public function store(Request $request, MasjidMosque $mosque): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-payments')
            && $mosque->company_id === $request->user()->company_id, 403);

        $data = $request->validate([
            'category'     => 'required|in:' . implode(',', array_keys(MasjidExpense::CATEGORIES)),
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'paid_to'      => 'nullable|string|max:255',
            'receipt_no'   => 'nullable|string|max:50',
            'season_id'    => 'nullable|exists:masjid_seasons,id',
            'notes'        => 'nullable|string',
            'attachment'   => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')
                ->store('masjid/expenses', 'public');
        }

        $data['company_id'] = $mosque->company_id;
        $data['mosque_id']  = $mosque->id;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $expense = MasjidExpense::create($data);

        return response()->json([
            'success' => true,
            'message' => __('Expense recorded.'),
            'data'    => $expense,
        ]);
    }

    public function update(Request $request, MasjidMosque $mosque, MasjidExpense $expense): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-payments')
            && $expense->mosque_id === $mosque->id, 403);

        $data = $request->validate([
            'category'     => 'required|in:' . implode(',', array_keys(MasjidExpense::CATEGORIES)),
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'paid_to'      => 'nullable|string|max:255',
            'receipt_no'   => 'nullable|string|max:50',
            'season_id'    => 'nullable|exists:masjid_seasons,id',
            'notes'        => 'nullable|string',
        ]);

        $data['updated_by'] = auth()->id();
        $expense->update($data);

        return response()->json([
            'success' => true,
            'message' => __('Expense updated.'),
            'data'    => $expense,
        ]);
    }

    public function destroy(Request $request, MasjidMosque $mosque, MasjidExpense $expense): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-payments')
            && $expense->mosque_id === $mosque->id, 403);

        $expense->delete();

        return response()->json(['success' => true, 'message' => __('Expense deleted.')]);
    }
}