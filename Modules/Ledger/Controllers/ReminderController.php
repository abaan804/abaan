<?php

namespace Modules\Ledger\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Ledger\Models\LedgerCustomer;
use Modules\Ledger\Models\LedgerReminder;
use Modules\Ledger\Models\LedgerSupplier;
use Modules\Ledger\Requests\StoreReminderRequest;

class ReminderController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-reminders'), 403);

        return view('ledger::reminders.index', [
            'customers' => LedgerCustomer::where('status', 'active')->orderBy('name')->get(),
            'suppliers' => LedgerSupplier::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function table(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-reminders'), 403);

        $query = LedgerReminder::with(['customer', 'supplier']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $reminders = $query->orderBy('due_date')->paginate(15)->withQueryString();

        return view('ledger::reminders._table', compact('reminders'));
    }

    public function store(StoreReminderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;
        $data['status'] = 'pending';

        $reminder = LedgerReminder::create($data);

        return response()->json(['success' => true, 'message' => __('Reminder created.'), 'data' => $reminder]);
    }

    public function dismiss(LedgerReminder $reminder): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-reminders'), 403);

        $reminder->update(['status' => 'dismissed']);

        return response()->json(['success' => true, 'message' => __('Reminder dismissed.')]);
    }

    public function destroy(LedgerReminder $reminder): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-reminders'), 403);

        $reminder->delete();

        return response()->json(['success' => true, 'message' => __('Reminder deleted.')]);
    }
}