<?php

namespace App\Contracts\Messaging;

/**
 * Sends a WhatsApp Business template message. Bound to an "unavailable" implementation until a provider adapter
 * is built and tested; adding credentials alone does not activate it.
 */
interface WhatsAppSender
{
    public function isConfigured(): bool;

    public function sendTemplate(string $to, string $template, array $parameters = []): bool;
}
