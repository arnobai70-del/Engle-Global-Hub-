<?php

namespace App\Services\Integrations;

use App\Models\User;
use App\Services\Feature\FeatureManager;
use App\Services\SettingService;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * The single source of truth for the public state of an API-dependent feature.
 *
 * A provider is only "Live" after its configuration passed a connection test that
 * was recorded for exactly the current configuration. Everything else is shown
 * honestly as waiting for an API, enquiry-only, a local demo, or disabled.
 */
class ProviderStatus
{
    public const LIVE = 'live';

    public const WAITING = 'waiting';

    public const ENQUIRY = 'enquiry';

    public const DEMO = 'demo';

    public const DISABLED = 'disabled';

    /** @var array<string, string> */
    public const LABELS = [
        self::LIVE => 'Live Provider',
        self::WAITING => 'Waiting for API',
        self::ENQUIRY => 'Enquiry only',
        self::DEMO => 'Demo Preview',
        self::DISABLED => 'Disabled',
    ];

    /**
     * What visitors see. Technical states (waiting for an API, demo data) are
     * never shown on the public website; admins still see LABELS.
     */
    public const PUBLIC_LABELS = [
        self::LIVE => 'Book online',
        self::WAITING => 'Coming soon',
        self::ENQUIRY => 'Enquiry only',
        self::DEMO => 'Coming soon',
        self::DISABLED => 'Unavailable',
    ];

    /** Capabilities that need an external provider, with the feature switch that controls them. */
    public const CAPABILITIES = [
        'flights' => ['feature' => 'flights', 'label' => 'Live flight search and booking'],
        'hotels' => ['feature' => 'hotels', 'label' => 'Live hotel availability and booking'],
        'tours' => ['feature' => 'tours', 'label' => 'Live tour booking'],
        'visa' => ['feature' => 'visa', 'label' => 'Live visa requirement lookup'],
        'transport' => ['feature' => null, 'label' => 'Live train, bus, car and bike booking'],
        'payments' => ['feature' => 'payments', 'label' => 'Online payment'],
        'maps' => ['feature' => null, 'label' => 'Interactive map'],
        'sms' => ['feature' => null, 'label' => 'SMS notifications'],
        'whatsapp' => ['feature' => null, 'label' => 'WhatsApp messaging'],
        'push' => ['feature' => null, 'label' => 'Mobile push notifications'],
        'mobile_app' => ['feature' => null, 'label' => 'Mobile app download'],
    ];

    public function __construct(
        private Repository $config,
        private TravelServiceRegistry $travel,
        private FeatureManager $features,
        private SettingService $settings,
    ) {}

    public function of(string $capability, ?User $user = null): string
    {
        $user ??= Auth::user() instanceof User ? Auth::user() : null;
        $feature = self::CAPABILITIES[$capability]['feature'] ?? null;
        if ($feature !== null && ! $this->features->isVisibleTo($feature, $user)) {
            return self::DISABLED;
        }

        return match ($capability) {
            'flights' => $this->flights(),
            'hotels', 'tours', 'visa' => $this->travelProvider($capability),
            'mobile_app' => $this->mobileApp(),
            default => self::WAITING,
        };
    }

    public function label(string $state): string
    {
        return self::LABELS[$state] ?? self::LABELS[self::WAITING];
    }

    /**
     * Records that the connection test passed for the current configuration.
     */
    public function recordVerified(string $capability): void
    {
        $this->settings->set('integrations', $capability.'_verified', $this->fingerprint($capability));
    }

    public function isVerified(string $capability): bool
    {
        try {
            $stored = $this->settings->get('integrations', $capability.'_verified');
        } catch (Throwable) {
            return false;
        }

        return is_string($stored) && $stored !== '' && hash_equals($stored, $this->fingerprint($capability));
    }

    private function flights(): string
    {
        $provider = (string) $this->config->get('flight.search_provider');
        if ($provider === 'fixture') {
            // Fixture data is only ever a local demo; never present it as live anywhere else.
            return app()->environment(['local', 'testing']) ? self::DEMO : self::WAITING;
        }

        return $provider === 'duffel' && filled($this->config->get('flight.duffel.access_token')) && $this->isVerified('flights')
            ? self::LIVE
            : self::WAITING;
    }

    private function travelProvider(string $key): string
    {
        $service = $this->travel->all()[$key] ?? null;

        return ($service['available'] ?? false) && $this->isVerified($key) ? self::LIVE : self::WAITING;
    }

    private function mobileApp(): string
    {
        try {
            $homepage = $this->settings->group('homepage');
        } catch (Throwable) {
            return self::WAITING;
        }

        return filled($homepage['google_play_url'] ?? null) || filled($homepage['app_store_url'] ?? null) ? self::LIVE : self::WAITING;
    }

    /**
     * A hash of the provider configuration, so a changed key or provider needs a new test.
     */
    private function fingerprint(string $capability): string
    {
        $parts = match ($capability) {
            'flights' => [$this->config->get('flight.search_provider'), $this->config->get('flight.duffel.access_token'), $this->config->get('flight.duffel.base_url')],
            default => [$capability, json_encode($this->config->get('travel_services.services.'.$capability))],
        };

        return hash('sha256', implode('|', array_map(static fn ($part): string => (string) $part, $parts)));
    }
}
