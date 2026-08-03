<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeason;
use Modules\Masjid\Repositories\MasjidMemberRepository;
use Modules\Masjid\Services\MasjidBalanceService;
use Modules\Masjid\Services\MasjidPdfService;
use Modules\Masjid\Services\MasjidReportService;
use Modules\Masjid\Services\MasjidSettingService;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Masjid\Exports\PaymentsExport;
use Modules\Masjid\Exports\SeasonReportExport;

class ReportController extends Controller
{
    public function __construct(
        protected MasjidReportService $reportService,
        protected MasjidBalanceService $balanceService,
        protected MasjidPdfService $pdfService,
        protected MasjidSettingService $settingService,
        protected MasjidMemberRepository $memberRepo
    ) {
    }

    public function index(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.view-reports')
            && $mosque->company_id === $request->user()->company_id, 403);

        $seasons = $mosque->seasons()->orderByDesc('start_date')->get();

        return view('masjid::reports.index', compact('mosque', 'seasons'));
    }

    public function collection(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.view-reports'), 403);

        $members = $this->memberRepo->activeMembers($mosque);
        $seasons = $mosque->seasons()->orderByDesc('start_date')->get();

        return view('masjid::reports.collection', compact('mosque', 'members', 'seasons'));
    }

    public function collectionTable(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.view-reports'), 403);

        $filters = $request->only(['date_from', 'date_to', 'member_id', 'season_id', 'payment_method']);
        $payments = $this->reportService->filteredPayments($mosque, $filters)
            ->orderByDesc('payment_date')->get();

        $total = $payments->sum('amount_paid');

        return view('masjid::reports._collection_table', compact('payments', 'total'));
    }

    public function collectionPdf(Request $request, MasjidMosque $mosque)
    {
        abort_unless($request->user()->can('masjid.view-reports'), 403);

        $filters = $request->only(['date_from', 'date_to', 'member_id', 'season_id', 'payment_method']);
        $payments = $this->reportService->filteredPayments($mosque, $filters)
            ->orderByDesc('payment_date')->get();

        $total = $payments->sum('amount_paid');

        return $this->pdfService->download('masjid::reports.pdf.collection', [
            'letterhead' => $this->pdfService->mosqueLetterhead($mosque),
            'payments' => $payments,
            'total' => $total,
            'filters' => $filters,
            'mosque' => $mosque,
        ], 'collection-' . now()->format('Y-m-d') . '.pdf');
    }

    public function outstanding(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.view-reports'), 403);

        $seasons = $mosque->seasons()->orderByDesc('start_date')->get();

        return view('masjid::reports.outstanding', compact('mosque', 'seasons'));
    }

    public function outstandingTable(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.view-reports'), 403);

        $seasonId = $request->get('season_id');
        $status = $request->get('status', 'pending');

        $query = \Modules\Masjid\Models\MasjidSeasonMember::where('mosque_id', $mosque->id)
            ->with(['member', 'season']);

        if ($seasonId) $query->where('season_id', $seasonId);
        if ($status !== 'all') $query->where('status', $status);

        $assignments = $query->orderBy('status')->get();

        return view('masjid::reports._outstanding_table', compact('assignments', 'mosque'));
    }

    public function outstandingPdf(Request $request, MasjidMosque $mosque)
    {
        abort_unless($request->user()->can('masjid.view-reports'), 403);

        $assignments = \Modules\Masjid\Models\MasjidSeasonMember::where('mosque_id', $mosque->id)
            ->whereIn('status', ['pending', 'partial'])
            ->with(['member', 'season'])
            ->orderBy('status')
            ->get();

        return $this->pdfService->download('masjid::reports.pdf.outstanding', [
            'letterhead' => $this->pdfService->mosqueLetterhead($mosque),
            'assignments' => $assignments,
            'mosque' => $mosque,
        ], 'outstanding-' . now()->format('Y-m-d') . '.pdf');
    }

    public function season(Request $request, MasjidMosque $mosque, MasjidSeason $season): View
    {
        abort_unless($request->user()->can('masjid.view-reports')
            && $season->mosque_id === $mosque->id, 403);

        $summary = $this->reportService->seasonSummary($mosque, $season->id);

        $assignments = \Modules\Masjid\Models\MasjidSeasonMember::where('season_id', $season->id)
            ->with(['member', 'payments'])
            ->orderBy('status')
            ->get();

        return view('masjid::reports.season', compact('mosque', 'season', 'summary', 'assignments'));
    }

    public function seasonPdf(Request $request, MasjidMosque $mosque, MasjidSeason $season)
    {
        abort_unless($request->user()->can('masjid.view-reports')
            && $season->mosque_id === $mosque->id, 403);

        $summary = $this->reportService->seasonSummary($mosque, $season->id);

        $assignments = \Modules\Masjid\Models\MasjidSeasonMember::where('season_id', $season->id)
            ->with(['member', 'payments'])
            ->orderBy('status')
            ->get();

        return $this->pdfService->download('masjid::reports.pdf.season', [
            'letterhead' => $this->pdfService->mosqueLetterhead($mosque),
            'mosque' => $mosque,
            'season' => $season,
            'summary' => $summary,
            'assignments' => $assignments,
        ], 'season-' . \Illuminate\Support\Str::slug($season->name) . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function collectionExport(Request $request, MasjidMosque $mosque, string $format)
    {
        abort_unless($request->user()->can('masjid.view-reports')
            && $mosque->company_id === $request->user()->company_id, 403);

        $filters = $request->only(['date_from', 'date_to', 'member_id', 'season_id', 'payment_method']);
        $export = new PaymentsExport($mosque, $filters);
        $filename = 'collection-' . now()->format('Y-m-d');

        return match ($format) {
            'csv'  => Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }

    public function outstandingExport(Request $request, MasjidMosque $mosque, string $format)
    {
        abort_unless($request->user()->can('masjid.view-reports')
            && $mosque->company_id === $request->user()->company_id, 403);

        $seasonId = $request->get('season_id');
        $status = $request->get('status', 'pending');

        $query = \Modules\Masjid\Models\MasjidSeasonMember::where('mosque_id', $mosque->id)
            ->with(['member', 'season']);

        if ($seasonId) $query->where('season_id', $seasonId);
        if ($status !== 'all') $query->where('status', $status);

        $assignments = $query->orderBy('status')->get();

        // Inline anonymous export — too simple to warrant a full Export class
        $export = new class($assignments) implements
            \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithMapping,
            \Maatwebsite\Excel\Concerns\WithStyles
        {
            public function __construct(protected \Illuminate\Support\Collection $rows) {}

            public function collection(): \Illuminate\Support\Collection { return $this->rows; }

            public function headings(): array
            {
                return [__('Member'), __('Mobile'), __('Season'), __('Due'), __('Paid'), __('Balance'), __('Status')];
            }

            public function map($sm): array
            {
                return [
                    $sm->member?->name ?? '',
                    $sm->member?->mobile ?? '',
                    $sm->season?->name ?? '',
                    $sm->amount_due,
                    $sm->amount_paid,
                    $sm->balance(),
                    ucfirst($sm->status),
                ];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
            {
                return [1 => ['font' => ['bold' => true]]];
            }
        };

        $filename = 'outstanding-' . now()->format('Y-m-d');

        return match ($format) {
            'csv'  => Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }

    public function seasonExport(Request $request, MasjidMosque $mosque, MasjidSeason $season, string $format)
    {
        abort_unless($request->user()->can('masjid.view-reports')
            && $season->mosque_id === $mosque->id, 403);

        $summary = $this->reportService->seasonSummary($mosque, $season->id);
        $export = new SeasonReportExport($mosque, $season, $summary);
        $filename = 'season-' . \Illuminate\Support\Str::slug($season->name) . '-' . now()->format('Y-m-d');

        return match ($format) {
            'xlsx' => Excel::download($export, "{$filename}.xlsx"),
            // Season report has 2 sheets — CSV doesn't support multi-sheet; offer xlsx only
            default => abort(404),
        };
    }
}