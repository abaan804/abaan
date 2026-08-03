<?php

namespace Modules\Masjid\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeason;
use Modules\Masjid\Models\MasjidSeasonMember;

class SeasonReportExport implements WithMultipleSheets
{
    public function __construct(
        protected MasjidMosque $mosque,
        protected MasjidSeason $season,
        protected array $summary = []
    ) {
    }

    public function sheets(): array
    {
        return [
            new SeasonMembersSheet($this->mosque, $this->season),
            new SeasonPaymentsSheet($this->mosque, $this->season),
        ];
    }
}

// ─── Sheet 1: Member breakdown ───────────────────────────────────────────────
class SeasonMembersSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(
        protected MasjidMosque $mosque,
        protected MasjidSeason $season
    ) {
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return MasjidSeasonMember::where('season_id', $this->season->id)
            ->with('member')
            ->orderBy('status')
            ->get();
    }

    public function headings(): array
    {
        return [
            __('Member'),
            __('Mobile'),
            __('Father Name'),
            __('Amount Due'),
            __('Amount Paid'),
            __('Balance'),
            __('Status'),
        ];
    }

    public function map($sm): array
    {
        return [
            $sm->member?->name ?? '',
            $sm->member?->mobile ?? '',
            $sm->member?->father_name ?? '',
            $sm->amount_due,
            $sm->amount_paid,
            $sm->balance(),
            ucfirst($sm->status),
        ];
    }

    public function title(): string
    {
        return __('Member Breakdown');
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

// ─── Sheet 2: Payments for this season ───────────────────────────────────────
class SeasonPaymentsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function __construct(
        protected MasjidMosque $mosque,
        protected MasjidSeason $season
    ) {
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->season->payments()
            ->with(['member', 'receivedBy'])
            ->orderByDesc('payment_date')
            ->get();
    }

    public function headings(): array
    {
        return [
            __('Receipt No'),
            __('Member'),
            __('Payment Date'),
            __('Method'),
            __('Reference No'),
            __('Amount'),
            __('Received By'),
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->receipt_no ?? '',
            $payment->member?->name ?? '',
            $payment->payment_date?->format('Y-m-d') ?? '',
            ucfirst($payment->payment_method),
            $payment->reference_no ?? '',
            $payment->amount_paid,
            $payment->receivedBy?->name ?? '',
        ];
    }

    public function title(): string
    {
        return __('Payments');
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}