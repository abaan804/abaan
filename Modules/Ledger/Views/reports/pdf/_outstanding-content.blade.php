<table class="data-table">
    <thead>
        <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Mobile') }}</th>
            <th class="text-end">{{ __('Balance') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row['party']->name }}</td>
                <td>{{ $row['party']->mobile ?? '—' }}</td>
                <td class="text-end {{ $type === 'payables' ? 'text-danger' : 'text-success' }}">{{ formatCurrency($row['balance']) }}</td>
            </tr>
        @empty
            <tr><td colspan="3" style="text-align:center;">{{ __('No outstanding balances') }}</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">{{ __('Total') }}</td>
            <td class="text-end">{{ formatCurrency($rows->sum('balance')) }}</td>
        </tr>
    </tfoot>
</table>