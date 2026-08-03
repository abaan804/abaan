<?php

namespace Modules\VideoDownloader\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Modules\VideoDownloader\Exports\DownloadHistoryExport;
use Modules\VideoDownloader\Services\DownloadReportService;
use Modules\VideoDownloader\Services\VideoDownloadPdfService;

class ReportController extends Controller
{
    public function __construct(
        protected DownloadReportService  $reportService,
        protected VideoDownloadPdfService $pdfService,
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('videodownloader.view-reports'), 403);

        $companyId = $request->user()->company_id;
        $stats     = $this->reportService->usageStats($companyId);
        $daily     = $this->reportService->dailyStats($companyId, 30);

        return view('videodownloader::reports.index', compact('stats', 'daily'));
    }

    public function table(Request $request): View
    {
        abort_unless($request->user()->can('videodownloader.view-reports'), 403);

        $filters   = $request->only([
            'status', 'platform', 'format_ext', 'date_from', 'date_to', 'search',
        ]);
        $downloads = $this->reportService->history(
            $request->user()->company_id,
            $filters
        );

        return view('videodownloader::reports._table', compact('downloads'));
    }

    public function export(Request $request, string $format)
    {
        abort_unless($request->user()->can('videodownloader.view-reports'), 403);

        $filters   = $request->only([
            'status', 'platform', 'format_ext', 'date_from', 'date_to', 'search',
        ]);
        $downloads = $this->reportService->history(
            $request->user()->company_id,
            $filters
        );

        $export   = new DownloadHistoryExport($downloads);
        $filename = 'download-history-' . now()->format('Y-m-d');

        return match ($format) {
            'csv'  => Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }

    public function pdf(Request $request)
    {
        abort_unless($request->user()->can('videodownloader.view-reports'), 403);

        $filters   = $request->only([
            'status', 'platform', 'format_ext', 'date_from', 'date_to',
        ]);
        $downloads = $this->reportService->history(
            $request->user()->company_id,
            $filters
        );

        $filename = 'download-history-' . now()->format('Y-m-d') . '.pdf';

        return $this->pdfService->download(
            'videodownloader::reports.pdf.history',
            ['downloads' => $downloads, 'filters' => $filters],
            $filename
        );
    }
}