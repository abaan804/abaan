<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: dejavusans;
            font-size: 11px;
            color: #1a252f;
            margin: 0;
        }

        /* Header */
        .slip-header {
            background: #1B6B45;
            color: #fff;
            padding: 14px 18px;
            margin-bottom: 0;
        }
        .mosque-name { font-size: 17px; font-weight: bold; margin-bottom: 3px; }
        .mosque-sub  { font-size: 10px; opacity: .85; }

        /* Title band */
        .slip-title {
            background: #C9A84C;
            color: #fff;
            text-align: center;
            padding: 6px;
            font-weight: bold;
            font-size: 12px;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        /* Receipt no badge (in header) */
        .receipt-no {
            float: right;
            background: rgba(255,255,255,.22);
            border: 1px solid rgba(255,255,255,.35);
            color: #fff;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            margin-top: -2px;
        }

        /* Amount box */
        .amount-box {
            background: #f0fdf4;
            border: 2px solid #22c55e;
            border-radius: 10px;
            text-align: center;
            padding: 12px;
            margin: 0 16px 16px;
        }
        .amount-label { color: #166534; font-size: 10px; font-weight: bold; }
        .amount-value { font-size: 26px; font-weight: 800; color: #15803d; }

        /* Info rows */
        .info-table { width: 100%; border-collapse: collapse; margin: 0 0 14px; }
        .info-table td {
            padding: 5px 18px;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 10.5px;
        }
        .info-table .label { color: #6b7280; width: 38%; }
        .info-table .value { font-weight: 600; text-align: right; }

        /* Notes */
        .notes-box {
            margin: 0 16px 14px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 10px;
        }
        .notes-title { font-weight: bold; color: #6b7280; margin-bottom: 4px; }

        /* Footer */
        .slip-footer {
            border-top: 1px solid #e5e7eb;
            padding: 12px 18px;
            margin-top: 6px;
        }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { vertical-align: bottom; }
        .dua { font-style: italic; color: #6b7280; font-size: 9px; max-width: 260px; }
        .sig-line { border-top: 1px solid #374151; width: 120px; margin-bottom: 4px; }
        .sig-label { font-size: 9px; color: #6b7280; }
        .sig-name  { font-size: 9px; font-weight: bold; }

        /* Watermark */
        .watermark {
            text-align: center;
            font-size: 8.5px;
            color: #9ca3af;
            padding: 6px;
            letter-spacing: .04em;
        }
    </style>
</head>
<body>

{{-- Header --}}
<div class="slip-header">
    @if ($donation->receipt_no)
        <span class="receipt-no">{{ __('Receipt') }} #{{ $donation->receipt_no }}</span>
    @endif
    <div class="mosque-name">{{ $mosque->mosque_name }}</div>
    <div class="mosque-sub">
        @if ($mosque->village_name) {{ $mosque->village_name }}, @endif
        {{ $mosque->city ?? '' }}{{ $mosque->country ? ', ' . $mosque->country : '' }}
    </div>
    @if ($mosque->mosque_contact)
        <div class="mosque-sub">{{ __('Tel') }}: {{ $mosque->mosque_contact }}</div>
    @endif
</div>

{{-- Title Band --}}
<div class="slip-title">{{ __('Donation Receipt') }}</div>

{{-- Amount Box --}}
<div class="amount-box">
    <div class="amount-label">{{ __('Amount Donated') }}</div>
    <div class="amount-value">{{ formatCurrency($donation->amount) }}</div>
</div>

{{-- Info Rows --}}
<table class="info-table">
    <tr>
        <td class="label">{{ __('Donor Name') }}</td>
        <td class="value">{{ $donation->donor_name }}</td>
    </tr>
    @if ($donation->donor_mobile)
    <tr>
        <td class="label">{{ __('Mobile') }}</td>
        <td class="value">{{ $donation->donor_mobile }}</td>
    </tr>
    @endif
    @if ($donation->donor_address)
    <tr>
        <td class="label">{{ __('Address') }}</td>
        <td class="value">{{ $donation->donor_address }}</td>
    </tr>
    @endif
    @if ($donation->purpose)
    <tr>
        <td class="label">{{ __('Purpose') }}</td>
        <td class="value">{{ $donation->purpose }}</td>
    </tr>
    @endif
    @if ($donation->season)
    <tr>
        <td class="label">{{ __('Season') }}</td>
        <td class="value">{{ $donation->season->name }}</td>
    </tr>
    @endif
    <tr>
        <td class="label">{{ __('Donation Date') }}</td>
        <td class="value">{{ formatDate($donation->donation_date) }}</td>
    </tr>
    @if ($donation->day_description)
    <tr>
        <td class="label">{{ __('Occasion') }}</td>
        <td class="value">{{ $donation->day_description }}</td>
    </tr>
    @endif
    @if ($donation->receivedBy)
    <tr>
        <td class="label">{{ __('Received By') }}</td>
        <td class="value">{{ $donation->receivedBy->name }}</td>
    </tr>
    @endif
    <tr>
        <td class="label">{{ __('Issued On') }}</td>
        <td class="value">{{ now()->format('d M Y, h:i A') }}</td>
    </tr>
</table>

@if ($donation->notes)
    <div class="notes-box">
        <div class="notes-title">{{ __('Notes') }}</div>
        {{ $donation->notes }}
    </div>
@endif

{{-- Footer --}}
<div class="slip-footer">
    <table class="footer-table">
        <tr>
            <td style="width:55%;">
                <p class="dua">
                    "{{ __('May Allah accept your donation and reward you with the best in this world and the hereafter. Ameen.') }}"
                </p>
            </td>
            <td style="text-align:right; width:45%;">
                <div class="sig-line" style="margin-left:auto;"></div>
                <div class="sig-label">{{ __('Authorized Signature') }}</div>
                @if ($mosque->scholar_name)
                    <div class="sig-name">{{ $mosque->scholar_name }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="watermark">
    {{ $mosque->mosque_name }} &mdash;
    {{ __('This is a computer-generated receipt') }} &mdash;
    {{ now()->format('d M Y') }}
</div>

</body>
</html>