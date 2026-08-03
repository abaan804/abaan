<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\RenewalRequest;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function expired(Request $request): View
    {
        $company = $request->user()->company;

        $lastSub = Subscription::where('company_id', $company->id)
            ->with('package')
            ->latest()
            ->first();

        // Check if company has a pending renewal request already
        $pendingRequest = RenewalRequest::where('company_id', $company->id)
            ->pending()
            ->latest()
            ->first();

        $trialUsed = $company->trial_used;
        $reason    = session('reason', 'expired');

        return view('tenant.subscription.expired', compact(
            'company', 'lastSub', 'trialUsed', 'reason', 'pendingRequest'
        ));
    }

    public function renew(Request $request): View
    {
        $company  = $request->user()->company;
        $packages = Package::active()
            ->with('moduleDefinitions')
            ->orderBy('sort_order')
            ->get();

        $trialUsed = $company->trial_used;

        // Pending request — show status instead of form
        $pendingRequest = RenewalRequest::where('company_id', $company->id)
            ->pending()
            ->with(['package', 'submittedBy'])
            ->latest()
            ->first();

        // Payment account details from settings
        $paymentDetails = [
            'bank_name'      => setting('bank_name'),
            'account_title'  => setting('account_title'),
            'account_number' => setting('account_number'),
            'jazzcash'       => setting('jazzcash_number'),
            'easypaisa'      => setting('easypaisa_number'),
            'iban'           => setting('iban'),
        ];

        return view('tenant.subscription.renew', compact(
            'company', 'packages', 'trialUsed',
            'pendingRequest', 'paymentDetails'
        ));
    }

    /**
     * Submit renewal request with payment screenshot.
     */
    public function submitRenewalRequest(Request $request): \Illuminate\Http\RedirectResponse
    {
        $company = $request->user()->company;

        // Block if a pending request already exists
        if (RenewalRequest::where('company_id', $company->id)->pending()->exists()) {
            return back()->with('error',
                __('You already have a pending renewal request. Please wait for admin review.')
            );
        }

        $data = $request->validate([
            'package_id'         => 'required|exists:packages,id',
            'billing_months'     => 'required|integer|min:1|max:24',
            'payment_method'     => 'required|string|max:100',
            'transaction_id'     => 'nullable|string|max:100',
            'payment_screenshot' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'note'               => 'nullable|string|max:1000',
        ]);

        $package = Package::findOrFail($data['package_id']);

        // Store screenshot privately (not public)
        $path = $request->file('payment_screenshot')
            ->store('renewal-screenshots/' . $company->id, 'local');

        RenewalRequest::create([
            'company_id'         => $company->id,
            'package_id'         => $package->id,
            'submitted_by'       => $request->user()->id,
            'billing_months'     => $data['billing_months'],
            'amount'             => $package->monthly_price * $data['billing_months'],
            'payment_screenshot' => $path,
            'payment_method'     => $data['payment_method'],
            'transaction_id'     => $data['transaction_id'] ?? null,
            'note'               => $data['note'] ?? null,
            'status'             => RenewalRequest::STATUS_PENDING,
        ]);

        return redirect()->route('subscription.renew')
            ->with('success',
                __('Your renewal request has been submitted successfully. We will review your payment and activate your subscription shortly.')
            );
    }

    /**
     * Cancel a pending renewal request (company side).
     */
    public function cancelRequest(Request $request, RenewalRequest $renewalRequest): \Illuminate\Http\RedirectResponse
    {
        abort_unless(
            $renewalRequest->company_id === $request->user()->company_id
            && $renewalRequest->isPending(),
            403
        );

        // Delete screenshot from storage
        if ($renewalRequest->payment_screenshot) {
            Storage::disk('local')->delete($renewalRequest->payment_screenshot);
        }

        $renewalRequest->delete();

        return redirect()->route('subscription.renew')
            ->with('success', __('Renewal request cancelled.'));
    }
}