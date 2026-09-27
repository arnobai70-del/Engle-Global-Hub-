<?php

namespace App\Services\Travel;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class TravelServiceRegistry
{
    public function __construct(private readonly Repository $config) {}

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        $services = $this->config->get('travel_services.services', []);
        if (! is_array($services)) return [];

        $capabilities = [];
        foreach ($services as $key => $service) {
            if (! is_string($key) || ! is_array($service)) continue;

            $providerAvailable = $this->isAvailable($service);
            $configuredRoute = $service['route_name'] ?? null;
            $pageRouteName = is_string($configuredRoute) && Route::has($configuredRoute)
                ? $configuredRoute
                : null;

            $capabilities[$key] = [
                'key' => $key,
                'label' => (string) ($service['label'] ?? ucfirst($key)),
                'available' => $providerAvailable,
                'provider_available' => $providerAvailable,
                'demo_mode' => ! $providerAvailable,
                'status' => $providerAvailable ? 'Live Provider' : 'Demo Preview',
                // Keep the legacy route contract live-provider-only.
                'route_name' => $providerAvailable ? $pageRouteName : null,
                // Customer pages may still render safely in Demo Preview mode.
                'page_route_name' => $pageRouteName,
                'permission' => is_string($service['permission'] ?? null) ? $service['permission'] : null,
            ];
        }

        return $capabilities;
    }

    /** @param array<string, mixed> $service */
    private function isAvailable(array $service): bool
    {
        if (($service['enabled'] ?? false) !== true) return false;
        $routeName = $service['route_name'] ?? null;
        if (! is_string($routeName) || ! Route::has($routeName)) return false;
        if (($service['provider_required'] ?? true) === false) return true;

        $providerName = $service['provider'] ?? null;
        $contract = $service['contract'] ?? null;
        $providers = $service['providers'] ?? [];
        if (! is_string($providerName) || $providerName === '' || $providerName === 'unavailable' || ! is_string($contract) || ! is_array($providers)) return false;

        $providerClass = $providers[$providerName] ?? null;
        if (! is_string($providerClass) || ! is_a($providerClass, $contract, true)) return false;

        $dependencies = $service['provider_dependencies'][$providerName] ?? [];
        if (! is_array($dependencies)) return false;
        foreach ($dependencies as $dependencyContract => $dependencyClass) {
            if (! is_string($dependencyContract) || ! is_string($dependencyClass) || ! is_a($dependencyClass, $dependencyContract, true)) return false;
        }

        $requirements = $service['provider_requirements'][$providerName] ?? [];
        if (! is_array($requirements)) return false;
        foreach ($requirements as $requirement) {
            if (! is_string($requirement)) return false;
            $value = data_get($service, $requirement);
            if (! is_string($value) || trim($value) === '') return false;
        }

        $rules = $service['provider_rules'][$providerName] ?? [];
        return is_array($rules) && ! Validator::make($service, $rules)->fails();
    }
}
