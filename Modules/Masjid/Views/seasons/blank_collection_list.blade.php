<!DOCTYPE html>
<html dir="{{ $isUrdu ? 'rtl' : 'ltr' }}" lang="{{ $isUrdu ? 'ur' : 'en' }}">
<head>
    <meta charset="utf-8">
    <style>
        /* ── Base ────────────────────────────────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: {{ $isUrdu ? "'JameelNooriNastaleeq', 'NotoNaskhArabic'" : "'DejaVu Sans', sans-serif" }};
            font-size: {{ $isUrdu ? '12px' : '11px' }};
            color: #1a252f;
            direction: {{ $isUrdu ? 'rtl' : 'ltr' }};
            line-height: {{ $isUrdu ? '2.2' : '1.5' }};
        }

        /* ── Header ──────────────────────────────────────────────────── */
        .header-wrap {
            border-bottom: 3px solid #1B6B45;
            padding-bottom: 10px;
            margin-bottom: 12px;
            text-align: center;
        }

        .mosque-name {
            font-size: {{ $isUrdu ? '22px' : '18px' }};
            font-weight: bold;
            color: #1B6B45;
            margin-bottom: {{ $isUrdu ? '6px' : '3px' }};
            line-height: {{ $isUrdu ? '1.4' : '1.4' }};
        }

        .mosque-address {
            font-size: {{ $isUrdu ? '14px' : '10.5px' }};
            color: #4b5563;
            margin-bottom: {{ $isUrdu ? '4px' : '2px' }};
            line-height: {{ $isUrdu ? '1.2' : '1.5' }};
        }

        .scholar-name {
            font-size: {{ $isUrdu ? '12px' : '10px' }};
            color: #6b7280;
        }

        /* ── Season title band ───────────────────────────────────────── */
        .season-band {
            background: #1B6B45;
            color: #fff;
            text-align: center;
            padding: {{ $isUrdu ? '0px 6px' : '5px 10px' }};
            font-size: {{ $isUrdu ? '12px' : '12px' }};
            font-weight: bold;
            margin-bottom: 10px;
            border-radius: 4px;
            line-height: {{ $isUrdu ? '2.2' : '1.5' }};
        }

        /* ── Summary Row ─────────────────────────────────────────────── */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: {{ $isUrdu ? '12px' : '10px' }};
        }

        .summary-table td {
            border: 1px solid #c9d5dc;
            padding: {{ $isUrdu ? '7px 7px' : '5px 7px' }};
            text-align: center;
            background: #f0f7f4;
            line-height: {{ $isUrdu ? '1.2' : '1.5' }};
        }

        .summary-table .s-label {
            font-size: {{ $isUrdu ? '11px' : '9px' }};
            color: #6b7280;
            display: block;
            line-height: {{ $isUrdu ? '2' : '1.3' }};
        }

        .summary-table .s-value {
            font-weight: bold;
            font-size: {{ $isUrdu ? '12px' : '11px' }};
            color: #1B6B45;
            line-height: {{ $isUrdu ? '2.2' : '1.5' }};
        }

        /* ── Main Table ──────────────────────────────────────────────── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            font-size: {{ $isUrdu ? '12px' : '10px' }};
        }

        .main-table thead tr {
            background: #1B6B45;
            color: #fff;
        }

        .main-table thead th {
            padding: {{ $isUrdu ? '0px 7px' : '0px 8px' }};
            border: 1px solid #155c38;
            text-align: {{ $isUrdu ? 'right' : 'center' }};
            font-size: {{ $isUrdu ? '14px' : '11px' }};
            font-weight: bold;
            line-height: {{ $isUrdu ? '2.2' : '1.5' }};
            color:white;
        }

        .main-table tbody tr:nth-child(even) td {
            background: #f4f9f6;
        }

        .main-table tbody tr:hover td {
            background: #e8f5ee;
        }

        .main-table tbody td {
            border: 1px solid #c9d5dc;
            padding: {{ $isUrdu ? '0px 7px' : '7px 8px' }};
            vertical-align: middle;
            line-height: {{ $isUrdu ? '2.2' : '1.5' }};
            font-size: {{ $isUrdu ? '14px' : '11px' }};
        }

        /* Column alignments */
        .col-sno    { width: {{ $isUrdu ? '5%' : '5%' }};  text-align: center; }
        .col-name   { width: {{ $isUrdu ? '10%' : '22%' }}; text-align: {{ $isUrdu ? 'right' : 'left' }}; }
        .col-fname  { width: {{ $isUrdu ? '10%' : '22%' }}; text-align: {{ $isUrdu ? 'right' : 'left' }}; }
        .col-amount { width: {{ $isUrdu ? '25%' : '13%' }}; text-align: center; }
        .col-date   { width: {{ $isUrdu ? '25%' : '13%' }}; text-align: center; }
        .col-remark { width: {{ $isUrdu ? '25%' : '25%' }}; text-align: {{ $isUrdu ? 'right' : 'left' }}; }

        /* Empty writing space rows */
        .writing-line {
            border-bottom: 1px dotted #aaa;
            height: {{ $isUrdu ? '30px' : '22px' }};
            display: block;
        }

        /* ── Footer ──────────────────────────────────────────────────── */
        .footer {
            margin-top: 18px;
            border-top: 1px solid #c9d5dc;
            padding-top: 10px;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
            font-size: {{ $isUrdu ? '12px' : '9.5px' }};
            color: #6b7280;
        }

        .sig-table td {
            text-align: center;
            padding: 4px;
            width: 33.33%;
        }

        .sig-line {
            border-top: 1px solid #374151;
            width: 120px;
            margin: {{ $isUrdu ? '14px' : '10px' }} auto 4px;
        }

        .page-note {
            text-align: center;
            font-size: {{ $isUrdu ? '11px' : '8.5px' }};
            color: #9ca3af;
            margin-top: 8px;
            line-height: {{ $isUrdu ? '2' : '1.4' }};
        }

        /* ── mPDF page repeating header ─────────────────────────────── */
        @page {
            header: page-header;
        }
    </style>
</head>
<body>
 
    {{-- ── Masjid Header ─────────────────────────────────────────── --}}
    <div class="header-wrap">
        <div class="mosque-name">
            {{ $isUrdu && $mosque->mosque_name_ur ? $mosque->mosque_name_ur : $mosque->mosque_name }}
        </div>

        @if ($mosque->village_name || $mosque->city)
            <div class="mosque-address">
                {{ $isUrdu && $mosque->address_ur
                    ? $mosque->address_ur
                    : implode(', ', array_filter([
                        $mosque->village_name,
                        $mosque->city,
                        $mosque->district ?? null,
                        $mosque->country  ?? null,
                    ]))
                }}
            </div>
        @endif

        @if ($mosque->mosque_contact)
            <div class="mosque-address">
                {{ $isUrdu ? 'رابطہ:' : 'Contact:' }}
                {{ $mosque->mosque_contact }}
            </div>
        @endif

        @if ($mosque->scholar_name)
            <div class="scholar-name">
                {{ $isUrdu ? 'امام / خطیب:' : 'Imam / Scholar:' }}
                <strong>
                    {{ $isUrdu && $mosque->scholar_name_ur
                        ? $mosque->scholar_name_ur
                        : $mosque->scholar_name }}
                </strong>
            </div>
        @endif
    </div>

    {{-- ── Season Title Band ──────────────────────────────────────── --}}
    <div class="season-band">
        {{ $isUrdu ? 'موسم:' : 'Season:' }}
        {{ $isUrdu && $season->name_ur ? $season->name_ur : $season->name }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        {{ $isUrdu ? 'سال:' : 'Year:' }}
        {{ $season->start_date ? \Carbon\Carbon::parse($season->start_date)->format('Y') : now()->year }}
        @if ($season->start_date && $season->end_date)
            &nbsp;&nbsp;|&nbsp;&nbsp;
            {{ \Carbon\Carbon::parse($season->start_date)->format('d M Y') }}
            —
            {{ \Carbon\Carbon::parse($season->end_date)->format('d M Y') }}
        @endif
    </div>

    {{-- ── Summary Stats Row ──────────────────────────────────────── --}}
    <table class="summary-table">
        <tr>
            <td style="width:33.33%;">
                <span class="s-label">
                    {{ $isUrdu ? 'کل اراکین' : 'Total Members' }}
                </span>
                <span class="s-value">{{ $totalMembers }}</span>
            </td>
            <td style="width:33.33%;">
                <span class="s-label">
                    {{ $isUrdu ? 'فی رکن چندہ' : 'Amount Per Member' }}
                </span>
                <span class="s-value">
                    {{ formatCurrency($amountPerMember) }}
                </span>
            </td>
            <td style="width:33.33%;">
                <span class="s-label">
                    {{ $isUrdu ? 'کل رقم' : 'Total Amount' }}
                </span>
                <span class="s-value">
                    {{ formatCurrency($totalAmount) }}
                </span>
            </td>
        </tr>
    </table>

    {{-- ── Collection Table ───────────────────────────────────────── --}}
    <table class="main-table">
        <thead>
            <tr>
                @if ($isUrdu)
                
                    {{-- RTL column order --}}
                    <th class="col-remark" >{{ __('S.No') }}</th>
                    <th class="col-date">{{ __('Name') }}</th>
                    <th class="col-amount">{{ __('Father Name') }}</th>
                    <th class="col-fname">{{ __('Paid Amount') }}</th>
                    <th class="col-name">{{ __('Date') }}</th>
                    <th class=" col-sno">{{ __('Remarks') }}</th>
                @else
               
                    {{-- LTR column order --}}
                    <th class="col-sno">{{ __('S.No') }}</th>
                    <th class="col-name">{{ __('Name') }}</th>
                    <th class="col-fname">{{ __('Father Name') }}</th>
                    <th class="col-amount">{{ __('Paid Amount') }}</th>
                    <th class="col-date">{{ __('Date') }}</th>
                    <th class="col-remark">{{ __('Remarks') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($seasonMembers as $i => $sm)
                <tr>
                    @if ($isUrdu)
                        {{-- RTL: Remarks | Date | Amount | FatherName | Name | SNo --}}
                        <td class="col-sno" style="text-align:center; ">
                            {{ $i + 1 }}
                        </td>
                        <td class="col-name">
                            {{ $sm->member?->name ?? '—' }}
                        </td>
                        <td class="col-fname">
                            {{ $sm->member?->father_name ?? '—' }}
                        </td>
                        <td class="col-amount">
                            <span class="writing-line"></span>
                        </td>
                        <td class="col-date">
                            <span class="writing-line"></span>
                        </td>
                        <td class="col-remark">
                            <span class="writing-line"></span>
                        </td>
                   @else
                        {{-- LTR: SNo | Name | FatherName | Amount | Date | Remarks --}}
                        <td class="col-sno">{{ $i + 1 }}</td>
                        <td class="col-name">
                            {{ $sm->member?->name ?? '—' }}
                        </td>
                        <td class="col-fname">
                            {{ $sm->member?->father_name ?? '—' }}
                        </td>
                        <td class="col-amount">
                            <span class="writing-line"></span>
                        </td>
                        <td class="col-date">
                            <span class="writing-line"></span>
                        </td>
                        <td class="col-remark">
                            <span class="writing-line"></span>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="6"
                        style="text-align:center;padding:20px;color:#9ca3af;">
                        {{ $isUrdu ? 'اس موسم میں کوئی رکن نہیں ہے' : 'No members assigned to this season yet.' }}
                    </td>
                </tr>
            @endforelse

            {{-- Extra blank rows for any walk-ins / new additions --}}
            @for ($e = 0; $e < 5; $e++)
                <tr>
                    @if ($isUrdu)
                        <td class="col-sno" style="text-align:center;">{{ $totalMembers + $e + 1 }}</td>
                        <td class="col-name"><span class="writing-line"></span></td>
                        <td class="col-fname"><span class="writing-line"></span></td>
                        <td class="col-amount"><span class="writing-line"></span></td>
                        <td class="col-date"><span class="writing-line"></span></td>
                        <td class="col-remark"><span class="writing-line"></span></td>
                    @else
                        <td class="col-sno">{{ $totalMembers + $e + 1 }}</td>
                        <td class="col-name"><span class="writing-line"></span></td>
                        <td class="col-fname"><span class="writing-line"></span></td>
                        <td class="col-amount"><span class="writing-line"></span></td>
                        <td class="col-date"><span class="writing-line"></span></td>
                        <td class="col-remark"><span class="writing-line"></span></td>
                    @endif
                </tr>
            @endfor
        </tbody>

        {{-- Summary footer row --}}
        <tfoot>
            <tr style="background:#f0f7f4;">
                @if ($isUrdu)
                    <td class="col-remark"></td>
                    <td class="col-date" style="text-align:center;font-weight:bold;color:#1B6B45;">
                        {{ __('Total') }}
                    </td>
                    <td class="col-amount"
                        style="text-align:center;font-weight:bold;color:#1B6B45;">
                        {{ formatCurrency($totalAmount) }}
                    </td>
                    <td colspan="2" class="col-fname"
                        style="text-align:{{ $isUrdu ? 'right' : 'left' }};font-size:{{ $isUrdu ? '12px' : '9px' }};color:#6b7280;">
                        {{ $isUrdu
                            ? 'کل اراکین: ' . $totalMembers . ' | فی رکن چندہ: ' . formatCurrency($amountPerMember)
                            : 'Total Members: ' . $totalMembers . ' | Per Member: ' . formatCurrency($amountPerMember) }}
                    </td>
                    <td class="col-sno"></td>
                @else
                    <td class="col-sno"></td>
                    <td colspan="2"
                        style="text-align:left;font-size:9px;color:#6b7280;padding-{{ $isUrdu ? 'right' : 'left' }}:8px;">
                        {{ 'Total Members: ' . $totalMembers . ' | Per Member: ' . formatCurrency($amountPerMember) }}
                    </td>
                    <td class="col-amount"
                        style="text-align:center;font-weight:bold;color:#1B6B45;">
                        {{ formatCurrency($totalAmount) }}
                    </td>
                    <td class="col-date"
                        style="text-align:center;font-weight:bold;color:#1B6B45;">
                        {{ __('Total') }}
                    </td>
                    <td class="col-remark"></td>
                @endif
            </tr>
        </tfoot>
    </table>

    {{-- ── Footer / Signature Block ───────────────────────────────── --}}
    <div class="footer">
        <table class="sig-table">
            <tr>
                @if ($isUrdu)
                    <td>
                        <div class="sig-line"></div>
                        {{ __('Verified By') }}
                    </td>
                    <td>
                        <div class="sig-line"></div>
                        {{ __('Collector Signature') }}
                    </td>
                    <td>
                        @if ($mosque->scholar_name)
                            <div class="sig-line"></div>
                            {{ $mosque->scholar_name_ur ?? $mosque->scholar_name }}
                            <br>
                            <span style="font-size:{{ $isUrdu ? '10px' : '8px' }};color:#9ca3af;">
                                {{ __('Imam / Scholar') }}
                            </span>
                        @endif
                    </td>
                @else
                    <td>
                        @if ($mosque->scholar_name)
                            <div class="sig-line"></div>
                            {{ $mosque->scholar_name }}
                            <br>
                            <span style="font-size:8px;color:#9ca3af;">
                                {{ __('Imam / Scholar') }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="sig-line"></div>
                        {{ __('Collector Signature') }}
                    </td>
                    <td>
                        <div class="sig-line"></div>
                        {{ __('Verified By') }}
                    </td>
                @endif
            </tr>
        </table>

        <!-- <div class="page-note">
            {{ $isUrdu
                ? $mosque->mosque_name_ur ?? $mosque->mosque_name
                : $mosque->mosque_name }}
            &mdash;
            {{ $isUrdu
                ? ($season->name_ur ?? $season->name) . ' — چندہ فہرست'
                : $season->name . ' — Collection List' }}
            &mdash;
            {{ $isUrdu ? 'تاریخ اجراء:' : 'Generated:' }}
            {{ now()->format('d M Y') }}
        </div> -->
    </div>

</body>
</html>