<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RenewalRequest;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RenewalRequestController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptionService)
    {
    }

    // ── List all requests ─────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        $requests = RenewalRequest::with(['company', 'package', 'submittedBy', 'reviewedBy'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $pendingCount = RenewalRequest::pending()->count();

        return view('admin.renewal-requests.index', compact(
            'requests', 'status', 'pendingCount'
        ));
    }

    // ── Detail view ───────────────────────────────────────────────────────────

    public function show(RenewalRequest $renewalRequest): View
    {
        $renewalRequest->load([
            'company.subscriptions' => fn ($q) => $q->latest()->take(3),
            'package.moduleDefinitions',
            'submittedBy',
            'reviewedBy',
        ]);

        return view('admin.renewal-requests.show', compact('renewalRequest'));
    }

    // ── Serve screenshot securely (private storage) ───────────────────────────

    public function screenshot(RenewalRequest $renewalRequest): StreamedResponse
    {
        abort_unless(
            Storage::disk('local')->exists($renewalRequest->payment_screenshot),
            404
        );

        $mime = Storage::disk('local')->mimeType($renewalRequest->payment_screenshot);

        return response()->stream(function () use ($renewalRequest) {
            $stream = Storage::disk('local')->readStream($renewalRequest->payment_screenshot);
            while (! feof($stream)) {
                echo fread($stream, 1024 * 64);
                flush();
            }
            fclose($stream);
        }, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="payment-proof.' . pathinfo($renewalRequest->payment_screenshot, PATHINFO_EXTENSION) . '"',
        ]);
    }

    // ── Approve ───────────────────────────────────────────────────────────────

    public function approve(Request $request, RenewalRequest $renewalRequest): \Illuminate\Http\RedirectResponse
    {
        abort_unless($renewalRequest->isPending(), 422);

        $data = $request->validate([
            'admin_note'     => 'nullable|string|max:500',
            'billing_months' => 'required|integer|min:1|max:24',
            'price_paid'     => 'required|numeric|min:0',
        ]);

        // Activate the subscription
        $this->subscriptionService->renew(
            company:       $renewalRequest->company,
            package:       $renewalRequest->package,
            billingMonths: (int) $data['billing_months'],
            pricePaid:     (float) $data['price_paid'],
            notes:         'Renewal request #' . $renewalRequest->id . '. ' . ($data['admin_note'] ?? ''),
        );

        // Mark request as approved
        $renewalRequest->update([
            'status'      => RenewalRequest::STATUS_APPROVED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_note'  => $data['admin_note'] ?? null,
        ]);

        return redirect()->route('admin.renewal-requests.index')
            ->with('success',
                __('Subscription activated for :company — :months month(s).', [
                    'company' => $renewalRequest->company->name,
                    'months'  => $data['billing_months'],
                ])
            );
    }

    // ── Reject ────────────────────────────────────────────────────────────────

    public function reject(Request $request, RenewalRequest $renewalRequest): \Illuminate\Http\RedirectResponse
    {
        abort_unless($renewalRequest->isPending(), 422);

        $data = $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $renewalRequest->update([
            'status'      => RenewalRequest::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_note'  => $data['admin_note'],
        ]);

        return redirect()->route('admin.renewal-requests.index')
            ->with('success', __('Renewal request rejected.'));
    }
}