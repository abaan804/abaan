@php
ob_start();
@endphp
<table class="dt">
    <thead>
        <tr>
            <th>{{ __('Receipt') }}</th>
            <th>{{ __('Member') }}</th>
            <th>{{ __('Season') }}</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Method') }}</th>
            <th class="text-end">{{ __('Amount') }}</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($payments as $p): ?>
        <tr>
            <td><?= $p->receipt_no ?? '—' ?></td>
            <td><?= $p->member?->name ?? '—' ?></td>
            <td><?= $p->season?->name ?? '—' ?></td>
            <td><?= formatDate($p->payment_date) ?></td>
            <td><?= ucfirst($p->payment_method) ?></td>
            <td class="text-end text-success"><?= formatCurrency($p->amount_paid) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if ($payments->isEmpty()): ?>
        <tr><td colspan="6" style="text-align:center;">{{ __('No payments in this period.') }}</td></tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">{{ __('Total') }}</td>
            <td class="text-end text-success"><?= formatCurrency($total) ?></td>
        </tr>
    </tfoot>
</table>
@php $content = ob_get_clean(); @endphp

@include('masjid::reports.pdf.layout', [
    'letterhead' => $letterhead,
    'reportTitle' => __('Collection Report'),
    'reportMeta' => (! empty($filters['date_from']) ? $filters['date_from'] . ' — ' . ($filters['date_to'] ?? now()->toDateString()) : __('All time'))
        . ' · ' . $payments->count() . ' ' . __('records'),
    'content' => $content,
])