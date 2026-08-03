<?php

namespace App\Services\Notifications;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioWhatsAppProvider implements NotificationChannelInterface
{
    protected ?string $lastError = null;

    public function send(string $to, string $subject, string $message): bool
    {
        if (! $this->isConfigured()) {
            $this->lastError = 'WhatsApp provider is not configured.';
            return false;
        }

        $sid = Setting::getValue('whatsapp_twilio_sid');
        $token = Setting::getValue('whatsapp_twilio_token');
        $from = Setting::getValue('whatsapp_twilio_from'); // format: whatsapp:+14155238886

        try {
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => 'whatsapp:' . $to,
                    'From' => $from,
                    'Body' => $message,
                ]);

            if ($response->failed()) {
                $this->lastError = $response->json('message') ?? 'Twilio WhatsApp request failed.';
                Log::warning('WhatsApp send failed', ['to' => $to, 'response' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            Log::warning('WhatsApp send exception', ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function isConfigured(): bool
    {
        return Setting::getValue('whatsapp_enabled', false)
            && Setting::getValue('whatsapp_twilio_sid')
            && Setting::getValue('whatsapp_twilio_token')
            && Setting::getValue('whatsapp_twilio_from');
    }
}