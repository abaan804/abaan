<?php

namespace App\Services\Notifications;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioSmsProvider implements NotificationChannelInterface
{
    protected ?string $lastError = null;

    public function send(string $to, string $subject, string $message): bool
    {
        if (! $this->isConfigured()) {
            $this->lastError = 'SMS provider is not configured.';
            return false;
        }

        $sid = Setting::getValue('sms_twilio_sid');
        $token = Setting::getValue('sms_twilio_token');
        $from = Setting::getValue('sms_twilio_from');

        try {
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $to,
                    'From' => $from,
                    'Body' => $message,
                ]);

            if ($response->failed()) {
                $this->lastError = $response->json('message') ?? 'Twilio request failed.';
                Log::warning('SMS send failed', ['to' => $to, 'response' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            Log::warning('SMS send exception', ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function isConfigured(): bool
    {
        return Setting::getValue('sms_enabled', false)
            && Setting::getValue('sms_twilio_sid')
            && Setting::getValue('sms_twilio_token')
            && Setting::getValue('sms_twilio_from');
    }
}