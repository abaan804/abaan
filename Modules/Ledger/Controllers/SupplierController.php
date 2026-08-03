<?php

namespace Modules\Ledger\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Ledger\Models\LedgerSupplier;
use Modules\Ledger\Requests\StoreSupplierRequest;
use Modules\Ledger\Requests\UpdateSupplierRequest;
use Modules\Ledger\Services\LedgerBalanceService;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Ledger\Exports\SuppliersExport;
use Modules\Ledger\Imports\SuppliersImport;
use Modules\Ledger\Exports\TransactionsExport;
use Modules\Ledger\Services\LedgerPdfService;

class SupplierController extends Controller
{
    public function __construct(
        protected LedgerBalanceService $balanceService,
        protected LedgerPdfService $pdfService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-suppliers'), 403);

        return view('ledger::suppliers.index');
    }

    public function table(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-suppliers'), 403);

        $query = LedgerSupplier::query();

        if ($search = $request->get('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%"));
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $suppliers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('ledger::suppliers._table', compact('suppliers'));
    }

    public function json(LedgerSupplier $supplier): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-suppliers'), 403);

        return response()->json(['data' => $supplier]);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $data = $request->safe()->except('photo');
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('ledger/suppliers', 'public');
        }

        $supplier = LedgerSupplier::create($data);

        return response()->json([
            'success' => true,
            'message' => __('Supplier added successfully.'),
            'data' => $supplier,
        ]);
    }

    public function update(UpdateSupplierRequest $request, LedgerSupplier $supplier): JsonResponse
    {
        $data = $request->safe()->except('photo');
        $data['updated_by'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('ledger/suppliers', 'public');
        }

        $supplier->update($data);

        return response()->json([
            'success' => true,
            'message' => __('Supplier updated successfully.'),
            'data' => $supplier,
        ]);
    }

    public function destroy(LedgerSupplier $supplier): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-suppliers'), 403);

        if ($supplier->transactions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot delete a supplier with existing transactions. Set status to Inactive instead.'),
            ], 422);
        }

        $supplier->delete();

        return response()->json(['success' => true, 'message' => __('Supplier deleted.')]);
    }

    public function show(LedgerSupplier $supplier): View
    {
        abort_unless(request()->user()->can('easykhata.manage-suppliers'), 403);

        $balance = $this->balanceService->supplierBalance($supplier);

        $totalDebit = $supplier->transactions()->where('type', 'debit')->sum('amount');
        $totalCredit = $supplier->transactions()->whereIn('type', ['credit', 'opening_balance'])->sum('amount');

        $transactions = $supplier->transactions()
            ->with(['paymentMethod'])
            ->latest('transaction_date')->latest('id')
            ->paginate(20);

        return view('ledger::suppliers.show', compact('supplier', 'balance', 'totalDebit', 'totalCredit', 'transactions'));
    }

    public function export(Request $request, string $format)
    {
        abort_unless($request->user()->can('easykhata.manage-transactions'), 403);

        $filters = $request->only(['date_from', 'date_to', 'customer_id', 'supplier_id', 'category_id', 'payment_method_id', 'type', 'search']);
        $export = new TransactionsExport($request->user()->company_id, $filters);
        $filename = 'transactions-' . now()->format('Y-m-d');

        return match ($format) {
            'csv' => Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }

    public function importTemplate()
    {
        abort_unless(request()->user()->can('easykhata.manage-suppliers'), 403);

        $headers = ['name', 'mobile', 'email', 'city', 'address', 'opening_balance'];
        $sample = ['Acme Traders', '03001234567', 'acme@example.com', 'Karachi', '456 Market Road', '0'];

        $csv = implode(',', $headers) . "\n" . implode(',', $sample) . "\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="suppliers-import-template.csv"',
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('easykhata.manage-suppliers'), 403);

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        $import = new SuppliersImport($request->user()->company_id, $request->user()->id);
        Excel::import($import, $request->file('file'));

        return response()->json([
            'success' => true,
            'imported_count' => count($import->imported),
            'failed_count' => count($import->failed),
            'failed' => $import->failed,
            'message' => __(':count suppliers imported successfully.', ['count' => count($import->imported)]),
        ]);
    }

    public function statementPdf(LedgerSupplier $supplier)
    {
        abort_unless(request()->user()->can('easykhata.manage-suppliers'), 403);

        $balance = $this->balanceService->supplierBalance($supplier);
        $totalDebit = $supplier->transactions()->where('type', 'debit')->sum('amount');
        $totalCredit = $supplier->transactions()->whereIn('type', ['credit', 'opening_balance'])->sum('amount');

        $transactions = $supplier->transactions()
            ->with(['paymentMethod'])
            ->orderBy('transaction_date')->orderBy('id')
            ->get();

        return $this->pdfService->download('ledger::reports.pdf.supplier-statement', [
            'letterhead' => $this->pdfService->companyLetterhead($supplier->company),
            'supplier' => $supplier,
            'balance' => $balance,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'transactions' => $transactions,
        ], 'statement-' . \Illuminate\Support\Str::slug($supplier->name) . '-' . now()->format('Y-m-d') . '.pdf');
    }
}