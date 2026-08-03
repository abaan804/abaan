<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidDonation;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeason;
use Modules\Masjid\Services\MasjidSettingService;

class DonationController extends Controller
{
    public function __construct(protected MasjidSettingService $settingService)
    {
    }

    public function index(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.manage-payments')
            && $mosque->company_id === $request->user()->company_id, 403);

        $seasons = MasjidSeason::where('mosque_id', $mosque->id)
            ->orderByDesc('start_date')->get();

        return view('masjid::donations.index', compact('mosque', 'seasons'));
    }

    public function table(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($mosque->company_id === $request->user()->company_id, 403);

        $query = MasjidDonation::where('mosque_id', $mosque->id)
            ->with(['season', 'receivedBy']);

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($seasonId = $request->get('season_id')) {
            $query->where('season_id', $seasonId);
        }
        if ($from = $request->get('date_from')) {
            $query->dateFrom($from);
        }
        if ($to = $request->get('date_to')) {
            $query->dateTo($to);
        }
        if ($search = $request->get('search')) {
            $query->where(fn ($q) => $q
                ->where('donor_name', 'like', "%{$search}%")
                ->orWhere('donor_mobile', 'like', "%{$search}%")
                ->orWhere('purpose', 'like', "%{$search}%")
                ->orWhere('day_description', 'like', "%{$search}%")
            );
        }

        $donations = $query->orderByDesc('donation_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $total = MasjidDonation::where('mosque_id', $mosque->id)
            ->when($request->get('type'), fn ($q, $v) => $q->where('type', $v))
            ->when($request->get('season_id'), fn ($q, $v) => $q->where('season_id', $v))
            ->when($request->get('date_from'), fn ($q, $v) => $q->dateFrom($v))
            ->when($request->get('date_to'), fn ($q, $v) => $q->dateTo($v))
            ->sum('amount');

        return view('masjid::donations._table', compact('donations', 'mosque', 'total'));
    }

    public function json(Request $request, MasjidMosque $mosque, MasjidDonation $donation): JsonResponse
    {
        abort_unless($donation->mosque_id === $mosque->id, 403);
        return response()->json(['data' => $donation]);
    }

    public function store(Request $request, MasjidMosque $mosque): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-payments')
            && $mosque->company_id === $request->user()->company_id, 403);

        $data = $request->validate([
            'type'             => 'required|in:named,anonymous',
            'donor_name'       => 'nullable|required_if:type,named|string|max:255',
            'donor_mobile'     => 'nullable|string|max:30',
            'donor_address'    => 'nullable|string|max:500',
            'amount'           => 'required|numeric|min:1',
            'donation_date'    => 'required|date',
            'day_description'  => 'nullable|string|max:100',
            'purpose'          => 'nullable|string|max:255',
            'season_id'        => 'nullable|exists:masjid_seasons,id',
            'receipt_no'       => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
            'received_by'      => 'nullable|exists:users,id',
        ]);

        $data['company_id'] = $mosque->company_id;
        $data['mosque_id']  = $mosque->id;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $donation = MasjidDonation::create($data);

        return response()->json([
            'success' => true,
            'message' => __('Donation recorded successfully.'),
            'data'    => $donation,
        ]);
    }

    public function update(Request $request, MasjidMosque $mosque, MasjidDonation $donation): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-payments')
            && $donation->mosque_id === $mosque->id, 403);

        $data = $request->validate([
            'type'             => 'required|in:named,anonymous',
            'donor_name'       => 'nullable|required_if:type,named|string|max:255',
            'donor_mobile'     => 'nullable|string|max:30',
            'donor_address'    => 'nullable|string|max:500',
            'amount'           => 'required|numeric|min:1',
            'donation_date'    => 'required|date',
            'day_description'  => 'nullable|string|max:100',
            'purpose'          => 'nullable|string|max:255',
            'season_id'        => 'nullable|exists:masjid_seasons,id',
            'receipt_no'       => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
            'received_by'      => 'nullable|exists:users,id',
        ]);

        $data['updated_by'] = auth()->id();
        $donation->update($data);

        return response()->json([
            'success' => true,
            'message' => __('Donation updated.'),
            'data'    => $donation,
        ]);
    }

    public function destroy(Request $request, MasjidMosque $mosque, MasjidDonation $donation): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-payments')
            && $donation->mosque_id === $mosque->id, 403);

        $donation->delete();

        return response()->json(['success' => true, 'message' => __('Donation deleted.')]);
    }
}