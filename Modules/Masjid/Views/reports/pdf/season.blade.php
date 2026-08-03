@php
ob_start();
@endphp
<table style="width:100%; margin-bottom:12px;">
    <tr>
        <?php $stats = [
            __('Members') => $summary['total_members'],
            __('Total Due') => formatCurrency($summary['total_due']),
            __('Collected') => formatCurrency($summary['total_paid']),
            __('Outstanding') => formatCurrency($summary['total_outstanding']),
        ]; ?>
        <?php foreach ($stats as $label => $value): ?>
        <td style="width:25%; padding-right:6px;">
            <div class="stat-box">
                <div class="stat-label"><?= $label ?></div>
                <div class="stat-value"><?= $value ?></div>
            </div>
        </td>
        <?php endforeach; ?>
    </tr>
</table>

<table class="dt">
    <thead>
        <tr>
            <th>{{ __('Member') }}</th>
            <th>{{ __('Mobile') }}</th>
            <th class="text-end">{{ __('Due') }}</th>
            <th class="text-end">{{ __('Paid') }}</th>
            <th class="text-end">{{ __('Balance') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($assignments as $sm): ?>
        <tr>
            <td><?= $sm->member?->name ?? '—' ?></td>
            <td><?= $sm->member?->mobile ?? '—' ?></td>
            <td class="text-end"><?= formatCurrency($sm->amount_due) ?></td>
            <td class="text-end text-success"><?= formatCurrency($sm->amount_paid) ?></td>
            <td class="text-end <?= $sm->balance() > 0 ? 'text-danger' : 'text-success' ?>"><?= formatCurrency(abs($sm->balance())) ?></td>
            <td><span class="badge-<?= $sm->status ?>"><?= ucfirst($sm->status) ?></span></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">{{ __('Total') }}</td>
            <td class="text-end"><?= formatCurrency($summary['total_due']) ?></td>
            <td class="text-end text-success"><?= formatCurrency($summary['total_paid']) ?></td>
            <td class="text-end text-danger"><?= formatCurrency($summary['total_outstanding']) ?></td>
            <td></td>
        </tr>
    </tfoot>
</table>
@php $content = ob_get_clean(); @endphp

@include('masjid::reports.pdf.layout',[
    'letterhead' => $letterhead,
    'reportTitle' => $season->name . ' — ' . __('Season Report'),
    'reportMeta' => formatDate($season->start_date)
        . ' — '
        . formatDate($season->end_date)
        . ' · '
        . formatCurrency($season->contribution_amount)
        . ' ' . __('per member'),
    'content' => $content,
])