<?php

namespace App\Contracts\Messaging;

/**
 * Builds map links or embeds for a location. Bound to an "unavailable" implementation until a provider adapter
 * is built and tested; adding credentials alone does not activate it.
 */
interface MapProvider
{
    public function isConfigured(): bool;

    public function linkFor(float $latitude, float $longitude): ?string;
}
