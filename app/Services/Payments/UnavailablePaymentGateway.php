<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGateway;
use Illuminate\Http\Request;

/**
 * Default when no payment provider adapter is installed: nothing is accepted.
 */
class UnavailablePaymentGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'unavailable';
    }

    public function isConfigured(): bool
    {
        return false;
    }

    public function verifyWebhook(Request $request): ?array
    {
        return null;
    }
}
