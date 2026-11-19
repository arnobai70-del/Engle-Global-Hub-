<?php

namespace App\Services\Admin;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\Destination;
use App\Models\Institution;
use App\Models\MediaAsset;
use App\Models\TeamMember;
use App\Services\Content\PageContent;
use App\Services\Content\PublicPageAvailability;
use App\Services\Integrations\IntegrationRegistry;
use App\Services\PhoneNumber;
use App\Services\SettingService;
use App\Services\SiteFacts;
use App\Services\System\ProductionReadiness;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Admin › Website launch checklist: one page that collects what still has to be filled in or
 * fixed before launch. It only reads the existing sources (PublicPageAvailability, SiteFacts,
 * the API & Integrations registry and the production-readiness checks) and never repeats
 * their rules.
 */
class LaunchChecklist
{
    public const GREEN = 'green';

    public const AMBER = 'amber';

    public const RED = 'red';

    /** Cache key the scheduler writes every minute (routes/console.php). */
    public const SCHEDULER_HEARTBEAT = 'egh.scheduler.last_run';

    /** Readiness checks already shown as their own System row. */
    private const READINESS_SHOWN_ELSEWHERE = [
        'Queue connection is asynchronous and configured',
        'Mail transport is configured for delivery',
    ];

    public function __construct(
        private PublicPageAvailability $availability,
        private SiteFacts $facts,
        private PageContent $pageContent,
        private SettingService $settings,
        private IntegrationRegistry $integrations,
        private ProductionReadiness $readiness,
    ) {}

    /**
     * @return list<array{key: string, title: string, items: list<array{label: string, status: string, detail: string, url: string|null}>}>
     */
    public function sections(): array
    {
        return [
            ['key' => 'explore', 'title' => 'Explore Bangladesh', 'items' => $this->explore()],
            ['key' => 'trust', 'title' => 'Trust and contact', 'items' => $this->trust()],
            ['key' => 'tools', 'title' => 'Planning tools', 'items' => $this->planningTools()],
            ['key' => 'about', 'title' => 'About page sections', 'items' => $this->about()],
            ['key' => 'system', 'title' => 'System', 'items' => $this->system()],
        ];
    }

    /**
     * @param  list<array{items: list<array{status: string}>}>|null  $sections  Already built sections, to avoid running the checks twice.
     * @return array{green: int, amber: int, red: int}
     */
    public function counts(?array $sections = null): array
    {
        $counts = [self::GREEN => 0, self::AMBER => 0, self::RED => 0];
        foreach ($sections ?? $this->sections() as $section) {
            foreach ($section['items'] as $item) {
                $counts[$item['status']]++;
            }
        }

        return $counts;
    }

    /** @return list<array{label: string, status: string, detail: string, url: string|null}> */
    private function explore(): array
    {
        $published = $this->count(fn (): int => Destination::query()->published()->inBangladesh()->count());
        $withoutPhoto = $this->count(fn (): int => Destination::query()->published()->inBangladesh()
            ->where(fn ($query) => $query->whereNull('cover_image_path')->orWhere('cover_image_path', ''))->count());

        return [
            $this->item(
                'Published destinations',
                $published > 0 ? self::GREEN : self::AMBER,
                $published > 0
                    ? $published.' destination(s) published.'
                    : 'No destination is published yet. The page still shows travel essentials and the trip form.',
                $this->route('admin.destinations.index'),
            ),
            $this->item(
                'Destination photos',
                $published === 0 ? self::AMBER : ($withoutPhoto === 0 ? self::GREEN : self::AMBER),
                $published === 0 ? 'Add destinations with a cover photo.' : ($withoutPhoto === 0 ? 'Every published destination has a cover photo.' : $withoutPhoto.' published destination(s) have no cover photo.'),
                $this->route('admin.destinations.index'),
            ),
        ];
    }

    /** @return list<array{label: string, status: string, detail: string, url: string|null}> */
    private function trust(): array
    {
        $facts = $this->route('admin.facts.edit');
        $credentials = $this->facts->credentials();
        $storedPhone = trim((string) $this->safely(fn () => $this->settings->get('contact', 'phone'), ''));
        $stories = $this->count(fn (): int => ContentEntry::query()->published(ContentType::SuccessStory)->count());
        $logos = $this->count(fn (): int => Institution::query()->publiclyListed()->whereNotNull('logo_media_id')->count());

        return [
            $this->item(
                'Office phone',
                $storedPhone === '' ? self::AMBER : (PhoneNumber::e164($storedPhone) !== null ? self::GREEN : self::RED),
                match (true) {
                    $storedPhone === '' => 'Empty, so the official number '.PhoneNumber::display(SiteFacts::OFFICIAL_PHONE).' is shown. Enter it in Homepage › Contact to confirm it.',
                    PhoneNumber::e164($storedPhone) !== null => 'Shown as '.PhoneNumber::display($storedPhone).'.',
                    default => '"'.$storedPhone.'" is not a usable number: call and WhatsApp links will not work. Use the international format, for example +8801953626481.',
                },
                $this->route('admin.homepage.index'),
            ),
            $this->filled('WhatsApp number', $this->facts->get('whatsapp_number'), 'Empty, so the office phone is used for WhatsApp.', $facts, self::AMBER),
            $this->filled('Office hours', $this->facts->get('office_hours'), 'Not shown until it is filled in.', $facts),
            $this->filled('Trade licence number', $credentials['trade_licence'], 'Not shown in the trust strip, About page or footer until it is filled in.', $facts),
            $this->filled('Company registration number', $credentials['registration'], 'Not shown until it is filled in.', $facts),
            $this->item('Google rating', $this->facts->googleRating() !== null ? self::GREEN : self::AMBER, $this->facts->googleRating() !== null ? 'Shown with the review count and link.' : 'Hidden until the rating, review count and https reviews link are all filled in.', $facts),
            $this->item('Our numbers', $this->facts->numbers() !== [] ? self::GREEN : self::AMBER, $this->facts->numbers() !== [] ? count($this->facts->numbers()).' figure(s) shown.' : 'The numbers strip is hidden until at least one figure you can prove is filled in.', $facts),
            $this->item('Testimonials', $stories > 0 ? self::GREEN : self::AMBER, $stories > 0 ? $stories.' published success story(ies).' : 'No published success story yet, so testimonials are hidden.', $this->route('admin.content.index', ['type' => ContentType::SuccessStory->value])),
            $this->item('Partner logos', $logos > 0 ? self::GREEN : self::AMBER, $logos > 0 ? $logos.' public institution(s) with a logo.' : 'The partner logo strip is hidden until a public institution has a logo.', $this->route('admin.institutions.index')),
        ];
    }

    /** @return list<array{label: string, status: string, detail: string, url: string|null}> */
    private function planningTools(): array
    {
        $tools = [
            ['consultancy.malaysia.eligibility', 'Eligibility checker', 'at least one eligibility rule is active.', 'admin.eligibility-rules.index'],
            ['consultancy.malaysia.cost-calculator', 'Cost calculator', 'Business facts has the MYR to Taka exchange rate and at least one Malaysia cost.', 'admin.facts.edit'],
            ['consultancy.malaysia.courses', 'Course finder', 'at least one active course belongs to a public Malaysian institution.', 'admin.courses.index'],
            ['appointments.book', 'Book an appointment', 'booking hours are set (days, start and end time, slot length, days ahead, meeting modes) and a free slot exists.', 'admin.appointments.index'],
        ];

        return array_map(fn (array $tool): array => $this->availability->isAvailable($tool[0])
            ? $this->item($tool[1], self::GREEN, 'Open to the public.', $this->route($tool[3]))
            : $this->item($tool[1], self::AMBER, 'Hidden from the public until '.$tool[2].' Visitors are sent to Study in Malaysia meanwhile.', $this->route($tool[3])), $tools);
    }

    /** @return list<array{label: string, status: string, detail: string, url: string|null}> */
    private function about(): array
    {
        $sections = $this->route('admin.content.index', ['type' => ContentType::PageSection->value]);
        $team = $this->count(fn (): int => TeamMember::query()->published()->whereNotNull('photo_path')->where('photo_path', '!=', '')->count());
        $gallery = $this->count(fn (): int => MediaAsset::query()->publishedIn('office_gallery')->count());

        $items = [$this->filled('Our story', $this->facts->get('about_story'), 'The story block is hidden until it is filled in.', $this->route('admin.facts.edit'))];
        foreach (['mission' => 'Mission', 'vision' => 'Vision', 'why-different' => 'Why we are different'] as $slug => $label) {
            $published = $this->pageContent->section($slug) !== null || filled($this->safely(fn () => $this->settings->get('company', $slug === 'why-different' ? 'why_choose_us_bn' : $slug), null));
            $items[] = $this->item($label, $published ? self::GREEN : self::AMBER, $published ? 'Published.' : 'Hidden until the "'.$slug.'" page section is published.', $sections);
        }
        $items[] = $this->item('Team', $team > 0 ? self::GREEN : self::AMBER, $team > 0 ? $team.' published member(s) with a photo.' : 'Hidden until a published team member has a real photo.', $this->route('admin.team-members.index'));
        $items[] = $this->item('Office gallery', $gallery > 0 ? self::GREEN : self::AMBER, $gallery > 0 ? $gallery.' published photo(s).' : 'Hidden until a photo is published in the office gallery.', $this->route('admin.media.index'));

        return $items;
    }

    /** @return list<array{label: string, status: string, detail: string, url: string|null}> */
    private function system(): array
    {
        $integrations = $this->route('admin.integrations.index');
        $registry = collect($this->safely(fn (): array => $this->integrations->all(), []))->keyBy('key');
        $mailer = (string) config('mail.default');
        $queue = (string) config('queue.default');
        $email = $registry->get('email');
        $worker = $registry->get('queue');

        $items = [
            $email !== null && $email['status'] === IntegrationRegistry::STATUS_LOG_ONLY
                ? $this->item('Enquiry emails', self::RED, 'MAIL_MAILER is "'.$mailer.'": enquiry emails are NOT being sent. Set the SMTP details in .env.', $integrations)
                : $this->item('Enquiry emails', self::GREEN, 'Sent with the "'.$mailer.'" mailer.', $integrations),
            $queue === 'sync'
                ? $this->item('Queue connection', self::AMBER, 'QUEUE_CONNECTION is "sync": emails are sent while the visitor waits, which slows forms down. Use "database" with a queue worker.', $integrations)
                : $this->item('Queue connection', self::GREEN, 'QUEUE_CONNECTION is "'.$queue.'".', $integrations),
        ];
        if ($queue !== 'sync' && $worker !== null) {
            $items[] = $this->item('Queue worker', $worker['status'] === IntegrationRegistry::STATUS_CONNECTED ? self::GREEN : self::RED, (string) $worker['notes'], $integrations);
        }

        $lastRun = Cache::get(self::SCHEDULER_HEARTBEAT);
        $items[] = match (true) {
            ! is_int($lastRun) => $this->item('Scheduler', self::RED, 'No scheduler run recorded. Add the cron entry: * * * * * php artisan schedule:run', null),
            $lastRun < now()->subMinutes(10)->getTimestamp() => $this->item('Scheduler', self::RED, 'Last run '.now()->createFromTimestamp($lastRun)->diffForHumans().'. Check the cron entry for php artisan schedule:run.', null),
            default => $this->item('Scheduler', self::GREEN, 'Last run '.now()->createFromTimestamp($lastRun)->diffForHumans().'.', null),
        };

        $passed = 0;
        foreach ($this->safely(fn (): array => $this->readiness->checks(), []) as $check) {
            if (in_array($check['label'], self::READINESS_SHOWN_ELSEWHERE, true)) {
                continue;
            }
            if ($check['status'] === ProductionReadiness::PASS) {
                $passed++;

                continue;
            }
            $items[] = $this->item($check['label'], $check['status'] === ProductionReadiness::FAIL ? self::RED : self::AMBER, 'From php artisan app:production-readiness.', null);
        }
        $items[] = $this->item('Other production checks', self::GREEN, $passed.' check(s) passed (php artisan app:production-readiness).', null);

        return $items;
    }

    /** @return array{label: string, status: string, detail: string, url: string|null} */
    private function filled(string $label, ?string $value, string $whenEmpty, ?string $url, string $emptyStatus = self::AMBER): array
    {
        return $value !== null && $value !== ''
            ? $this->item($label, self::GREEN, 'Filled in.', $url)
            : $this->item($label, $emptyStatus, $whenEmpty, $url);
    }

    /** @return array{label: string, status: string, detail: string, url: string|null} */
    private function item(string $label, string $status, string $detail, ?string $url): array
    {
        return ['label' => $label, 'status' => $status, 'detail' => $detail, 'url' => $url];
    }

    /** @param array<string, string> $parameters */
    private function route(string $name, array $parameters = []): ?string
    {
        return app('router')->has($name) ? route($name, $parameters) : null;
    }

    /** @param callable(): int $count */
    private function count(callable $count): int
    {
        return (int) $this->safely($count, 0);
    }

    private function safely(callable $callback, mixed $default): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return $default;
        }
    }
}
