<?php

namespace App\Services\Integrations;

use App\Contracts\Messaging\MapProvider;
use App\Contracts\Messaging\SmsSender;
use App\Contracts\Messaging\WhatsAppSender;

/**
 * Default for SMS, WhatsApp and maps while no provider adapter exists: nothing
 * is sent and no map is embedded. Callers fall back to email, the in-portal
 * notifications and plain address text.
 */
class UnavailableMessaging implements MapProvider, SmsSender, WhatsAppSender
{
    public function isConfigured(): bool
    {
        return false;
    }

    public function send(string $to, string $message): bool
    {
        return false;
    }

    public function sendTemplate(string $to, string $template, array $parameters = []): bool
    {
        return false;
    }

    public function linkFor(float $latitude, float $longitude): ?string
    {
        return null;
    }
}
