<?php

namespace Modules\Masjid\Services;

use Illuminate\Support\Facades\DB;
use Modules\Masjid\Models\MasjidActivityLog;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidPayment;
use Modules\Masjid\Models\MasjidPaymentAttachment;
use Modules\Masjid\Models\MasjidSeasonMember;

class MasjidPaymentService
{
    public function __construct(protected MasjidSettingService $settingService)
    {
    }

    /**
     * Record a new payment and update the season_member's cached totals.
     */
    public function create(MasjidMosque $mosque, array $data): MasjidPayment
    {
        return DB::transaction(function () use ($mosque, $data) {
            $data['company_id'] = $mosque->company_id;
            $data['mosque_id'] = $mosque->id;
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
            $data['received_by'] = $data['received_by'] ?? auth()->id();
            $data['receipt_no'] = $this->settingService->nextReceiptNumber($mosque);

            $payment = MasjidPayment::create($data);

            $this->recalculate($payment->seasonMember);
            $this->log($mosque, 'payment.created', $payment, [
                'after' => $payment->only(['amount_paid', 'payment_method', 'receipt_no']),
            ]);

            return $payment;
        });
    }

    /**
     * Update a payment and re-sync the season_member totals.
     */
    public function update(MasjidPayment $payment, array $data): MasjidPayment
    {
        return DB::transaction(function () use ($payment, $data) {
            $before = $payment->only(['amount_paid', 'payment_method', 'payment_date']);
            $data['updated_by'] = auth()->id();

            $payment->update($data);
            $this->recalculate($payment->fresh()->seasonMember);

            $this->log($payment->mosque, 'payment.updated', $payment, [
                'before' => $before,
                'after' => $payment->only(['amount_paid', 'payment_method', 'payment_date']),
            ]);

            return $payment;
        });
    }

    /**
     * Soft-delete a payment and re-sync the season_member totals.
     */
    public function delete(MasjidPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $seasonMember = $payment->seasonMember;

            $this->log($payment->mosque, 'payment.deleted', $payment, [
                'before' => $payment->only(['amount_paid', 'receipt_no']),
            ]);

            $payment->delete();

            if ($seasonMember) {
                $this->recalculate($seasonMember);
            }
        });
    }

    /**
     * Store uploaded attachments for a payment.
     */
    public function storeAttachments(MasjidPayment $payment, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('masjid/attachments', 'public');
            MasjidPaymentAttachment::create([
                'payment_id' => $payment->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    /**
     * Recalculate and persist amount_paid + status on a season_member row.
     * This is the single place where status logic lives.
     */
    protected function recalculate(MasjidSeasonMember $seasonMember): void
    {
        $seasonMember->recalculate();
    }

    protected function log(MasjidMosque $mosque, string $action, $subject, array $properties = []): void
    {
        MasjidActivityLog::create([
            'company_id' => $mosque->company_id,
            'mosque_id' => $mosque->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}