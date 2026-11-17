<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\Destination;
use App\Models\ForumCategory;
use App\Models\RewardProgram;
use App\Models\User;
use App\Services\Integrations\IntegrationRegistry;
use Database\Seeders\BangladeshDestinationSeeder;
use Database\Seeders\ContentDraftSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SingaporeGuideSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, BangladeshDestinationSeeder::class, SingaporeGuideSeeder::class, ContentDraftSeeder::class]);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    public function test_drafts_are_complete_bilingual_sourced_and_never_published(): void
    {
        $guides = ContentEntry::query()->where('type', 'guide')->get();
        $this->assertCount(7, $guides);
        foreach ($guides as $guide) {
            $this->assertNotSame('', trim((string) $guide->body), $guide->slug);
            $this->assertNotSame('', trim((string) $guide->body_bn), $guide->slug);
            $this->assertNotEmpty($guide->source_urls, $guide->slug);
            $this->assertTrue(collect($guide->source_urls)->every(fn ($url) => str_starts_with($url, 'https://')), $guide->slug);
            $this->assertNotNull($guide->review_status, $guide->slug);
            $this->assertLessThanOrEqual(70, mb_strlen((string) $guide->seo_title), $guide->slug);
            $this->assertLessThanOrEqual(170, mb_strlen((string) $guide->seo_description), $guide->slug);
            $this->assertStringNotContainsStringIgnoringCase('we guarantee', $guide->body);
        }

        $this->assertSame(0, ContentEntry::query()->where('status', 'published')->count());
        $this->assertSame(0, ForumCategory::query()->where('is_active', true)->count());
        $this->assertFalse(RewardProgram::query()->where('slug', 'eagle-rewards')->sole()->is_published);
        $this->assertSame(0, Destination::query()->where('is_published', true)->count());
        $this->get(route('consultancy.guides.show', 'students-pass-guidance'))->assertNotFound();
        $this->get(route('company.show', 'eagle-global-hub-ltd'))->assertNotFound();
    }

    public function test_reseeding_is_idempotent_and_never_overwrites_editor_changes(): void
    {
        $count = ContentEntry::query()->count();
        $guide = ContentEntry::query()->where('slug', 'tuition-fees')->sole();
        $guide->update(['body' => 'Edited by staff.']);
        Destination::query()->where('slug', 'sylhet')->update(['description' => 'Staff text.']);

        $this->seed(ContentDraftSeeder::class);

        $this->assertSame($count, ContentEntry::query()->count());
        $this->assertSame('Edited by staff.', $guide->refresh()->body);
        $this->assertSame('Staff text.', Destination::query()->where('slug', 'sylhet')->value('description'));
        $this->assertSame(4, ForumCategory::query()->count());
        $this->assertSame(1, RewardProgram::query()->count());
    }

    public function test_review_status_blocks_publishing_until_an_owner_approves(): void
    {
        $guide = ContentEntry::query()->where('slug', 'students-pass-guidance')->sole();
        $editor = $this->user('content-editor');

        $this->actingAs($editor)->patch(route('admin.content.status', $guide), ['status' => 'published'])->assertSessionHasErrors('status');
        $payload = ['title' => $guide->title, 'slug' => $guide->slug, 'summary' => $guide->summary, 'body' => $guide->body, 'sort_order' => 1,
            'source_urls_text' => implode("\n", $guide->source_urls), 'review_status' => 'approved'];
        $this->actingAs($editor)->patch(route('admin.content.update', $guide), $payload)->assertSessionHasErrors('review_status');
        $this->assertSame('needs_owner_review', $guide->refresh()->review_status);

        $owner = $this->user('super-admin');
        $this->actingAs($owner)->patch(route('admin.content.update', $guide), [...$payload, 'last_verified_at' => now()->toDateString()])->assertSessionHasNoErrors();
        $this->actingAs($editor)->patch(route('admin.content.status', $guide), ['status' => 'published'])->assertSessionHasNoErrors();

        $this->get(route('consultancy.guides.show', 'students-pass-guidance'))->assertOk()
            ->assertSee('<h2>Who needs a Student&#039;s Pass</h2>', false)
            ->assertSee('<li>Passport biodata page, and birth certificate if applicable</li>', false)
            ->assertSee('Information last checked against official sources')
            ->assertSee('hreflang="bn"', false)
            ->assertDontSee('ICA pages fetched and checked');
        $this->get(route('consultancy.guides.show', ['students-pass-guidance', 'lang' => 'bn']))->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('consultancy.guides.show', 'students-pass-guidance').'?lang=bn">', false)
            ->assertSee('স্টুডেন্টস পাস');
    }

    public function test_renderer_escapes_markup_in_headings_and_lists(): void
    {
        $entry = new ContentEntry(['title' => 'X', 'slug' => 'x', 'summary' => 'S', 'body' => "## <script>alert(1)</script>\n- <b>bold</b>\n- two", 'sort_order' => 0]);
        $entry->type = ContentType::Inspiration;
        $entry->status = ContentEntry::STATUS_PUBLISHED;
        $entry->save();

        $this->get(route('inspiration.show', 'x'))->assertOk()
            ->assertSee('<h2>&lt;script&gt;alert(1)&lt;/script&gt;</h2>', false)
            ->assertSee('<li>&lt;b&gt;bold&lt;/b&gt;</li>', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_about_page_uses_published_statements_in_both_languages(): void
    {
        $this->get(route('about'))->assertOk()->assertDontSee('Our Mission')->assertDontSee('What makes us different');

        ContentEntry::query()->where('type', 'page_section')->whereIn('slug', ['mission', 'why-different'])
            ->update(['status' => 'published', 'review_status' => 'approved']);

        $this->get(route('about'))->assertOk()->assertSee('Our Mission')->assertSee('honest, clear and well-organised support')
            ->assertSee('What makes us different')->assertSee('<li>Honest advice', false)->assertDontSee('Our Vision');
        $this->get(route('about', ['lang' => 'bn']))->assertOk()->assertSee('আমাদের মিশন')->assertSee('কেন আমরা ব্যতিক্রম?')->assertSee('সৎ পরামর্শ');
    }

    public function test_english_interface_text_can_be_edited_from_admin(): void
    {
        $editor = $this->user('content-editor');
        $this->actingAs($editor)->get(route('admin.translations.index', ['locale' => 'en', 'search' => 'More from us']))->assertOk()->assertSee('More from us');
        $this->actingAs($editor)->patch(route('admin.translations.update'), ['locale' => 'en', 'source_hash' => hash('sha256', 'More from us'), 'value' => 'Useful Links'])->assertRedirect();

        $this->flushSession();
        app('translator')->setLoaded([]);
        $this->get(route('about'))->assertSee('Useful Links')->assertDontSee('More from us');
    }

    public function test_queue_health_warns_about_stuck_and_failed_jobs(): void
    {
        config(['queue.default' => 'database']);
        $status = fn () => collect(app(IntegrationRegistry::class)->all())->firstWhere('key', 'queue');
        $this->assertSame(IntegrationRegistry::STATUS_CONNECTED, $status()['status']);

        DB::table('jobs')->insert(['queue' => 'default', 'payload' => '{}', 'attempts' => 0, 'available_at' => now()->subHour()->getTimestamp(), 'created_at' => now()->subHour()->getTimestamp()]);
        $this->assertSame(IntegrationRegistry::STATUS_ATTENTION, $status()['status']);
        $this->assertStringContainsString('worker may not be running', $status()['notes']);

        DB::table('jobs')->delete();
        DB::table('failed_jobs')->insert(['uuid' => 'x-1', 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'boom', 'failed_at' => now()]);
        $this->assertStringContainsString('failed job', $status()['notes']);
    }

    public function test_private_storage_is_not_served_and_housekeeping_is_scheduled(): void
    {
        Storage::fake('student_documents');
        Storage::disk('student_documents')->put('1/probe.pdf', 'PRIVATE');

        $this->assertFalse(Route::has('storage.local'));
        $this->assertFalse(config('filesystems.disks.local.serve'));
        $this->get('/storage/student-documents/1/probe.pdf')->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee('student-documents')->assertDontSee('/admin');

        $events = collect(app(Schedule::class)->events())->map(fn ($event) => $event->description ?? $event->command)->implode(' | ');
        $this->assertStringContainsString('purge-expired-api-tokens', $events);
        $this->assertStringContainsString('queue:prune-failed', $events);
    }
}
