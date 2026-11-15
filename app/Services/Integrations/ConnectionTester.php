<?php

namespace App\Services\Integrations;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Runs a single read-only request against a configured provider to confirm
 * credentials work. Only the HTTP status is reported; response bodies and
 * credentials are never returned or logged.
 */
class ConnectionTester
{
    public function __construct(private Repository $config) {}

    /**
     * Integration keys that have a connection test.
     *
     * @return list<string>
     */
    public function testable(): array
    {
        return ['flights'];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function test(string $key): array
    {
        return match ($key) {
            'flights' => $this->duffel(),
            default => ['ok' => false, 'message' => 'No connection test exists for this integration.'],
        };
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function duffel(): array
    {
        $token = $this->config->get('flight.duffel.access_token');
        if ($this->config->get('flight.search_provider') !== 'duffel' || ! is_string($token) || $token === '') {
            return ['ok' => false, 'message' => 'Not configured: set FLIGHT_SEARCH_PROVIDER=duffel and DUFFEL_ACCESS_TOKEN.'];
        }

        $baseUrl = rtrim((string) $this->config->get('flight.duffel.base_url', 'https://api.duffel.com'), '/');
        if (! str_starts_with($baseUrl, 'https://')) {
            return ['ok' => false, 'message' => 'The Duffel base URL must use HTTPS.'];
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders(['Duffel-Version' => (string) $this->config->get('flight.duffel.api_version', 'v2'), 'Accept' => 'application/json'])
                ->connectTimeout(5)->timeout(10)
                ->get($baseUrl.'/air/airlines', ['limit' => 1]);
        } catch (ConnectionException) {
            return ['ok' => false, 'message' => 'Could not reach the provider (network error or timeout).'];
        }

        return match (true) {
            $response->successful() => ['ok' => true, 'message' => 'Connected: the provider accepted the credentials (HTTP '.$response->status().').'],
            in_array($response->status(), [401, 403], true) => ['ok' => false, 'message' => 'The provider rejected the credentials (HTTP '.$response->status().').'],
            default => ['ok' => false, 'message' => 'The provider returned an error (HTTP '.$response->status().').'],
        };
    }
}
