@php
ob_start();
@endphp
<table style="width:100%; margin-bottom:12px;">
    <tr>
        <td style="width:33%;">
            <div class="stat-box">
                <div class="stat-label">{{ __('Total Due') }}</div>
                <div class="stat-value">{{ formatCurrency($statement['total_due']) }}</div>
            </div>
        </td>
        <td style="width:2%;"></td>
        <td style="width:33%;">
            <div class="stat-box">
                <div class="stat-label">{{ __('Total Paid') }}</div>
                <div class="stat-value text-success">{{ formatCurrency($statement['total_paid']) }}</div>
            </div>
        </td>
        <td style="width:2%;"></td>
        <td style="width:30%;">
            <div class="stat-box">
                <div class="stat-label">{{ __('Balance') }}</div>
                <div class="stat-value {{ $statement['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                    {{ formatCurrency(abs($statement['balance'])) }}
                    {{ $statement['balance'] > 0 ? '('.__('Outstanding').')' : '('.__('Settled').')' }}
                </div>
            </div>
        </td>
    </tr>
</table>

<?php foreach ($statement['assignments'] as $sm): ?>
<div style="margin-bottom:14px;">
    <div style="font-weight:bold; color:#1B6B45; font-size:11px; margin-bottom:4px; border-bottom:1px solid #D1FAE5; padding-bottom:3px;">
        <?= $sm->season->name ?>
        <span class="badge-<?= $sm->status ?>"><?= ucfirst($sm->status) ?></span>
        <span style="float:right; font-size:10px; color:#6B7280;">
            {{ __('Due') }}: <?= formatCurrency($sm->amount_due) ?> |
            {{ __('Paid') }}: <?= formatCurrency($sm->amount_paid) ?>
        </span>
    </div>
    <?php if ($sm->payments->isNotEmpty()): ?>
    <table class="dt">
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Receipt') }}</th>
                <th>{{ __('Method') }}</th>
                <th>{{ __('Received By') }}</th>
                <th class="text-end">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sm->payments as $pay): ?>
            <tr>
                <td><?= formatDate($pay->payment_date) ?></td>
                <td><?= $pay->receipt_no ?? '—' ?></td>
                <td><?= ucfirst($pay->payment_method) ?></td>
                <td><?= $pay->receivedBy?->name ?? '—' ?></td>
                <td class="text-end text-success"><?= formatCurrency($pay->amount_paid) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="font-size:10px; color:#9CA3AF;">{{ __('No payments recorded.') }}</div>
    <?php endif; ?>
</div>
<?php endforeach;?>
@php
$content = ob_get_clean();
@endphp

@include('masjid::reports.pdf.layout', [
    'letterhead' => $letterhead,
    'reportTitle' => __('Member Statement') . ' — ' . $member->name,
    'reportMeta' => ($member->mobile ? __('Mobile') . ': ' . $member->mobile . '   ' : '')
        . ($member->father_name ? __('S/O') . ' ' . $member->father_name . '   ' : '')
        . __('As of') . ' ' . formatDate(now()),
    'content' => $content,
]);