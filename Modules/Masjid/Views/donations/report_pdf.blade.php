<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: dejavusans; font-size: 10px; color: #1a252f; margin: 0; }
        .header { background: #1B6B45; color: #fff; padding: 12px 16px; margin-bottom: 12px; }
        .header .mosque-name { font-size: 16px; font-weight: bold; }
        .header .sub { font-size: 9px; opacity: .85; }
        .report-title { font-size: 13px; font-weight: bold; color: #1B6B45; margin-bottom: 4px; }
        .report-meta  { font-size: 9px; color: #7f8c8d; margin-bottom: 12px; }

        .stats { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .stats td { padding: 8px 12px; border: 1px solid #e5e7eb; text-align: center; }
        .stats .stat-label { font-size: 9px; color: #6b7280; }
        .stats .stat-val   { font-size: 14px; font-weight: bold; color: #1B6B45; }

        table.dt { width: 100%; border-collapse: collapse; }
        table.dt th {
            background: #1B6B45; color: #fff;
            padding: 6px 7px; font-size: 9px; text-align: left;
        }
        table.dt td {
            padding: 5px 7px; font-size: 9px;
            border-bottom: 1px solid #f2f3f4;
        }
        table.dt tr:nth-child(even) td { background: #f9fafb; }
        table.dt tfoot td {
            font-weight: bold; border-top: 2px solid #1B6B45; font-size: 10px;
        }
        .badge-named { background:#dbeafe; color:#1e40af; padding:1px 5px; border-radius:3px; }
        .badge-anon  { background:#f3f4f6; color:#374151; padding:1px 5px; border-radius:3px; }
        .footer { margin-top: 18px; font-size: 8px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <div class="mosque-name">{{ $mosque->mosque_name }}</div>
    <div class="sub">
        {{ $mosque->village_name ?? '' }}{{ $mosque->city ? ', ' . $mosque->city : '' }}
    </div>
</div>

<div class="report-title">{{ __('Donation Report') }}</div>
<div class="report-meta">
    {{ __('Generated on') }} {{ now()->format('d M Y, h:i A') }}
    @if ($request->get('date_from') || $request->get('date_to'))
        &mdash; {{ $request->get('date_from') ?? '—' }} {{ __('to') }} {{ $request->get('date_to') ?? '—' }}
    @endif
</div>

{{-- Summary --}}
<table class="stats">
    <tr>
        <td>
            <div class="stat-label">{{ __('Total') }}</div>
            <div class="stat-val">{{ formatCurrency($total) }}</div>
        </td>
        <td>
            <div class="stat-label">{{ __('Records') }}</div>
            <div class="stat-val">{{ $donations->count() }}</div>
        </td>
        <td>
            <div class="stat-label">{{ __('Named') }}</div>
            <div class="stat-val">{{ formatCurrency($donations->where('type','named')->sum('amount')) }}</div>
        </td>
        <td>
            <div class="stat-label">{{ __('Anonymous') }}</div>
            <div class="stat-val">{{ formatCurrency($donations->where('type','anonymous')->sum('amount')) }}</div>
        </td>
    </tr>
</table>

<table class="dt">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Donor') }}</th>
            <th>{{ __('Mobile') }}</th>
            <th>{{ __('Purpose') }}</th>
            <th>{{ __('Season') }}</th>
            <th>{{ __('Date') }}</th>
            <th style="text-align:right;">{{ __('Amount') }}</th>
            <th>{{ __('Receipt') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($donations as $i => $d)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    @if ($d->type === 'named')
                        <span class="badge-named">{{ __('Named') }}</span>
                    @else
                        <span class="badge-anon">{{ __('Anon.') }}</span>
                    @endif
                </td>
                <td>{{ $d->donor_display_name }}</td>
                <td>{{ $d->donor_mobile ?? '—' }}</td>
                <td>{{ $d->purpose ?? '—' }}</td>
                <td>{{ $d->season?->name ?? '—' }}</td>
                <td>{{ formatDate($d->donation_date) }}</td>
                <td style="text-align:right; font-weight:bold; color:#166534;">
                    {{ formatCurrency($d->amount) }}
                </td>
                <td>{{ $d->receipt_no ?? '—' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7">{{ __('Total') }}: {{ $donations->count() }} {{ __('records') }}</td>
            <td style="text-align:right; color:#166534;">{{ formatCurrency($total) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    {{ $mosque->mosque_name }} &mdash; {{ __('Donation Report') }} &mdash; {{ now()->format('d M Y') }}
</div>

</body>
</html>