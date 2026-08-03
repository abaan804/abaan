@php
    $content = view('ledger::reports.pdf._supplier-statement-content', [
        'supplier' => $supplier, 'balance' => $balance, 'totalDebit' => $totalDebit,
        'totalCredit' => $totalCredit, 'transactions' => $transactions,
    ])->render();
@endphp
@include('ledger::reports.pdf.layout', [
    'letterhead' => $letterhead,
    'reportTitle' => __('Supplier Statement') . ' — ' . $supplier->name,
    'reportMeta' => ($supplier->mobile ? __('Mobile') . ': ' . $supplier->mobile . '   ' : '') . __('As of') . ' ' . formatDate(now()),
    'content' => $content,
])