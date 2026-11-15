<?php

namespace App\Services\Integrations;

use App\Services\SettingService;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * One place that reports every third-party integration the platform knows
 * about: whether an adapter exists, whether it is configured, which business
 * module it serves, and which manual fallback is used without it.
 *
 * Credentials stay in environment variables (project policy). This registry
 * only reports whether required values are present; it never returns them.
 */
class IntegrationRegistry
{
    public const STATUS_CONNECTED = 'connected';

    public const STATUS_NOT_CONFIGURED = 'not_configured';

    public const STATUS_NO_ADAPTER = 'no_adapter';

    public const STATUS_LOG_ONLY = 'log_only';

    public const STATUS_ATTENTION = 'attention';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_CONNECTED => 'Configured',
        self::STATUS_NOT_CONFIGURED => 'Adapter ready — not configured',
        self::STATUS_NO_ADAPTER => 'No adapter yet — manual process',
        self::STATUS_LOG_ONLY => 'Development mode (not delivered)',
        self::STATUS_ATTENTION => 'Needs attention',
    ];

    public function __construct(
        private Repository $config,
        private TravelServiceRegistry $travel,
        private SettingService $settings,
    ) {}

    /**
     * @return list<array{key: string, group: string, name: string, module: string, provider: string, status: string, status_label: string, env: list<string>, missing: list<string>, fallback: string|null, fallback_route: string|null, fallback_url: string|null, notes: string}>
     */
    public function all(): array
    {
        $travel = collect($this->travel->all())->keyBy('key');

        return array_map(fn (array $item): array => [
            ...$item,
            'status_label' => self::STATUS_LABELS[$item['status']],
            'fallback_url' => $this->url($item['fallback_route']),
        ], [
            $this->flightSearch(),
            $this->travelProvider('hotels', 'Hotels', 'Hotel Booking', ['HOTELS_ENABLED', 'HOTEL_PROVIDER', 'DUFFEL_ACCESS_TOKEN'], $travel->get('hotels')['available'] ?? false, 'Hotel enquiry form', 'service-enquiries.create:hotel-booking'),
            $this->travelProvider('tours', 'Tours', 'Tour Packaging', ['TOURS_ENABLED', 'TOUR_PROVIDER', 'TOUR_API_KEY'], $travel->get('tours')['available'] ?? false, 'Tour package enquiry form', 'service-enquiries.create:tour-package'),
            $this->travelProvider('visa', 'Visa information', 'Tourist / Business / Work Visa', ['VISA_ENABLED', 'VISA_PROVIDER', 'VISA_API_KEY', 'SHERPA_API_BASE_URL'], $travel->get('visa')['available'] ?? false, 'Visa enquiry form', 'service-enquiries.create:tourist-visa'),
            $this->email(),
            $this->queue(),
            $this->noAdapter('sms', 'Communication', 'SMS', 'Student notifications', 'Email and in-portal notifications', 'notifications.index', 'SmsSender contract is ready; no provider adapter yet.'),
            $this->noAdapter('whatsapp', 'Communication', 'WhatsApp Business', 'Student and lead communication', 'WhatsApp number collected on enquiry forms; staff contact manually', null, 'WhatsAppSender contract is ready; no provider adapter yet.'),
            $this->noAdapter('payments', 'Payments', 'Payment gateway', 'Consultancy and travel payments', 'Offline payment recorded by staff', 'admin.payments.index', 'Gateway contract, signed-webhook endpoint and idempotency are ready (config/payments.php); a provider adapter still has to be built.'),
            $this->noAdapter('maps', 'Other', 'Maps', 'Explore Bangladesh, office location', 'Text address on the support page; OpenStreetMap links for saved coordinates', 'support', 'MapProvider contract is ready; no embedded map provider yet.'),
            $this->analytics(),
            $this->noAdapter('search-console', 'Marketing & SEO', 'Google Search Console', 'SEO', 'Submit /sitemap.xml manually in Search Console', 'sitemap', 'Verification is done in Search Console; the sitemap is generated automatically.'),
            $this->noAdapter('education-partners', 'Education', 'University / college partner APIs', 'Singapore Student Consultancy', 'Institutions and application statuses maintained by staff', 'admin.institutions.index'),
            $this->noAdapter('transport', 'Travel', 'Train / Bus / Car / Bike providers', 'Transportation', 'Transport enquiry form', 'service-enquiries.create:bus'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function flightSearch(): array
    {
        $provider = (string) $this->config->get('flight.search_provider', 'unavailable');
        $missing = $provider === 'duffel' && blank($this->config->get('flight.duffel.access_token')) ? ['DUFFEL_ACCESS_TOKEN'] : [];

        return [
            'key' => 'flights', 'group' => 'Travel', 'name' => 'Flights', 'module' => 'Air Ticket',
            'provider' => $provider,
            'status' => match (true) {
                $provider === 'duffel' && $missing === [] => self::STATUS_CONNECTED,
                default => self::STATUS_NOT_CONFIGURED,
            },
            'env' => ['FLIGHT_SEARCH_PROVIDER', 'DUFFEL_ACCESS_TOKEN', 'DUFFEL_LIVE_ORDER_CREATION_ENABLED'],
            'missing' => $provider === 'duffel' ? $missing : ['FLIGHT_SEARCH_PROVIDER'],
            'fallback' => 'Manual flight enquiry and BDT quotation', 'fallback_route' => 'flights.enquiries.create',
            'notes' => $provider === 'fixture' ? 'Fixture data is for development only and must not be shown as live fares.' : '',
        ];
    }

    /**
     * @param  list<string>  $env
     * @return array<string, mixed>
     */
    private function travelProvider(string $key, string $name, string $module, array $env, bool $available, string $fallback, string $fallbackRoute): array
    {
        $provider = (string) $this->config->get("travel_services.services.{$key}.provider", 'unavailable');

        return [
            'key' => $key, 'group' => 'Travel', 'name' => $name, 'module' => $module, 'provider' => $provider,
            'status' => $available ? self::STATUS_CONNECTED : self::STATUS_NOT_CONFIGURED,
            'env' => $env,
            'missing' => $available ? [] : $env,
            'fallback' => $fallback, 'fallback_route' => $fallbackRoute,
            'notes' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function email(): array
    {
        $mailer = (string) $this->config->get('mail.default', 'log');
        $logOnly = in_array($mailer, ['log', 'array'], true);

        return [
            'key' => 'email', 'group' => 'Communication', 'name' => 'Email', 'module' => 'Notifications', 'provider' => $mailer,
            'status' => $logOnly ? self::STATUS_LOG_ONLY : self::STATUS_CONNECTED,
            'env' => ['MAIL_MAILER', 'MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM_ADDRESS'],
            'missing' => $logOnly ? ['MAIL_MAILER'] : [],
            'fallback' => 'In-portal notifications', 'fallback_route' => 'notifications.index',
            'notes' => 'Notifications are queued; a queue worker (php artisan queue:work) must run in production.',
        ];
    }

    /**
     * Queued notifications are only delivered while a worker runs, so the
     * page warns when jobs wait too long or have failed.
     *
     * @return array<string, mixed>
     */
    private function queue(): array
    {
        $connection = (string) $this->config->get('queue.default', 'sync');
        $base = ['key' => 'queue', 'group' => 'Communication', 'name' => 'Queue worker', 'module' => 'Notifications and emails', 'provider' => $connection, 'env' => ['QUEUE_CONNECTION'], 'fallback' => null, 'fallback_route' => null];

        if ($connection === 'sync') {
            return [...$base, 'status' => self::STATUS_CONNECTED, 'missing' => [], 'notes' => 'Synchronous: notifications are sent during the request. No worker is needed, but pages respond more slowly.'];
        }

        try {
            $stale = DB::table('jobs')->where('available_at', '<', now()->subMinutes(10)->getTimestamp())->count();
            $failed = DB::table('failed_jobs')->count();
        } catch (QueryException) {
            return [...$base, 'status' => self::STATUS_ATTENTION, 'missing' => [], 'notes' => 'The jobs tables could not be read. Run the database migrations.'];
        }

        $problems = array_filter([
            $stale > 0 ? $stale.' job(s) waiting over 10 minutes — the queue worker may not be running' : null,
            $failed > 0 ? $failed.' failed job(s) — check with php artisan queue:failed' : null,
        ]);

        return [...$base,
            'status' => $problems === [] ? self::STATUS_CONNECTED : self::STATUS_ATTENTION,
            'missing' => [],
            'notes' => $problems === [] ? 'No stuck or failed jobs. Keep php artisan queue:work running under a process manager.' : implode('. ', $problems).'.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function analytics(): array
    {
        $enabled = (bool) $this->settings->get('analytics', 'meta_pixel_enabled', false)
            && filled($this->settings->get('analytics', 'meta_pixel_id'));

        return [
            'key' => 'meta-pixel', 'group' => 'Marketing & SEO', 'name' => 'Meta Pixel', 'module' => 'Analytics', 'provider' => 'meta',
            'status' => $enabled ? self::STATUS_CONNECTED : self::STATUS_NOT_CONFIGURED,
            'env' => [], 'missing' => $enabled ? [] : ['Settings › analytics › meta_pixel_enabled / meta_pixel_id'],
            'fallback' => null, 'fallback_route' => null,
            'notes' => 'Managed in Settings. Never send student document data to marketing tools.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function noAdapter(string $key, string $group, string $name, string $module, string $fallback, ?string $fallbackRoute, string $notes = ''): array
    {
        return [
            'key' => $key, 'group' => $group, 'name' => $name, 'module' => $module, 'provider' => '—',
            'status' => self::STATUS_NO_ADAPTER, 'env' => [], 'missing' => [],
            'fallback' => $fallback, 'fallback_route' => $fallbackRoute, 'notes' => $notes,
        ];
    }

    /**
     * Resolves "route.name" or "route.name:param" to a relative URL, if the route exists.
     */
    private function url(?string $target): ?string
    {
        if ($target === null) {
            return null;
        }

        [$name, $parameter] = array_pad(explode(':', $target, 2), 2, null);

        return Route::has($name) ? route($name, $parameter === null ? [] : [$parameter], false) : null;
    }
}
