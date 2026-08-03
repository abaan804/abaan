<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidMember;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Repositories\MasjidMemberRepository;
use Modules\Masjid\Requests\StoreMemberRequest;
use Modules\Masjid\Requests\UpdateMemberRequest;
use Modules\Masjid\Services\MasjidBalanceService;
use Modules\Masjid\Services\MasjidPdfService;
use Modules\Masjid\Services\MasjidReportService;
use Modules\Masjid\Services\MasjidSeasonService;
use Modules\Masjid\Services\MasjidSettingService;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Masjid\Exports\MembersExport;

class MemberController extends Controller
{
    public function __construct(
        protected MasjidMemberRepository $memberRepo,
        protected MasjidSeasonService $seasonService,
        protected MasjidBalanceService $balanceService,
        protected MasjidReportService $reportService,
        protected MasjidPdfService $pdfService,
        protected MasjidSettingService $settingService
    ) {
    }

    public function index(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.manage-members')
            && $mosque->company_id === $request->user()->company_id, 403);

        return view('masjid::members.index', compact('mosque'));
    }

    public function table(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.manage-members'), 403);

        $members = $this->memberRepo->paginate($mosque, $request->only(['search', 'status', 'sort', 'dir']));

        return view('masjid::members._table', compact('members', 'mosque'));
    }

    public function json(Request $request, MasjidMosque $mosque, MasjidMember $member): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-members')
            && $member->mosque_id === $mosque->id, 403);

        return response()->json(['data' => $member]);
    }

    public function store(StoreMemberRequest $request, MasjidMosque $mosque): JsonResponse
    {
        $data = $request->safe()->except('photo');
        $data['company_id'] = $mosque->company_id;
        $data['mosque_id'] = $mosque->id;
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('masjid/members', 'public');
        }

        $member = MasjidMember::create($data);

        // Auto-assign to open seasons
        // $assignedCount = $this->seasonService->assignMemberToOpenSeasons($mosque, $member);

        return response()->json([
            'success' => true,
            // 'message' => __('Member added successfully. Assigned to :count open season(s).', ['count' => $assignedCount]),
            'message' => __('Member added successfully.'),
            'data' => $member,
        ]);
    }

    public function update(UpdateMemberRequest $request, MasjidMosque $mosque, MasjidMember $member): JsonResponse
    {
        abort_unless($member->mosque_id === $mosque->id, 403);

        $data = $request->safe()->except('photo');
        $data['updated_by'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('masjid/members', 'public');
        }

        $member->update($data);

        return response()->json([
            'success' => true,
            'message' => __('Member updated successfully.'),
            'data' => $member,
        ]);
    }

    public function destroy(Request $request, MasjidMosque $mosque, MasjidMember $member): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-members')
            && $member->mosque_id === $mosque->id, 403);

        if ($member->payments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot delete a member with payment history. Set status to Inactive instead.'),
            ], 422);
        }

        $member->delete();

        return response()->json(['success' => true, 'message' => __('Member deleted.')]);
    }

    public function statement(Request $request, MasjidMosque $mosque, MasjidMember $member): View
    {
        abort_unless($request->user()->can('masjid.manage-members')
            && $member->mosque_id === $mosque->id, 403);

        $statement = $this->reportService->memberStatement($mosque, $member->id);

        return view('masjid::members.statement', compact('mosque', 'member', 'statement'));
    }

    public function statementPdf(Request $request, MasjidMosque $mosque, MasjidMember $member)
    {
        abort_unless($request->user()->can('masjid.manage-members')
            && $member->mosque_id === $mosque->id, 403);

        $statement = $this->reportService->memberStatement($mosque, $member->id);

        return $this->pdfService->download('masjid::reports.pdf.member-statement', [
            'letterhead' => $this->pdfService->mosqueLetterhead($mosque),
            'mosque' => $mosque,
            'member' => $member,
            'statement' => $statement,
        ], 'statement-' . \Illuminate\Support\Str::slug($member->name) . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function export(Request $request, MasjidMosque $mosque, string $format)
    {
        abort_unless($request->user()->can('masjid.manage-members')
            && $mosque->company_id === $request->user()->company_id, 403);

        $filters = $request->only(['search', 'status']);
        $export = new MembersExport($mosque, $filters);
        $filename = 'members-' . \Illuminate\Support\Str::slug($mosque->mosque_name) . '-' . now()->format('Y-m-d');

        return match ($format) {
            'csv'  => Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }
}