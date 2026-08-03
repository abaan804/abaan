<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Masjid\Exports\DonationsExport;
use Modules\Masjid\Models\MasjidDonation;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeason;
use Modules\Masjid\Services\MasjidSettingService;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

class DonationReportController extends Controller
{
    public function __construct(protected MasjidSettingService $settingService)
    {
    }

    // ── Report Index Page ─────────────────────────────────────────────────────

    public function index(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.view-reports')
            && $mosque->company_id === $request->user()->company_id, 403);

        $seasons = MasjidSeason::where('mosque_id', $mosque->id)
            ->orderByDesc('start_date')->get();

        // Summary stats
        $totalNamed     = MasjidDonation::where('mosque_id', $mosque->id)->where('type', 'named')->sum('amount');
        $totalAnonymous = MasjidDonation::where('mosque_id', $mosque->id)->where('type', 'anonymous')->sum('amount');
        $totalAll       = $totalNamed + $totalAnonymous;
        $countNamed     = MasjidDonation::where('mosque_id', $mosque->id)->where('type', 'named')->count();
        $countAnonymous = MasjidDonation::where('mosque_id', $mosque->id)->where('type', 'anonymous')->count();

        return view('masjid::donations.report', compact(
            'mosque', 'seasons',
            'totalNamed', 'totalAnonymous', 'totalAll',
            'countNamed', 'countAnonymous'
        ));
    }

    // ── AJAX Table ────────────────────────────────────────────────────────────

    public function table(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($mosque->company_id === $request->user()->company_id, 403);

        $donations = $this->buildQuery($mosque, $request)
            ->orderByDesc('donation_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $total = $this->buildQuery($mosque, $request)->sum('amount');

        return view('masjid::donations._report_table', compact('donations', 'mosque', 'total'));
    }

    // ── Excel / CSV Export ────────────────────────────────────────────────────

    public function export(Request $request, MasjidMosque $mosque, string $format): Response
    {
        abort_unless($request->user()->can('masjid.view-reports')
            && $mosque->company_id === $request->user()->company_id, 403);

        $donations = $this->buildQuery($mosque, $request)
            ->orderByDesc('donation_date')
            ->get();

        $export   = new DonationsExport($donations);
        $filename = 'donations-' . \Illuminate\Support\Str::slug($mosque->mosque_name)
            . '-' . now()->format('Y-m-d');

        return match ($format) {
            'csv'  => Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }

    // ── Report PDF ────────────────────────────────────────────────────────────

    public function pdf(Request $request, MasjidMosque $mosque): Response
    {
        abort_unless($request->user()->can('masjid.view-reports')
            && $mosque->company_id === $request->user()->company_id, 403);

        $donations = $this->buildQuery($mosque, $request)
            ->orderByDesc('donation_date')
            ->get();

        $total    = $donations->sum('amount');
        $setting  = $this->settingService->forMosque($mosque);
        $filename = 'donations-' . \Illuminate\Support\Str::slug($mosque->mosque_name)
            . '-' . now()->format('Y-m-d') . '.pdf';

        $html = view('masjid::donations.report_pdf', compact(
            'mosque', 'donations', 'total', 'setting',
            'request'
        ))->render();

        return $this->streamPdf($html, $filename);
    }

    // ── Individual Donation Slip (Browser Print) ──────────────────────────────

    public function slip(Request $request, MasjidMosque $mosque, MasjidDonation $donation): View
    {
        abort_unless($mosque->company_id === $request->user()->company_id
            && $donation->mosque_id === $mosque->id, 403);

        abort_if($donation->type !== 'named', 422,
            __('Slips are only available for named donations.'));

        $donation->load('season', 'receivedBy');
        $setting = $this->settingService->forMosque($mosque);

        return view('masjid::donations.slip', compact('mosque', 'donation', 'setting'));
    }

    // ── Individual Donation Slip PDF Download ─────────────────────────────────

    public function slipPdf(Request $request, MasjidMosque $mosque, MasjidDonation $donation): Response
    {
        abort_unless($mosque->company_id === $request->user()->company_id
            && $donation->mosque_id === $mosque->id, 403);

        abort_if($donation->type !== 'named', 422,
            __('Slips are only available for named donations.'));

        $donation->load('season', 'receivedBy');
        $setting = $this->settingService->forMosque($mosque);

        $html     = view('masjid::donations.slip_pdf', compact('mosque', 'donation', 'setting'))->render();
        $filename = 'donation-slip-' . ($donation->receipt_no ?? $donation->id) . '.pdf';

        return $this->streamPdf($html, $filename, 'A5');
    }

    // ── Shared Helpers ────────────────────────────────────────────────────────

    protected function buildQuery(MasjidMosque $mosque, Request $request)
    {
        $query = MasjidDonation::where('mosque_id', $mosque->id)
            ->with(['season', 'receivedBy']);

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($seasonId = $request->get('season_id')) {
            $query->where('season_id', $seasonId);
        }
        if ($from = $request->get('date_from')) {
            $query->whereDate('donation_date', '>=', $from);
        }
        if ($to = $request->get('date_to')) {
            $query->whereDate('donation_date', '<=', $to);
        }
        if ($search = $request->get('search')) {
            $query->where(fn ($q) => $q
                ->where('donor_name', 'like', "%{$search}%")
                ->orWhere('donor_mobile', 'like', "%{$search}%")
                ->orWhere('purpose', 'like', "%{$search}%")
                ->orWhere('receipt_no', 'like', "%{$search}%")
            );
        }

        return $query;
    }

    protected function streamPdf(string $html, string $filename, string $format = 'A4'): Response
    {
        $defaultConfig     = (new ConfigVariables())->getDefaults();
        $defaultFontConfig = (new FontVariables())->getDefaults();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => $format,
            'margin_top'    => 10,
            'margin_bottom' => 12,
            'margin_left'   => 12,
            'margin_right'  => 12,
            'fontDir'       => array_merge($defaultConfig['fontDir'], config('mpdf.fontDir', [])),
            'fontdata'      => array_merge($defaultFontConfig['fontdata'], config('mpdf.fontdata', [])),
            'default_font'  => 'dejavusans',
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output($filename, Destination::STRING_RETURN), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}