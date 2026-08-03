<?php

namespace Modules\Ledger\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Ledger\Models\LedgerPaymentMethod;
use Modules\Ledger\Requests\StorePaymentMethodRequest;

class PaymentMethodController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-categories'), 403);

        return view('ledger::payment-methods.index');
    }

    public function table(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-categories'), 403);

        $methods = LedgerPaymentMethod::orderBy('name')->paginate(15)->withQueryString();

        return view('ledger::payment-methods._table', compact('methods'));
    }

    public function json(LedgerPaymentMethod $paymentMethod): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-categories'), 403);

        return response()->json(['data' => $paymentMethod]);
    }

    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $method = LedgerPaymentMethod::create($request->validated());

        return response()->json(['success' => true, 'message' => __('Payment method created.'), 'data' => $method]);
    }

    public function update(StorePaymentMethodRequest $request, LedgerPaymentMethod $paymentMethod): JsonResponse
    {
        $paymentMethod->update($request->validated());

        return response()->json(['success' => true, 'message' => __('Payment method updated.'), 'data' => $paymentMethod]);
    }

    public function destroy(LedgerPaymentMethod $paymentMethod): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-categories'), 403);

        if ($paymentMethod->transactions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot delete a payment method in use. Set it to Inactive instead.'),
            ], 422);
        }

        $paymentMethod->delete();

        return response()->json(['success' => true, 'message' => __('Payment method deleted.')]);
    }
}