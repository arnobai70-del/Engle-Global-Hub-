<?php

namespace App\Contracts\Messaging;

/**
 * Sends an SMS. Implementations must never log message bodies. Bound to an "unavailable" implementation until a provider adapter
 * is built and tested; adding credentials alone does not activate it.
 */
interface SmsSender
{
    public function isConfigured(): bool;

    public function send(string $to, string $message): bool;
}
