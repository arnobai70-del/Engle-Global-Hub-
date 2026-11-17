<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\MediaAsset;
use App\Models\Offer;
use App\Models\Redirect;
use App\Models\TeamMember;
use App\Models\TranslationOverride;
use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\BangladeshDestinationSeeder;
use Database\Seeders\CompanyContentSettingsSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SeoSettingsSeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Database\Seeders\TeamMemberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, ServiceDestinationSeeder::class, TeamMemberSeeder::class, CompanyContentSettingsSeeder::class, SeoSettingsSeeder::class, BangladeshDestinationSeeder::class]);
        Storage::fake('public');
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    public function test_about_page_shows_only_published_team_and_written_mission(): void
    {
        $this->get(route('about'))->assertOk()
            ->assertDontSee('Tajul Islam')->assertDontSee('Our Mission')->assertDontSee('Our Vision');

        app(SettingService::class)->set('company', 'mission', 'Owner approved mission text.', 'string', true);
        TeamMember::query()->where('name', 'Tajul Islam')->update(['is_published' => true]);

        $this->get(route('about'))->assertOk()
            ->assertSee('Our Mission')->assertSee('Owner approved mission text.')->assertDontSee('Our Vision')
            ->assertSee('Tajul Islam')->assertSee('Managing Director')
            ->assertDontSee('Sharmeen Akter Rony');
    }

    public function test_media_library_upload_publish_and_office_gallery(): void
    {
        $editor = $this->user('content-editor');

        $this->actingAs($editor)->post(route('admin.media.store'), [
            'image' => UploadedFile::fake()->create('fake.jpg', 20, 'text/plain'), 'alt_text' => 'x', 'collection' => 'office_gallery',
        ])->assertSessionHasErrors('image');
        $this->actingAs($editor)->post(route('admin.media.store'), [
            'image' => UploadedFile::fake()->image('office.jpg', 800, 600), 'collection' => 'office_gallery',
        ])->assertSessionHasErrors('alt_text');

        $this->actingAs($editor)->post(route('admin.media.store'), [
            'image' => UploadedFile::fake()->image('office.jpg', 800, 600), 'alt_text' => 'Reception desk at our Gulshan office', 'collection' => 'office_gallery',
        ])->assertRedirect();
        $asset = MediaAsset::query()->sole();
        $this->assertFalse($asset->is_published);
        $this->assertSame(800, $asset->width);
        Storage::disk('public')->assertExists($asset->path);
        $this->get(route('about'))->assertDontSee('Reception desk at our Gulshan office');

        $this->actingAs($editor)->patch(route('admin.media.update', $asset), [
            'alt_text' => 'Reception desk at our Gulshan office', 'collection' => 'office_gallery', 'sort_order' => 1, 'is_published' => '1',
        ])->assertRedirect();
        $this->get(route('about'))->assertSee('alt="Reception desk at our Gulshan office"', false);

        $this->actingAs($this->user('seo-manager'))->delete(route('admin.media.destroy', $asset))->assertForbidden();
        $this->actingAs($editor)->delete(route('admin.media.destroy', $asset))->assertRedirect();
        Storage::disk('public')->assertMissing($asset->path);
    }

    public function test_language_switch_keeps_label_and_translation_overrides_apply(): void
    {
        $this->get(route('about'))->assertOk()
            ->assertSee('class="egho-switch" href="'.route('about', ['lang' => 'bn']).'"', false)
            ->assertSee('aria-current="true" data-locale-switch>EN</a>', false)
            ->assertSee('More from us');

        $this->get(route('about', ['lang' => 'bn']))->assertOk()
            ->assertSee('aria-current="true" data-locale-switch>বাংলা</a>', false)
            ->assertSee('আমাদের আরও সেবা')
            ->assertSee(route('about', ['lang' => 'en']), false);

        $editor = $this->user('content-editor');
        $this->actingAs($editor)->get(route('admin.translations.index', ['search' => 'More from us']))->assertOk()->assertSee('আমাদের আরও সেবা');
        $this->actingAs($editor)->patch(route('admin.translations.update'), [
            'locale' => 'bn', 'source_hash' => hash('sha256', 'More from us'), 'value' => 'দরকারি লিংক <b>x</b>',
        ])->assertRedirect();
        $this->assertSame('দরকারি লিংক x', TranslationOverride::query()->sole()->value);

        $this->flushSession();
        // Each real request starts fresh; in tests the translator keeps loaded lines in memory.
        app('translator')->setLoaded([]);
        $this->get(route('about', ['lang' => 'bn']))->assertSee('দরকারি লিংক x')->assertDontSee('আমাদের আরও সেবা');

        $this->actingAs($editor)->patch(route('admin.translations.update'), ['locale' => 'bn', 'source_hash' => hash('sha256', 'Not a key'), 'value' => 'x'])->assertNotFound();
        $this->actingAs($editor)->patch(route('admin.translations.update'), ['locale' => 'bn', 'source_hash' => hash('sha256', 'More from us'), 'value' => ''])->assertRedirect();
        $this->assertDatabaseCount('translation_overrides', 0);
        $this->actingAs($this->user('crm-staff'))->get(route('admin.translations.index'))->assertForbidden();
    }

    public function test_navigation_connects_new_pages_through_existing_menus(): void
    {
        $this->get(route('home'))->assertOk()
            // Listing pages without published content stay out of the menus until something is published.
            ->assertDontSee('href="'.route('offers.index').'"', false)
            ->assertDontSee('href="'.route('reviews.index').'"', false)
            ->assertSee(route('services.show', 'export-import'), false)
            ->assertSee(route('b2b.info'), false)
            ->assertSee(route('search'), false)
            ->assertSee(route('services.show', 'tourist-visa'), false);

        $this->get(route('work-visa.apply'))->assertRedirect(route('work-visa.index').'#quote-form');
        $this->get(route('work-visa.index'))->assertOk()->assertSee('action="'.route('leads.quote', 'work-visa').'"', false);
    }

    public function test_global_search_covers_public_content_only(): void
    {
        Destination::query()->where('slug', 'sylhet')->update(['is_published' => true, 'summary' => 'Tea gardens and hills.']);
        Offer::query()->create(['title' => 'Tea Tour Deal', 'slug' => 'tea-tour', 'summary' => 'Visit tea estates', 'is_published' => true]);
        Offer::query()->create(['title' => 'Hidden Tea Draft', 'slug' => 'hidden', 'summary' => 'Draft', 'is_published' => false]);

        $this->get(route('search', ['q' => 'tea']))->assertOk()
            ->assertSee('Sylhet')->assertSee('Tea Tour Deal')->assertDontSee('Hidden Tea Draft');
        $this->get(route('search', ['q' => 'kuakata']))->assertOk()->assertSee('Explore Bangladesh')->assertDontSee('/explore-bangladesh/kuakata', false);
        $this->get(route('search', ['q' => 'singapore']))->assertOk()->assertSee(route('consultancy.singapore'), false);
        $this->get(route('search', ['q' => 'bus']))->assertOk()->assertSee(route('service-enquiries.create', 'bus'), false);
        $this->get(route('search', ['q' => 'x']))->assertOk()->assertDontSee('Search results');
    }

    public function test_seo_manager_edits_metadata_and_safe_redirects_work(): void
    {
        $seo = $this->user('seo-manager');

        $this->actingAs($seo)->patch(route('admin.seo.pages.update'), [
            'route' => 'consultancy.singapore', 'title' => 'Study in Singapore — Free Consultation', 'description' => 'Plan your Singapore studies.',
        ])->assertRedirect();
        $this->get(route('consultancy.singapore'))->assertSee('<title>Study in Singapore — Free Consultation | Eagle Global Hub LTD</title>', false);

        $this->actingAs($seo)->post(route('admin.seo.redirects.store'), ['from_path' => '/old-study', 'to_url' => '/study-in-singapore', 'status_code' => 301])->assertRedirect();
        $this->get('/old-study')->assertRedirect('/study-in-singapore')->assertStatus(301);
        $this->assertSame(1, Redirect::query()->sole()->hits);

        foreach ([
            ['from_path' => '/admin', 'to_url' => '/'],
            ['from_path' => '/x', 'to_url' => '//evil.example'],
            ['from_path' => '/y', 'to_url' => 'http://evil.example'],
            ['from_path' => '/loop', 'to_url' => '/loop'],
            ['from_path' => '/chain', 'to_url' => '/old-study'],
            ['from_path' => '/old-study', 'to_url' => '/about'],
        ] as $bad) {
            $this->actingAs($seo)->post(route('admin.seo.redirects.store'), [...$bad, 'status_code' => 301])->assertSessionHasErrors();
        }
        $this->assertSame(1, Redirect::query()->count());

        $redirect = Redirect::query()->sole();
        $this->actingAs($seo)->patch(route('admin.seo.redirects.update', $redirect), ['is_active' => 0])->assertRedirect();
        $this->get('/old-study')->assertNotFound();
    }

    public function test_integration_connection_test_reports_status_without_leaking_secrets(): void
    {
        $superAdmin = $this->user('super-admin');
        config(['flight.search_provider' => 'unavailable']);
        $this->actingAs($superAdmin)->post(route('admin.integrations.test', 'flights'))->assertSessionHas('test_error');

        config(['flight.search_provider' => 'duffel', 'flight.duffel.access_token' => 'duffel_test_SECRET', 'flight.duffel.base_url' => 'https://api.duffel.com']);
        Http::fake(['api.duffel.com/*' => Http::sequence()
            ->push(['data' => []], 200)
            ->push(['errors' => [['message' => 'secret detail']]], 401)]);
        $this->actingAs($superAdmin)->post(route('admin.integrations.test', 'flights'))->assertSessionHas('status', fn (string $message): bool => str_contains($message, 'HTTP 200'));
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer duffel_test_SECRET') && str_contains($request->url(), '/air/airlines'));

        $this->actingAs($superAdmin)->post(route('admin.integrations.test', 'flights'))
            ->assertSessionHas('test_error', fn (string $message): bool => str_contains($message, 'HTTP 401') && ! str_contains($message, 'secret'));

        $this->actingAs($superAdmin)->post(route('admin.integrations.test', 'whatsapp'))->assertNotFound();
        $this->actingAs($this->user('admin'))->post(route('admin.integrations.test', 'flights'))->assertForbidden();
        $this->assertDatabaseHas('activity_logs', ['action' => 'integration.tested']);
    }
}
