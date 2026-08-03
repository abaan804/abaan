<?php

namespace App\Services\Notifications;

use App\Models\Setting;

class NotificationManager
{
    public function provider(string $channel): ?NotificationChannelInterface
    {
        return match ($channel) {
            'email' => app(EmailNotificationProvider::class),
            'sms' => app(TwilioSmsProvider::class),
            'whatsapp' => app(TwilioWhatsAppProvider::class),
            default => null,
        };
    }

    public function isChannelActive(string $channel): bool
    {
        if ($channel === 'email') {
            return Setting::getValue('email_notifications_enabled', true);
        }

        $provider = $this->provider($channel);

        return $provider && $provider->isConfigured();
    }

    /**
     * Send via a given channel. Returns ['success' => bool, 'error' => ?string].
     */
    public function send(string $channel, string $to, string $subject, string $message): array
    {
        $provider = $this->provider($channel);

        if (! $provider) {
            return ['success' => false, 'error' => "Unknown channel: {$channel}"];
        }

        if (! $this->isChannelActive($channel)) {
            return ['success' => false, 'error' => 'This channel is not enabled or configured.'];
        }

        $success = $provider->send($to, $subject, $message);

        return ['success' => $success, 'error' => $success ? null : $provider->getLastError()];
    }
}