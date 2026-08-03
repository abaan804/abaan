<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidPayment;
use Modules\Masjid\Models\MasjidPaymentAttachment;
use Modules\Masjid\Models\MasjidSeason;
use Modules\Masjid\Models\MasjidSeasonMember;
use Modules\Masjid\Repositories\MasjidMemberRepository;
use Modules\Masjid\Repositories\MasjidPaymentRepository;
use Modules\Masjid\Requests\StorePaymentRequest;
use Modules\Masjid\Requests\UpdatePaymentRequest;
use Modules\Masjid\Services\MasjidNotificationService;
use Modules\Masjid\Services\MasjidPaymentService;
use Modules\Masjid\Services\MasjidSettingService;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Masjid\Exports\PaymentsExport;

class PaymentController extends Controller
{
    public function __construct(
        protected MasjidPaymentService $paymentService,
        protected MasjidPaymentRepository $paymentRepo,
        protected MasjidMemberRepository $memberRepo,
        protected MasjidNotificationService $notificationService,
        protected MasjidSettingService $settingService
    ) {
    }

    public function index(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.manage-payments')
            && $mosque->company_id === $request->user()->company_id, 403);

        $members = $this->memberRepo->activeMembers($mosque);
        $seasons = $mosque->seasons()->where('status', 'active')->get();

        return view('masjid::payments.index', compact('mosque', 'members', 'seasons'));
    }

    public function table(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.manage-payments'), 403);

        $payments = $this->paymentRepo->paginate($mosque, $request->only([
            'member_id', 'season_id', 'payment_method', 'date_from', 'date_to', 'search',
        ]));

        return view('masjid::payments._table', compact('payments', 'mosque'));
    }

    public function json(Request $request, MasjidMosque $mosque, MasjidPayment $payment): JsonResponse
    {
        abort_unless($payment->mosque_id === $mosque->id, 403);
        $payment->load('attachments');

        return response()->json(['data' => $payment]);
    }

    public function store(StorePaymentRequest $request, MasjidMosque $mosque): JsonResponse
    {
        $data = $request->safe()->except('attachments');
        $payment = $this->paymentService->create($mosque, $data);

        if ($request->hasFile('attachments')) {
            $this->paymentService->storeAttachments($payment, $request->file('attachments'));
        }

        // Dispatch payment-received notification (queued — does not block the HTTP response)
        $payment->loadMissing(['member', 'season', 'mosque']);
        $setting = $this->settingService->forMosque($mosque);

        if ($setting->notification_email || $setting->notification_sms || $setting->notification_whatsapp) {
            $this->notificationService->sendPaymentReceived($payment);
        }

        return response()->json([
            'success' => true,
            'message' => __('Payment recorded. Receipt: :receipt', ['receipt' => $payment->receipt_no]),
            'data' => $payment,
        ]);
    }

    public function update(UpdatePaymentRequest $request, MasjidMosque $mosque, MasjidPayment $payment): JsonResponse
    {
        abort_unless($payment->mosque_id === $mosque->id, 403);

        $payment = $this->paymentService->update($payment, $request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Payment updated.'),
            'data' => $payment,
        ]);
    }

    public function destroy(Request $request, MasjidMosque $mosque, MasjidPayment $payment): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-payments')
            && $payment->mosque_id === $mosque->id, 403);

        $this->paymentService->delete($payment);

        return response()->json(['success' => true, 'message' => __('Payment deleted.')]);
    }

    public function deleteAttachment(Request $request, MasjidMosque $mosque, MasjidPaymentAttachment $attachment): JsonResponse
    {
        abort_unless($request->user()->can('masjid.manage-payments'), 403);

        \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(['success' => true, 'message' => __('Attachment removed.')]);
    }

    /**
     * AJAX: load season_member record when member+season selection changes in Add Payment form.
     */
    public function seasonMemberInfo(Request $request, MasjidMosque $mosque): JsonResponse
    {
        $seasonMember = MasjidSeasonMember::where('mosque_id', $mosque->id)
            ->where('member_id', $request->member_id)
            ->where('season_id', $request->season_id)
            ->first();

        if (! $seasonMember) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'season_member_id' => $seasonMember->id,
            'amount_due' => $seasonMember->amount_due,
            'amount_paid' => $seasonMember->amount_paid,
            'balance' => $seasonMember->balance(),
            'status' => $seasonMember->status,
        ]);
    }

    public function export(Request $request, MasjidMosque $mosque, string $format)
    {
        abort_unless($request->user()->can('masjid.manage-payments')
            && $mosque->company_id === $request->user()->company_id, 403);

        $filters = $request->only(['member_id', 'season_id', 'payment_method', 'date_from', 'date_to', 'search']);
        $export = new PaymentsExport($mosque, $filters);
        $filename = 'payments-' . \Illuminate\Support\Str::slug($mosque->mosque_name) . '-' . now()->format('Y-m-d');

        return match ($format) {
            'csv'  => Excel::download($export, "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => Excel::download($export, "{$filename}.xlsx"),
            default => abort(404),
        };
    }
}