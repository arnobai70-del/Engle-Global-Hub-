<?php

namespace App\Http\Controllers;

use App\Contracts\Payments\PaymentGateway;
use App\Models\WebhookEvent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives payment provider webhooks. Signatures are verified by the adapter
 * and each provider event is processed only once.
 */
class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, string $provider, PaymentGateway $gateway): JsonResponse
    {
        abort_unless($gateway->isConfigured() && hash_equals($gateway->key(), $provider), 404);

        $event = $gateway->verifyWebhook($request);
        if ($event === null) {
            Log::warning('Payment webhook rejected: invalid signature.', ['provider' => $provider]);

            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        try {
            WebhookEvent::query()->create([
                'provider' => $provider,
                'event_id' => $event['id'],
                'event_type' => $event['type'],
                'payload_hash' => hash('sha256', $request->getContent()),
            ]);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['message' => 'Already processed.']);
        }

        // Matching the event to a payment record is implemented together with a
        // real provider adapter; until then events are stored for audit only.
        Log::info('Payment webhook received.', ['provider' => $provider, 'type' => $event['type']]);

        return response()->json(['message' => 'Received.']);
    }
}
