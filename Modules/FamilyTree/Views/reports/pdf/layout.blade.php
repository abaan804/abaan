<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: dejavusans; font-size: 11px; color: #1a252f; margin: 0; }

        .letterhead { width: 100%; border-bottom: 2.5px solid #1a5276; padding-bottom: 10px; margin-bottom: 14px; }
        .letterhead table { width: 100%; }
        .family-name { font-size: 18px; font-weight: bold; color: #1a5276; }
        .family-meta { font-size: 10px; color: #7f8c8d; line-height: 1.6; }

        .report-title { font-size: 14px; font-weight: bold; color: #1a5276; margin: 12px 0 4px; }
        .report-meta  { font-size: 10px; color: #7f8c8d; margin-bottom: 12px; }

        table.dt { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.dt th {
            background: #eaf2f8; padding: 6px 8px;
            font-size: 10px; text-align: left;
            border-bottom: 1px solid #aed6f1;
        }
        table.dt td { padding: 5px 8px; font-size: 10px; border-bottom: 1px solid #f2f3f4; }
        table.dt tfoot td { font-weight: bold; border-top: 2px solid #1a5276; }

        .text-end   { text-align: right; }
        .male-color { color: #1a5276; }
        .female-color { color: #8e44ad; }
        .deceased   { color: #7f8c8d; font-style: italic; }

        .badge-living   { background: #d5f5e3; color: #1e8449; padding: 1px 5px; border-radius: 3px; font-size: 9px; }
        .badge-deceased { background: #f2f3f4; color: #566573; padding: 1px 5px; border-radius: 3px; font-size: 9px; }
        .badge-male     { background: #d6eaf8; color: #1a5276; padding: 1px 5px; border-radius: 3px; font-size: 9px; }
        .badge-female   { background: #e8daef; color: #8e44ad; padding: 1px 5px; border-radius: 3px; font-size: 9px; }
        .badge-missing  { background: #fef9e7; color: #b7950b; padding: 1px 5px; border-radius: 3px; font-size: 9px; }

        .stat-row table { width: 100%; margin-bottom: 12px; }
        .stat-box { border: 1px solid #d6eaf8; border-radius: 5px; padding: 7px 10px; background: #eaf2f8; text-align: center; }
        .stat-label { font-size: 9px; color: #7f8c8d; }
        .stat-value { font-size: 14px; font-weight: bold; color: #1a5276; }

        .footer-note { margin-top: 20px; font-size: 9px; color: #aab7b8; text-align: center; }
    </style>
</head>
<body>
    {{-- Letterhead --}}
    <div class="letterhead">
        <table>
            <tr>
                <td style="width:75%; vertical-align:top;">
                    <div class="family-name">{{ $letterhead['family_name'] }}</div>
                    <div class="family-meta">
                        @if ($letterhead['village']) {{ $letterhead['village'] }}, @endif
                        @if ($letterhead['city']) {{ $letterhead['city'] }}, @endif
                        {{ $letterhead['country'] ?? 'Pakistan' }}
                    </div>
                </td>
                <td style="width:25%; text-align:right; vertical-align:top;">
                    @if ($letterhead['photo'] && file_exists($letterhead['photo']))
                        <img src="{{ $letterhead['photo'] }}" style="max-height:50px; border-radius:6px;">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">{{ $reportTitle }}</div>
    <div class="report-meta">{{ $reportMeta }}</div>

    {!! $content !!}

    <div class="footer-note">
        {{ __('Generated on') }} {{ now()->format('d M Y, h:i A') }} — {{ __('Family Tree Manager') }}
    </div>
</body>
</html>