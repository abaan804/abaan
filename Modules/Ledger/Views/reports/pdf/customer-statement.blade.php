@php
    $content = view('ledger::reports.pdf._customer-statement-content', [
        'customer' => $customer, 'balance' => $balance, 'totalDebit' => $totalDebit,
        'totalCredit' => $totalCredit, 'transactions' => $transactions,
    ])->render();
@endphp
@include('ledger::reports.pdf.layout', [
    'letterhead' => $letterhead,
    'reportTitle' => __('Customer Statement') . ' — ' . $customer->name,
    'reportMeta' => ($customer->mobile ? __('Mobile') . ': ' . $customer->mobile . '   ' : '') . __('As of') . ' ' . formatDate(now()),
    'content' => $content,
])