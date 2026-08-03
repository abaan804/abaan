<?php

namespace Modules\Ledger\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Ledger\Models\LedgerCustomer;
use Modules\Ledger\Requests\StoreCustomerRequest;
use Modules\Ledger\Requests\UpdateCustomerRequest;
use Modules\Ledger\Services\LedgerBalanceService;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Ledger\Exports\CustomersExport;
use Modules\Ledger\Imports\CustomersImport;
use Modules\Ledger\Services\LedgerPdfService;

class CustomerController extends Controller
{
    public function __construct(
    protected LedgerBalanceService $balanceService,
    protected LedgerPdfService $pdfService
    ) {
    }

    /**
     * Initial page load — renders the shell only. Table content always
     * comes from table() via fetch, even on first load (keeps one source of truth).
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-customers'), 403);

        return view('ledger::customers.index');
    }

    /**
     * AJAX: returns the table partial HTML for the current filters/page.
     * Called on load, on search/filter input, and after any create/update/delete.
     */
    public function table(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-customers'), 403);

        $query = LedgerCustomer::query();

        if ($search = $request->get('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%"));
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $customers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('ledger::customers._table', compact('customers'));
    }

    /**
     * AJAX: returns a single customer's data as JSON, used to populate the Edit modal.
     */
    public function json(LedgerCustomer $customer): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-customers'), 403);

        return response()->json(['data' => $customer]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $data = $request->safe()->except('photo');
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('ledger/customers', 'public');
        }

        $customer = LedgerCustomer::create($data);

        return response()->json([
            'success' => true,
            'message' => __('Customer added successfully.'),
            'data' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, LedgerCustomer $customer): JsonResponse
    {
        $data = $request->safe()->except('photo');
        $data['updated_by'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('ledger/customers', 'public');
        }

        $customer->update($data);

        return response()->json([
            'success' => true,
            'message' => __('Customer updated successfully.'),
            'data' => $customer,
        ]);
    }

    public function destroy(LedgerCustomer $customer): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-customers'), 403);

        if ($customer->transactions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot delete a customer with existing transactions. Set status to Inactive instead.'),
            ], 422);
        }

        $customer->delete();

        return response()->json(['success' => true, 'message' => __('Customer deleted.')]);
    }

    public function show(LedgerCustomer $customer): View
    {
        abort_unless(request()->user()->can('easykhata.manage-customers'), 403);

        $balance = $this->balanceService->customerBalance($customer);

        $totalDebit = $customer->transactions()->whereIn('type', ['debit', 'opening_balance'])->sum('amount');
        $totalCredit = $customer->transactions()->where('type', 'credit')->sum('amount');

        $transactions = $customer->transactions()
            ->with(['paymentMethod'])
            ->latest('transaction_date')->latest('id')
            ->paginate(20);

        return view('ledger::customers.show', compact('customer', 'balance', 'totalDebit', 'totalCredit', 'transactions'));
    }

    public function export(Request $request, string $format)
    {
        abort_unless($request->user()->can('easykhata.manage-customers'), 403);

        $filters = $request->only(['search', 'status']);
        $export = new CustomersExport($request->user()->company_id, $filters);
        $filename = 'customers-' . now()->format('Y-m-d');

        return match ($format) {
            'csv' => Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }

    public function importTemplate()
    {
        abort_unless(request()->user()->can('easykhata.manage-customers'), 403);

        $headers = ['name', 'mobile', 'email', 'city', 'address', 'opening_balance'];
        $sample = ['John Doe', '03001234567', 'john@example.com', 'Lahore', '123 Main Street', '0'];

        $csv = implode(',', $headers) . "\n" . implode(',', $sample) . "\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers-import-template.csv"',
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('easykhata.manage-customers'), 403);

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        $import = new CustomersImport($request->user()->company_id, $request->user()->id);
        Excel::import($import, $request->file('file'));

        return response()->json([
            'success' => true,
            'imported_count' => count($import->imported),
            'failed_count' => count($import->failed),
            'failed' => $import->failed,
            'message' => __(':count customers imported successfully.', ['count' => count($import->imported)]),
        ]);
    }

    public function statementPdf(LedgerCustomer $customer)
    {
        abort_unless(request()->user()->can('easykhata.manage-customers'), 403);

        $balance = $this->balanceService->customerBalance($customer);
        $totalDebit = $customer->transactions()->whereIn('type', ['debit', 'opening_balance'])->sum('amount');
        $totalCredit = $customer->transactions()->where('type', 'credit')->sum('amount');

        $transactions = $customer->transactions()
            ->with(['paymentMethod'])
            ->orderBy('transaction_date')->orderBy('id')
            ->get();
        
        return $this->pdfService->download('ledger::reports.pdf.customer-statement', [
            'letterhead' => $this->pdfService->companyLetterhead($customer->company),
            'customer' => $customer,
            'balance' => $balance,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'transactions' => $transactions,
        ], 'statement-' . \Illuminate\Support\Str::slug($customer->name) . '-' . now()->format('Y-m-d') . '.pdf');
    }
}