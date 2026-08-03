<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationProvider implements NotificationChannelInterface
{
    protected ?string $lastError = null;

    public function send(string $to, string $subject, string $message): bool
    {
        try {
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)->subject($subject);
            });

            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            Log::warning('Email notification failed', ['to' => $to, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function isConfigured(): bool
    {
        // Email already works via Abaan's existing .env mail config — always "configured"
        // unless mail is fundamentally broken, which we don't pre-check here.
        return true;
    }
}