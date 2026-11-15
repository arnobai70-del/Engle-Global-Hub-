<?php

use App\Services\Payments\UnavailablePaymentGateway;

return [
    /*
     * Online payment gateway adapter class. Leave as the unavailable gateway
     * until a provider adapter has been built, tested and approved. Offline
     * payment recording (Admin › Payments) works regardless.
     */
    'gateway' => UnavailablePaymentGateway::class,
];
