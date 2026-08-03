<?php

namespace Modules\Ledger\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Ledger\Models\LedgerCustomer;
use Modules\Ledger\Models\LedgerSupplier;
use Modules\Ledger\Models\LedgerTransaction;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = trim((string) $request->get('q'));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        if ($user->can('easykhata.manage-customers')) {
            $customers = LedgerCustomer::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('mobile', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })->take(5)->get();

            foreach ($customers as $c) {
                $results[] = [
                    'group' => __('Customers'),
                    'icon' => 'bi-person',
                    'title' => $c->name,
                    'subtitle' => $c->mobile ?? $c->email ?? '',
                    'url' => route('ledger.customers.show', $c),
                ];
            }
        }

        if ($user->can('easykhata.manage-suppliers')) {
            $suppliers = LedgerSupplier::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('mobile', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })->take(5)->get();

            foreach ($suppliers as $s) {
                $results[] = [
                    'group' => __('Suppliers'),
                    'icon' => 'bi-truck',
                    'title' => $s->name,
                    'subtitle' => $s->mobile ?? $s->email ?? '',
                    'url' => route('ledger.suppliers.show', $s),
                ];
            }
        }

        if ($user->can('easykhata.manage-transactions')) {
            $transactions = LedgerTransaction::where(function ($q) use ($query) {
                $q->where('reference_no', 'like', "%{$query}%")
                  ->orWhere('notes', 'like', "%{$query}%")
                  ->orWhere('amount', 'like', "%{$query}%");
            })->with(['customer', 'supplier'])->take(5)->get();

            foreach ($transactions as $t) {
                $results[] = [
                    'group' => __('Transactions'),
                    'icon' => 'bi-arrow-left-right',
                    'title' => formatCurrency($t->amount) . ' — ' . ucfirst(str_replace('_', ' ', $t->type)),
                    'subtitle' => ($t->customer?->name ?? $t->supplier?->name ?? '') . ' · ' . formatDate($t->transaction_date),
                    'url' => route('ledger.transactions.index') . '?search=' . urlencode($t->reference_no ?? $t->amount),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}