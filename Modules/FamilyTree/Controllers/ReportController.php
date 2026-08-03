<?php

namespace Modules\FamilyTree\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Repositories\FtMemberRepository;
use Modules\FamilyTree\Services\FamilyTreeExportService;
use Modules\FamilyTree\Services\FamilyTreePdfService;
use Modules\FamilyTree\Services\FamilyTreeReportService;
use Modules\FamilyTree\Exports\EventsExport;

class ReportController extends Controller
{
    public function __construct(
        protected FamilyTreeReportService $reportService,
        protected FamilyTreePdfService $pdfService,
        protected FamilyTreeExportService $exportService,
        protected FtMemberRepository $memberRepo
    ) {
    }

    public function index(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports')
            && $request->user()->company_id === $family->company_id, 403);

        return view('familytree::reports.index', compact('family'));
    }

    public function members(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        return view('familytree::reports.members', compact('family'));
    }

    public function membersTable(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        $filters = $request->only(['gender', 'life_status', 'marital_status', 'occupation', 'blood_group']);
        $members = $this->reportService->membersReport($family, $filters);
        return view('familytree::reports._members_table', compact('members', 'family'));
    }

    public function membersPdf(Request $request, FtFamily $family)
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        $filters = $request->only(['gender', 'life_status', 'marital_status']);
        $members = $this->reportService->membersReport($family, $filters);
        return $this->pdfService->download('familytree::reports.pdf.members', [
            'letterhead' => $this->pdfService->familyLetterhead($family),
            'members' => $members,
            'family' => $family,
        ], 'members-' . \Illuminate\Support\Str::slug($family->name) . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function membersExport(Request $request, FtFamily $family, string $format)
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        $filters = $request->only(['gender', 'life_status', 'marital_status']);
        return $this->exportService->exportMembers($family, $filters, $format);
    }

    public function births(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        return view('familytree::reports.births', compact('family'));
    }

    public function birthsTable(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        $members = $this->reportService->birthReport($family, $request->only(['year', 'date_from', 'date_to']));
        return view('familytree::reports._births_table', compact('members', 'family'));
    }

    public function deaths(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        return view('familytree::reports.deaths', compact('family'));
    }

    public function deathsTable(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        $members = $this->reportService->deathReport($family, $request->only(['year']));
        return view('familytree::reports._deaths_table', compact('members', 'family'));
    }

    public function marriages(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        return view('familytree::reports.marriages', compact('family'));
    }

    public function marriagesTable(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        $marriages = $this->reportService->marriageReport($family, $request->only(['status', 'year']));
        return view('familytree::reports._marriages_table', compact('marriages', 'family'));
    }

    public function events(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        return view('familytree::reports.events', compact('family'));
    }

    public function eventsTable(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        $events = $this->reportService->eventsReport($family, $request->only(['event_type', 'date_from', 'date_to']));
        return view('familytree::reports._events_table', compact('events', 'family'));
    }

    public function missing(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        return view('familytree::reports.missing', compact('family'));
    }

    public function missingTable(Request $request, FtFamily $family): View
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);
        $rows = $this->reportService->missingInfoReport($family);
        return view('familytree::reports._missing_table', compact('rows', 'family'));
    }

    public function eventsExport(Request $request, FtFamily $family, string $format)
    {
        abort_unless($request->user()->can('familytree.view-reports')
            && $family->company_id === $request->user()->company_id, 403);

        $filters = $request->only(['event_type', 'date_from', 'date_to']);
        $export  = new EventsExport($family, $filters);
        $filename = 'events-' . \Illuminate\Support\Str::slug($family->name) . '-' . now()->format('Y-m-d');

        return match ($format) {
            'csv'  => \Maatwebsite\Excel\Facades\Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => \Maatwebsite\Excel\Facades\Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }

    public function birthsExport(Request $request, FtFamily $family, string $format)
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);

        $data   = $this->reportService->birthReport($family, $request->only(['year', 'date_from', 'date_to']));
        $export = new \Modules\FamilyTree\Exports\FamilyReportExport('births', $data);
        $filename = 'births-' . \Illuminate\Support\Str::slug($family->name) . '-' . now()->format('Y-m-d');

        return match ($format) {
            'csv'  => \Maatwebsite\Excel\Facades\Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => \Maatwebsite\Excel\Facades\Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }

    public function deathsExport(Request $request, FtFamily $family, string $format)
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);

        $data   = $this->reportService->deathReport($family, $request->only(['year']));
        $export = new \Modules\FamilyTree\Exports\FamilyReportExport('deaths', $data);
        $filename = 'deaths-' . \Illuminate\Support\Str::slug($family->name) . '-' . now()->format('Y-m-d');

        return match ($format) {
            'csv'  => \Maatwebsite\Excel\Facades\Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => \Maatwebsite\Excel\Facades\Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }

    public function marriagesExport(Request $request, FtFamily $family, string $format)
    {
        abort_unless($request->user()->can('familytree.view-reports'), 403);

        $data   = $this->reportService->marriageReport($family, $request->only(['status', 'year']));
        $export = new \Modules\FamilyTree\Exports\FamilyReportExport('marriages', $data);
        $filename = 'marriages-' . \Illuminate\Support\Str::slug($family->name) . '-' . now()->format('Y-m-d');

        return match ($format) {
            'csv'  => \Maatwebsite\Excel\Facades\Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => \Maatwebsite\Excel\Facades\Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }
}