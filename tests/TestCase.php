<?php

namespace Tests;

use App\Services\Integrations\ProviderStatus;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Configures the flight provider and records a passed connection test, which is
     * when signed-in customers get the online flight search instead of the enquiry page.
     */
    protected function makeFlightsLive(): void
    {
        config(['flight.search_provider' => 'duffel', 'flight.duffel.access_token' => 'test-token-for-ui-tests']);
        app(ProviderStatus::class)->recordVerified('flights');
    }
}
