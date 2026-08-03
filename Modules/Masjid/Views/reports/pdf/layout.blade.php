<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: dejavusans; font-size: 11px; color: #1e293b; margin: 0; }
        .bismillah { text-align: center; font-size: 20px; color: #1B6B45; margin-bottom: 10px; }
        .letterhead { width: 100%; border-bottom: 2px solid #1B6B45; padding-bottom: 10px; margin-bottom: 14px; }
        .letterhead table { width: 100%; }
        .mosque-name { font-size: 17px; font-weight: bold; color: #1B6B45; }
        .mosque-meta { font-size: 10px; color: #64748B; line-height: 1.6; }
        .report-title { font-size: 14px; font-weight: bold; margin: 14px 0 4px; color: #1B6B45; }
        .report-meta { font-size: 10px; color: #64748B; margin-bottom: 12px; }
        table.dt { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.dt th { background: #F0FFF4; padding: 6px 8px; font-size: 10px; text-align: left; border-bottom: 1px solid #D1FAE5; }
        table.dt td { padding: 6px 8px; font-size: 10px; border-bottom: 1px solid #F1F5F9; }
        table.dt tfoot td { font-weight: bold; border-top: 2px solid #1B6B45; }
        .text-end { text-align: right; }
        .text-success { color: #16A34A; }
        .text-danger { color: #DC2626; }
        .text-info { color: #0891B2; }
        .badge-pending { background: #FEF3C7; color: #92400E; padding: 2px 6px; border-radius: 4px; font-size: 9px; }
        .badge-partial  { background: #DBEAFE; color: #1E40AF; padding: 2px 6px; border-radius: 4px; font-size: 9px; }
        .badge-paid     { background: #DCFCE7; color: #166534; padding: 2px 6px; border-radius: 4px; font-size: 9px; }
        .badge-overpaid { background: #F3E8FF; color: #6B21A8; padding: 2px 6px; border-radius: 4px; font-size: 9px; }
        .footer-note { margin-top: 20px; font-size: 9px; color: #94A3B8; text-align: center; }
        .stat-row { width: 100%; margin-bottom: 12px; }
        .stat-box { border: 1px solid #D1FAE5; border-radius: 6px; padding: 8px 12px; background: #F0FFF4; }
        .stat-label { font-size: 9px; color: #6B7280; }
        .stat-value { font-size: 14px; font-weight: bold; color: #1B6B45; }
    </style>
</head>
<body>
    <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>

    <div class="letterhead">
        <table>
            <tr>
                <td style="width:70%; vertical-align:top;">
                    <div class="mosque-name">{{ $letterhead['mosque_name'] }}</div>
                    <div class="mosque-meta">
                        @if ($letterhead['village_name']) {{ $letterhead['village_name'] }}<br> @endif
                        @if ($letterhead['scholar_name']) {{ __('Scholar') }}: {{ $letterhead['scholar_name'] }}<br> @endif
                        @if ($letterhead['committee_name']) {{ __('Committee') }}: {{ $letterhead['committee_name'] }}<br> @endif
                        @if ($letterhead['mosque_contact']) {{ __('Contact') }}: {{ $letterhead['mosque_contact'] }} @endif
                    </div>
                </td>
                <td style="width:30%; text-align:right; vertical-align:top;">
                    @if ($letterhead['logo'] && file_exists($letterhead['logo']))
                        <img src="{{ $letterhead['logo'] }}" style="max-height:55px;">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">{{ $reportTitle }}</div>
    <div class="report-meta">{{ $reportMeta }}</div>

    {!! $content !!}

    <div class="footer-note">
        {{ __('Generated on') }} {{ now()->format('d M Y, h:i A') }} — {{ __('Masjid Contribution Manager') }}
    </div>
</body>
</html>