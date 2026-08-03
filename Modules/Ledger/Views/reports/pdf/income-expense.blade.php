@php
    $content = view('ledger::reports.pdf._income-expense-content', ['byCategory' => $byCategory, 'totals' => $totals])->render();
@endphp
@include('ledger::reports.pdf.layout', [
    'letterhead' => $letterhead,
    'reportTitle' => __('Income & Expense / Profit-Loss'),
    'reportMeta' => __('Period') . ': ' . $dateFrom . ' — ' . $dateTo,
    'content' => $content,
])