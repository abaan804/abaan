@php
ob_start();
@endphp
<table class="dt">
    <thead>
        <tr>
            <th>{{ __('Member') }}</th>
            <th>{{ __('Mobile') }}</th>
            <th>{{ __('Season') }}</th>
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
            <td><?= $sm->season?->name ?? '—' ?></td>
            <td class="text-end"><?= formatCurrency($sm->amount_due) ?></td>
            <td class="text-end text-success"><?= formatCurrency($sm->amount_paid) ?></td>
            <td class="text-end text-danger"><?= formatCurrency(abs($sm->balance())) ?></td>
            <td><span class="badge-<?= $sm->status ?>"><?= ucfirst($sm->status) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if ($assignments->isEmpty()): ?>
        <tr><td colspan="7" style="text-align:center;">{{ __('No outstanding balances.') }}</td></tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">{{ __('Total Outstanding') }}</td>
            <td class="text-end text-danger"><?= formatCurrency($assignments->sum(fn($a) => $a->balance())) ?></td>
            <td></td>
        </tr>
    </tfoot>
</table>
@php $content = ob_get_clean(); @endphp

@include('masjid::reports.pdf.layout', [
    'letterhead' => $letterhead,
    'reportTitle' => __('Outstanding Report'),
    'reportMeta' => __('As of') . ' ' . formatDate(now()) . ' · ' . $assignments->count() . ' ' . __('members'),
    'content' => $content,
])