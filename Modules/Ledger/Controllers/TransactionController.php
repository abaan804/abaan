<?php

namespace Modules\Ledger\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Ledger\Models\LedgerCategory;
use Modules\Ledger\Models\LedgerCustomer;
use Modules\Ledger\Models\LedgerPaymentMethod;
use Modules\Ledger\Models\LedgerSupplier;
use Modules\Ledger\Models\LedgerTransaction;
use Modules\Ledger\Models\LedgerTransactionAttachment;
use Modules\Ledger\Requests\StoreTransactionRequest;
use Modules\Ledger\Requests\UpdateTransactionRequest;
use Modules\Ledger\Services\LedgerReportService;
use Modules\Ledger\Services\LedgerTransactionService;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Ledger\Exports\TransactionsExport;
use Modules\Ledger\Imports\TransactionsImport;

class TransactionController extends Controller
{
    public function __construct(
        protected LedgerTransactionService $transactionService,
        protected LedgerReportService $reportService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-transactions'), 403);

        return view('ledger::transactions.index', [
            'customers' => LedgerCustomer::where('status', 'active')->orderBy('name')->get(),
            'suppliers' => LedgerSupplier::where('status', 'active')->orderBy('name')->get(),
            'categories' => LedgerCategory::where('status', 'active')->get(),
            'paymentMethods' => LedgerPaymentMethod::where('status', 'active')->get(),
        ]);
    }

    public function table(Request $request): View
    {
        abort_unless($request->user()->can('easykhata.manage-transactions'), 403);

        $filters = $request->only(['date_from', 'date_to', 'customer_id', 'supplier_id', 'category_id', 'payment_method_id', 'type', 'search']);

        $transactions = $this->reportService
            ->filteredQuery($request->user()->company_id, $filters)
            ->with(['attachments'])
            ->latest('transaction_date')->latest('id')
            ->paginate(15)->withQueryString();
        
        return view('ledger::transactions._table', compact('transactions'));
    }

    public function json(LedgerTransaction $transaction): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-transactions'), 403);

        $transaction->load('attachments');

        return response()->json(['data' => $transaction]);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $data = $request->safe()->except('attachments');
        $data['company_id'] = $request->user()->company_id;

        $transaction = $this->transactionService->create($data);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ledger/attachments', 'public');
                LedgerTransactionAttachment::create([
                    'ledger_transaction_id' => $transaction->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('Transaction recorded successfully.'),
            'data' => $transaction,
        ]);
    }

    public function update(UpdateTransactionRequest $request, LedgerTransaction $transaction): JsonResponse
    {
        $this->transactionService->update($transaction, $request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Transaction updated successfully.'),
            'data' => $transaction,
        ]);
    }

    public function destroy(LedgerTransaction $transaction): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-transactions'), 403);

        $this->transactionService->delete($transaction);

        return response()->json(['success' => true, 'message' => __('Transaction deleted.')]);
    }

    public function deleteAttachment(LedgerTransactionAttachment $attachment): JsonResponse
    {
        abort_unless(request()->user()->can('easykhata.manage-transactions'), 403);

        \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(['success' => true, 'message' => __('Attachment removed.')]);
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
        abort_unless(request()->user()->can('easykhata.manage-transactions'), 403);

        $headers = ['date', 'type', 'customer', 'supplier', 'category', 'payment_method', 'amount', 'reference_no', 'notes'];
        $sample = ['2026-06-28', 'debit', 'John Doe', '', '', 'Cash', '5000', 'INV-001', 'Sample note'];

        $csv = implode(',', $headers) . "\n" . implode(',', $sample) . "\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions-import-template.csv"',
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('easykhata.manage-transactions'), 403);

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        $import = new TransactionsImport(
            $request->user()->company_id,
            $request->user()->id,
            app(\Modules\Ledger\Services\LedgerTransactionService::class)
        );

        Excel::import($import, $request->file('file'));

        return response()->json([
            'success' => true,
            'imported_count' => count($import->imported),
            'failed_count' => count($import->failed),
            'failed' => $import->failed,
            'message' => __(':count transactions imported successfully.', ['count' => count($import->imported)]),
        ]);
    }
}