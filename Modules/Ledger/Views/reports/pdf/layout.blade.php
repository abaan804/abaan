<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
         body {
        font-family: {{ app()->getLocale() === 'ur' ? 'jameelnoorinastaleeq' : (app()->getLocale() === 'ar' ? 'notonaskharabic' : 'dejavusans') }};
        font-size: {{ app()->getLocale() === 'ur' ? '13px' : '11px' }};
        color: #1e293b;
        };  
        .letterhead { width: 100%; margin-bottom: 16px; border-bottom: 2px solid #2563EB; padding-bottom: 10px; }
        .letterhead table { width: 100%; }
        .company-name { font-size: 18px; font-weight: bold; color: #15233B; }
        .company-meta { font-size: 10px; color: #64748B; line-height: 1.5; }
        .report-title { font-size: 15px; font-weight: bold; margin: 14px 0 4px; color: #2563EB; }
        .report-meta { font-size: 10px; color: #64748B; margin-bottom: 12px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data-table th { background: #F1F5F9; padding: 6px 8px; font-size: 10px; text-align: left; border-bottom: 1px solid #E2E8F0; }
        table.data-table td { padding: 6px 8px; font-size: 10px; border-bottom: 1px solid #F1F5F9; }
        table.data-table tfoot td { font-weight: bold; border-top: 2px solid #2563EB; }
        .text-end { text-align: right; }
        .text-success { color: #16A34A; }
        .text-danger { color: #DC2626; }
        .footer-note { margin-top: 20px; font-size: 9px; color: #94A3B8; text-align: center; }
    </style>
</head>
<body>
    <div class="letterhead">
        <table>
            <tr>
                <td style="width: 70%; vertical-align: top;">
                    <div class="company-name">{{ $letterhead['name'] }}</div>
                    <div class="company-meta">
                        @if ($letterhead['address']) {{ $letterhead['address'] }}<br> @endif
                        @if ($letterhead['phone']) {{ __('Phone') }}: {{ $letterhead['phone'] }} @endif
                        @if ($letterhead['email']) &nbsp;|&nbsp; {{ $letterhead['email'] }} @endif
                    </div>
                </td>
                <td style="width: 30%; text-align: right; vertical-align: top;">
                    @if ($letterhead['logo'] && file_exists($letterhead['logo']))
                        <img src="{{ $letterhead['logo'] }}" style="max-height: 50px;">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">{{ $reportTitle }}</div>
    <div class="report-meta">{{ $reportMeta }}</div>

    {!! $content !!}

    <div class="footer-note">
        {{ __('Generated on') }} {{ now()->format('M d, Y h:i A') }} {{ __('via EasyKhata') }}
    </div>
</body>
</html>