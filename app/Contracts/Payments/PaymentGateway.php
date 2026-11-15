<?php

namespace App\Contracts\Payments;

use Illuminate\Http\Request;

/**
 * Contract a future online payment provider adapter must implement. The site
 * never stores card numbers: payment happens on the provider's hosted page and
 * results are confirmed server-side (webhook or API lookup), never from the
 * browser redirect alone.
 */
interface PaymentGateway
{
    public function key(): string;

    /**
     * True only when an adapter exists and its credentials are configured.
     */
    public function isConfigured(): bool;

    /**
     * Verifies the webhook signature and returns the parsed event, or null if
     * the signature is invalid.
     *
     * @return array{id: string, type: string, payment_reference: string|null, status: string|null}|null
     */
    public function verifyWebhook(Request $request): ?array;
}
