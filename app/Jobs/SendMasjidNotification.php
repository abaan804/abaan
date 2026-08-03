<?php

namespace App\Jobs;

use App\Services\Notifications\NotificationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Masjid\Models\MasjidMember;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidNotificationLog;

class SendMasjidNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted before being marked failed.
     */
    public int $tries = 3;

    /**
     * Wait N seconds before retrying after a failure.
     */
    public int $backoff = 60;

    /**
     * Timeout for each attempt (Twilio API can be slow under load).
     */
    public int $timeout = 30;

    public function __construct(
        protected MasjidMosque $mosque,
        protected MasjidMember $member,
        protected string $channel,
        protected string $to,
        protected string $type,
        protected string $subject,
        protected string $message,
    ) {
    }

    public function handle(NotificationManager $manager): void
    {
        $result = $manager->send($this->channel, $this->to, $this->subject, $this->message);

        MasjidNotificationLog::create([
            'company_id' => $this->mosque->company_id,
            'mosque_id' => $this->mosque->id,
            'member_id' => $this->member->id,
            'channel' => $this->channel,
            'type' => $this->type,
            'status' => $result['success'] ? 'sent' : 'failed',
            'payload' => [
                'to' => $this->to,
                'subject' => $this->subject,
                'message' => $this->message,
                'error' => $result['error'] ?? null,
            ],
            'sent_at' => $result['success'] ? now() : null,
        ]);

        if (! $result['success']) {
            Log::warning('MasjidNotification failed', [
                'mosque_id' => $this->mosque->id,
                'member_id' => $this->member->id,
                'channel' => $this->channel,
                'error' => $result['error'],
            ]);

            // Re-throw to trigger the retry backoff — but only log to failed_jobs
            // after all retries are exhausted, not on first attempt.
            if ($this->attempts() >= $this->tries) {
                Log::error('MasjidNotification permanently failed after all retries', [
                    'mosque_id' => $this->mosque->id,
                    'member_id' => $this->member->id,
                    'channel' => $this->channel,
                ]);
                return; // don't throw — already logged as failed in notification_logs
            }

            throw new \RuntimeException($result['error'] ?? 'Notification send failed');
        }
    }

    /**
     * Called when the job has failed all retries.
     * Updates the notification log status to 'failed' if a log was written on first attempt.
     */
    public function failed(\Throwable $e): void
    {
        Log::error('SendMasjidNotification job failed permanently', [
            'mosque' => $this->mosque->id,
            'member' => $this->member->id,
            'channel' => $this->channel,
            'error' => $e->getMessage(),
        ]);
    }
}