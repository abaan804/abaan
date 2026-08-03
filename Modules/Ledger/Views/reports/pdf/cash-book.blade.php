@php
    $content = view('ledger::reports.pdf._cash-book-content', ['rows' => $rows, 'totals' => $totals])->render();
@endphp
@include('ledger::reports.pdf.layout', [
    'letterhead' => $letterhead,
    'reportTitle' => __('Cash Book'),
    'reportMeta' => __('Period') . ': ' . $dateFrom . ' — ' . $dateTo,
    'content' => $content,
])