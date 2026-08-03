@php
    $content = view('ledger::reports.pdf._outstanding-content', ['rows' => $rows, 'type' => $type])->render();
@endphp
@include('ledger::reports.pdf.layout', [
    'letterhead' => $letterhead,
    'reportTitle' => $type === 'payables' ? __('Outstanding Payables') : __('Outstanding Receivables'),
    'reportMeta' => __('As of') . ' ' . formatDate(now()),
    'content' => $content,
])