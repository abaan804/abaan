<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ur','ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Donation Slip') }} — {{ $donation->receipt_no ?? '#' . $donation->id }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ in_array(app()->getLocale(), ['ur','ar']) ? '.rtl' : '' }}.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', sans-serif; }

        .slip-wrapper {
            max-width: 620px; margin: 2rem auto;
            background: #fff; border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.12);
            overflow: hidden;
        }

        /* Header */
        .slip-header {
            background: linear-gradient(135deg, #1B6B45, #144d32);
            color: #fff; padding: 1.75rem 2rem; position: relative;
        }
        .slip-header .mosque-name {
            font-size: 1.4rem; font-weight: 700; margin-bottom: .25rem;
        }
        .slip-header .mosque-sub { opacity: .8; font-size: .9rem; }
        .slip-header .badge-receipt {
            position: absolute; top: 1.5rem; inset-inline-end: 1.75rem;
            background: rgba(255,255,255,.2);
            border: 1px solid rgba(255,255,255,.35);
            color: #fff; padding: .4rem 1rem;
            border-radius: 20px; font-size: .85rem; font-weight: 600;
        }

        /* Title band */
        .slip-title-band {
            background: #C9A84C; color: #fff;
            text-align: center; padding: .6rem;
            font-weight: 700; letter-spacing: .08em; font-size: .95rem;
            text-transform: uppercase;
        }

        /* Body */
        .slip-body { padding: 1.75rem 2rem; }

        .slip-row {
            display: flex; justify-content: space-between;
            align-items: flex-start; padding: .6rem 0;
            border-bottom: 1px dashed #e5e7eb;
            gap: 1rem;
        }
        .slip-row:last-child { border-bottom: none; }
        .slip-label {
            color: #6b7280; font-size: .85rem;
            min-width: 140px; flex-shrink: 0;
        }
        .slip-value {
            font-weight: 600; text-align: end; font-size: .9rem;
        }

        /* Amount highlight */
        .slip-amount-box {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 2px solid #22c55e;
            border-radius: 12px; padding: 1rem 1.5rem;
            text-align: center; margin: 1.25rem 0;
        }
        .slip-amount-box .amount-label { color: #166534; font-size: .85rem; font-weight: 600; }
        .slip-amount-box .amount-value {
            font-size: 2.2rem; font-weight: 800;
            color: #15803d; line-height: 1.1;
        }

        /* Footer */
        .slip-footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 1rem 2rem;
            display: flex; justify-content: space-between;
            align-items: flex-end; gap: 1rem;
        }
        .slip-footer .dua {
            font-size: .82rem; color: #6b7280;
            font-style: italic; max-width: 260px;
        }
        .slip-signature {
            text-align: center; min-width: 130px;
        }
        .slip-signature .sig-line {
            border-top: 1.5px solid #374151;
            margin-bottom: .25rem; width: 120px;
        }
        .slip-signature .sig-label {
            font-size: .75rem; color: #6b7280;
        }

        /* Watermark */
        .slip-watermark {
            text-align: center; padding: .5rem;
            font-size: .72rem; color: #9ca3af; letter-spacing: .05em;
        }

        /* Print styles */
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .slip-wrapper { box-shadow: none; margin: 0; border-radius: 0; }
        }
    </style>
</head>
<body>

{{-- Print / Download actions --}}
<div class="no-print d-flex justify-content-center gap-3 py-3">
    <button class="btn btn-primary" onclick="window.print()">
        <i class="bi bi-printer"></i> {{ __('Print Slip') }}
    </button>
    <a href="{{ route('masjid.mosque.donations.slip.pdf', [$mosque, $donation]) }}"
       class="btn btn-outline-danger">
        <i class="bi bi-file-earmark-pdf"></i> {{ __('Download PDF') }}
    </a>
    <a href="{{ route('masjid.mosque.donations.report', [$mosque,'standalone=>1']) }}"
       class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('Back to Report') }}
    </a>
</div>

<div class="slip-wrapper">

    {{-- Header --}}
    <div class="slip-header">
        <div class="mosque-name">{{ $mosque->mosque_name }}</div>
        <div class="mosque-sub">
            @if ($mosque->village_name) {{ $mosque->village_name }}, @endif
            {{ $mosque->city ?? '' }} {{ $mosque->country ? ', ' . $mosque->country : '' }}
        </div>
        @if ($mosque->mosque_contact)
            <div class="mosque-sub mt-1">
                <i class="bi bi-telephone"></i> {{ $mosque->mosque_contact }}
            </div>
        @endif
        @if ($donation->receipt_no)
            <div class="badge-receipt">
                {{ __('Receipt') }} #{{ $donation->receipt_no }}
            </div>
        @endif
    </div>

    {{-- Title Band --}}
    <div class="slip-title-band">
        <i class="bi bi-gift"></i> {{ __('Donation Receipt') }}
    </div>

    {{-- Body --}}
    <div class="slip-body">

        {{-- Amount highlight --}}
        <div class="slip-amount-box">
            <div class="amount-label">{{ __('Amount Donated') }}</div>
            <div class="amount-value">{{ formatCurrency($donation->amount) }}</div>
        </div>

        {{-- Donor Details --}}
        <div class="slip-row">
            <span class="slip-label">{{ __('Donor Name') }}</span>
            <span class="slip-value">{{ $donation->donor_name }}</span>
        </div>
        @if ($donation->donor_mobile)
            <div class="slip-row">
                <span class="slip-label">{{ __('Mobile') }}</span>
                <span class="slip-value">{{ $donation->donor_mobile }}</span>
            </div>
        @endif
        @if ($donation->donor_address)
            <div class="slip-row">
                <span class="slip-label">{{ __('Address') }}</span>
                <span class="slip-value">{{ $donation->donor_address }}</span>
            </div>
        @endif
        @if ($donation->purpose)
            <div class="slip-row">
                <span class="slip-label">{{ __('Purpose') }}</span>
                <span class="slip-value">{{ $donation->purpose }}</span>
            </div>
        @endif
        @if ($donation->season)
            <div class="slip-row">
                <span class="slip-label">{{ __('Season') }}</span>
                <span class="slip-value">{{ $donation->season->name }}</span>
            </div>
        @endif
        <div class="slip-row">
            <span class="slip-label">{{ __('Donation Date') }}</span>
            <span class="slip-value">{{ formatDate($donation->donation_date) }}</span>
        </div>
        @if ($donation->day_description)
            <div class="slip-row">
                <span class="slip-label">{{ __('Occasion') }}</span>
                <span class="slip-value">{{ $donation->day_description }}</span>
            </div>
        @endif
        @if ($donation->receivedBy)
            <div class="slip-row">
                <span class="slip-label">{{ __('Received By') }}</span>
                <span class="slip-value">{{ $donation->receivedBy->name }}</span>
            </div>
        @endif
        <div class="slip-row">
            <span class="slip-label">{{ __('Issued On') }}</span>
            <span class="slip-value">{{ now()->format('d M Y, h:i A') }}</span>
        </div>
        @if ($donation->notes)
            <div class="mt-3 p-3 rounded" style="background:#f9fafb;border:1px solid #e5e7eb;">
                <div class="text-muted small fw-semibold mb-1">{{ __('Notes') }}</div>
                <div class="small">{{ $donation->notes }}</div>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="slip-footer">
        <div class="dua">
            "{{ __('May Allah accept your donation and reward you with the best in this world and the hereafter. Ameen.') }}"
        </div>
        <div class="slip-signature">
            <div class="sig-line"></div>
            <div class="sig-label">{{ __('Authorized Signature') }}</div>
            @if ($mosque->scholar_name)
                <div class="sig-label mt-1 fw-semibold">{{ $mosque->scholar_name }}</div>
            @endif
        </div>
    </div>

    <div class="slip-watermark">
        {{ $mosque->mosque_name }} &mdash; {{ __('This is a computer-generated receipt.') }}
    </div>
</div>

</body>
</html>