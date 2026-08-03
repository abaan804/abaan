<?php

namespace App\Services\Notifications;

interface NotificationChannelInterface
{
    /**
     * Send a notification. Returns true on success, false on failure.
     * Should never throw — catch provider exceptions internally and return false,
     * letting the caller log the failure reason via getLastError().
     */
    public function send(string $to, string $subject, string $message): bool;

    public function getLastError(): ?string;

    public function isConfigured(): bool;
}