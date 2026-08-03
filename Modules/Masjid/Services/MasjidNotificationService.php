<?php

namespace Modules\Masjid\Services;

use App\Jobs\SendMasjidNotification;
use Modules\Masjid\Models\MasjidMember;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidNotificationLog;
use Modules\Masjid\Models\MasjidPayment;
use Modules\Masjid\Models\MasjidSeason;
use Modules\Masjid\Models\MasjidSeasonMember;

class MasjidNotificationService
{
    public function __construct(protected MasjidSettingService $settingService)
    {
    }

    /**
     * Payment received — dispatched automatically after recording a payment.
     */
    public function sendPaymentReceived(MasjidPayment $payment): void
    {
        $payment->loadMissing(['member', 'season', 'mosque']);
        $mosque = $payment->mosque;
        $member = $payment->member;

        if (! $mosque || ! $member) return;

        $subject = __('Payment Received — :mosque', ['mosque' => $mosque->mosque_name]);
        $message = __(
            "Assalamu Alaikum :name,\n\nYour payment of :amount has been received for :season.\nReceipt No: :receipt\nDate: :date\n\nJazakAllah Khair,\n:mosque",
            [
                'name' => $member->name,
                'amount' => $this->settingService->formatCurrency($mosque, $payment->amount_paid),
                'season' => $payment->season?->name ?? '—',
                'receipt' => $payment->receipt_no ?? '—',
                'date' => $payment->payment_date?->format('d M Y') ?? '—',
                'mosque' => $mosque->mosque_name,
            ]
        );

        $this->dispatch($mosque, $member, 'payment_received', $subject, $message);
    }

    /**
     * Season assigned — dispatched for each member when a season is created with auto_assign.
     */
    public function sendSeasonAssigned(MasjidMosque $mosque, MasjidMember $member, MasjidSeason $season): void
    {
        $subject = __('New Contribution Season — :season', ['season' => $season->name]);
        $message = __(
            "Assalamu Alaikum :name,\n\nA new contribution season has been started.\n\nSeason: :season\nPeriod: :start — :end\nYour Contribution: :amount\n\nPlease arrange your payment at your earliest convenience.\n\nJazakAllah Khair,\n:mosque",
            [
                'name' => $member->name,
                'season' => $season->name,
                'start' => $season->start_date->format('d M Y'),
                'end' => $season->end_date->format('d M Y'),
                'amount' => $this->settingService->formatCurrency($mosque, $season->contribution_amount),
                'mosque' => $mosque->mosque_name,
            ]
        );

        $this->dispatch($mosque, $member, 'season_assigned', $subject, $message);
    }

    /**
     * Balance reminder — dispatched manually from the Notifications page,
     * or automatically via the SendMasjidReminders artisan command.
     */
    public function sendBalanceReminder(
        MasjidMosque $mosque,
        MasjidMember $member,
        MasjidSeason $season,
        float $balance
    ): void {
        $subject = __('Contribution Reminder — :mosque', ['mosque' => $mosque->mosque_name]);
        $message = __(
            "Assalamu Alaikum :name,\n\nThis is a friendly reminder that your contribution for ':season' is outstanding.\n\nAmount Due: :amount\n\nPlease contact us to arrange your payment.\n\nJazakAllah Khair,\n:mosque",
            [
                'name' => $member->name,
                'season' => $season->name,
                'amount' => $this->settingService->formatCurrency($mosque, $balance),
                'mosque' => $mosque->mosque_name,
            ]
        );

        $this->dispatch($mosque, $member, 'balance_reminder', $subject, $message);
    }

    /**
     * Core dispatcher — resolves enabled channels from mosque settings
     * and dispatches one queued job per channel.
     * Logging happens inside the job itself (post-send), not here.
     */
    protected function dispatch(
        MasjidMosque $mosque,
        MasjidMember $member,
        string $type,
        string $subject,
        string $message
    ): void {
        $setting = $this->settingService->forMosque($mosque);

        $channels = [];

        if ($setting->notification_email && $member->email) {
            $channels['email'] = $member->email;
        }
        if ($setting->notification_sms && $member->mobile) {
            $channels['sms'] = $member->mobile;
        }
        if ($setting->notification_whatsapp && ($member->whatsapp ?: $member->mobile)) {
            $channels['whatsapp'] = $member->whatsapp ?: $member->mobile;
        }

        if (empty($channels)) return;

        foreach ($channels as $channel => $to) {
            // Log as 'queued' immediately — the job updates to 'sent'/'failed' after running
            MasjidNotificationLog::create([
                'company_id' => $mosque->company_id,
                'mosque_id' => $mosque->id,
                'member_id' => $member->id,
                'channel' => $channel,
                'type' => $type,
                'status' => 'queued',
                'payload' => [
                    'to' => $to,
                    'subject' => $subject,
                    'message' => $message,
                ],
                'sent_at' => null,
            ]);

            SendMasjidNotification::dispatch(
                $mosque, $member, $channel, $to, $type, $subject, $message
            )->onQueue('notifications');
        }
    }

    /**
     * Send season-assigned notifications to all assigned members — called
     * after MasjidSeasonService::create() when auto_assign = true.
     * Dispatches in batch, each member gets their own queued job.
     */
    public function sendSeasonAssignedToAll(
        MasjidMosque $mosque,
        MasjidSeason $season,
        \Illuminate\Support\Collection $members
    ): void {
        foreach ($members as $member) {
            $this->sendSeasonAssigned($mosque, $member, $season);
        }
    }
}