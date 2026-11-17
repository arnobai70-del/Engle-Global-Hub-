<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SingaporeGuideSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    private function create(User $user, ContentType $type, array $fields = []): ContentEntry
    {
        $this->actingAs($user)->post(route('admin.content.store'), [
            'type' => $type->value, 'title' => 'Entry title', 'summary' => 'Short summary', 'body' => 'Body text.', 'sort_order' => 0, ...$fields,
        ])->assertRedirect();

        return ContentEntry::query()->latest('id')->firstOrFail();
    }

    private function publish(User $user, ContentEntry $entry)
    {
        return $this->actingAs($user)->patch(route('admin.content.status', $entry), ['status' => 'published']);
    }

    public function test_company_profile_needs_owner_approval_and_edits_reset_it(): void
    {
        $editor = $this->user('content-editor');
        $entry = $this->create($editor, ContentType::CompanyProfile, ['title' => 'Trade Licence', 'body' => 'Registration <script>alert(1)</script>number TBC.']);
        $this->assertSame('Registration alert(1)number TBC.', $entry->body);
        $this->assertSame(ContentEntry::STATUS_DRAFT, $entry->status);

        $this->publish($editor, $entry)->assertSessionHasErrors('status');
        $this->get(route('company.show', $entry->slug))->assertNotFound();

        $this->actingAs($editor)->post(route('admin.content.approve', $entry))->assertForbidden();
        $owner = $this->user('super-admin');
        $this->actingAs($owner)->post(route('admin.content.approve', $entry))->assertRedirect();
        $this->publish($editor, $entry)->assertSessionHasNoErrors();
        $this->get(route('company.show', $entry->slug))->assertOk()->assertSee('Trade Licence')->assertDontSee('<script>', false);

        $this->actingAs($editor)->patch(route('admin.content.update', $entry), ['title' => 'Trade Licence', 'summary' => 'Changed claim', 'sort_order' => 0])->assertRedirect();
        $entry->refresh();
        $this->assertNull($entry->owner_approved_at);
        $this->assertSame(ContentEntry::STATUS_DRAFT, $entry->status);
        $this->get(route('company.show', $entry->slug))->assertNotFound();
    }

    public function test_singapore_guides_are_seeded_as_drafts_and_need_official_sources(): void
    {
        $this->seed(SingaporeGuideSeeder::class);
        $this->assertSame(7, ContentEntry::query()->where('type', 'guide')->where('status', 'draft')->count());
        $this->get(route('consultancy.guides.show', 'tuition-fees'))->assertNotFound();

        $editor = $this->user('content-editor');
        $guide = ContentEntry::query()->where('slug', 'students-pass-guidance')->sole();
        $this->actingAs($editor)->patch(route('admin.content.update', $guide), [
            'title' => $guide->title, 'summary' => 'Verified overview.', 'body' => 'Checked text.', 'sort_order' => 1,
            'source_urls_text' => 'http://insecure.example',
        ])->assertSessionHasErrors('source_urls.0');
        $this->publish($editor, $guide->refresh())->assertSessionHasErrors('status');

        $this->actingAs($editor)->patch(route('admin.content.update', $guide), [
            'title' => $guide->title, 'summary' => 'Verified overview.', 'body' => 'Checked text.', 'sort_order' => 1,
            'source_urls_text' => 'https://www.ica.gov.sg/', 'seo_title' => 'Student Pass Guide', 'title_bn' => 'স্টুডেন্টস পাস নির্দেশিকা',
        ])->assertRedirect();
        $this->publish($editor, $guide->refresh())->assertSessionHasNoErrors();

        $this->get(route('consultancy.guides.show', 'students-pass-guidance'))->assertOk()
            ->assertSee('<title>Student Pass Guide | Eagle Global Hub LTD</title>', false)
            ->assertSee('www.ica.gov.sg')->assertSee('"@type":"Article"', false);
        $this->get(route('consultancy.guides.show', ['students-pass-guidance', 'lang' => 'bn']))->assertSee('স্টুডেন্টস পাস নির্দেশিকা');
        $this->get(route('consultancy.singapore'))->assertSee(route('consultancy.guides.show', 'students-pass-guidance'), false);
        $this->get(route('sitemap'))->assertSee(route('consultancy.guides.show', 'students-pass-guidance'), false)
            ->assertDontSee(route('consultancy.guides.show', 'tuition-fees'), false);
        $this->get(route('search', ['q' => 'Pass Guidance']))->assertSee(route('consultancy.guides.show', 'students-pass-guidance'), false);
    }

    public function test_success_story_needs_recorded_consent(): void
    {
        $editor = $this->user('content-editor');
        $story = $this->create($editor, ContentType::SuccessStory, ['title' => 'From Dhaka to Singapore']);
        $this->publish($editor, $story)->assertSessionHasErrors('status');

        $this->actingAs($editor)->patch(route('admin.content.update', $story), [
            'title' => 'From Dhaka to Singapore', 'summary' => 'Shared with consent.', 'sort_order' => 0,
            'consent_reference' => 'CONSENT-2026-001', 'consent_recorded_at' => now()->toDateString(),
        ])->assertRedirect();
        $this->publish($editor, $story->refresh())->assertSessionHasNoErrors();
        $this->get(route('success-stories.index'))->assertOk()->assertSee('From Dhaka to Singapore');
    }

    public function test_restaurant_and_business_service_pages_preview_revisions_and_permissions(): void
    {
        $editor = $this->user('content-editor');
        $restaurant = $this->create($editor, ContentType::Restaurant, ['title' => 'Kacchi House', 'category' => 'Bangladeshi', 'location' => 'Gulshan, Dhaka', 'latitude' => '23.79', 'longitude' => '90.41', 'contact_phone' => '+8801700000000']);
        $this->get(route('restaurants.show', $restaurant->slug))->assertNotFound();
        $this->actingAs($editor)->get(route('admin.content.preview', $restaurant))->assertOk()->assertSee('Preview')->assertSee('noindex,nofollow', false);
        $this->publish($editor, $restaurant)->assertSessionHasNoErrors();
        $this->get(route('restaurants.show', $restaurant->slug))->assertOk()->assertSee('"@type":"Restaurant"', false)->assertSee('openstreetmap.org', false);

        $service = $this->create($editor, ContentType::BusinessService, ['title' => 'Export & Import Services', 'category' => 'export-import']);
        $this->publish($editor, $service);
        $this->get(route('business-services.show', $service->slug))->assertOk()->assertSee(route('service-enquiries.create', 'export-import'), false);

        $this->actingAs($editor)->patch(route('admin.content.update', $restaurant), ['title' => 'Kacchi House', 'summary' => 'New summary', 'sort_order' => 0])->assertRedirect();
        $first = $restaurant->revisions()->reorder('id')->first();
        $this->actingAs($editor)->post(route('admin.content.restore', $restaurant), ['revision' => $first->id])->assertRedirect();
        $this->assertSame('Short summary', $restaurant->refresh()->summary);

        $this->actingAs($this->user('crm-staff'))->get(route('admin.content.index'))->assertForbidden();
        $this->actingAs($this->user('customer'))->get(route('admin.content.preview', $restaurant))->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->get(route('admin.content.preview', $restaurant))->assertRedirect(route('login'));
    }

    public function test_page_sections_and_faqs_replace_defaults_only_when_published(): void
    {
        $this->get(route('about'))->assertSee('helping Bangladeshi students study in Singapore');
        $this->get(route('terms'))->assertSee('Flight Availability');

        $editor = $this->user('content-editor');
        $about = $this->create($editor, ContentType::PageSection, ['slug' => 'about-intro', 'title' => 'About intro', 'body' => 'We are a travel and study consultancy in Dhaka.']);
        $this->get(route('about'))->assertSee('helping Bangladeshi students study in Singapore');
        $this->publish($editor, $about)->assertSessionHasNoErrors();
        $this->get(route('about'))->assertSee('We are a travel and study consultancy in Dhaka.')->assertDontSee('helping Bangladeshi students study in Singapore');

        $terms = $this->create($editor, ContentType::PageSection, ['slug' => 'terms', 'title' => 'Terms and Conditions', 'body' => 'Owner approved terms.']);
        $this->publish($editor, $terms);
        $this->get(route('terms'))->assertSee('Owner approved terms.')->assertDontSee('Flight Availability');

        $this->actingAs($editor)->post(route('admin.content.store'), ['type' => 'page_section', 'slug' => 'homepage-hero', 'title' => 'x', 'sort_order' => 0])
            ->assertSessionHasErrors('slug');

        $faq = $this->create($editor, ContentType::Faq, ['title' => 'Do you guarantee visas?', 'body' => 'No. Decisions are made by the authorities.']);
        $this->get(route('support'))->assertDontSee('Do you guarantee visas?');
        $this->publish($editor, $faq);
        $this->get(route('support'))->assertSee('Do you guarantee visas?')->assertSee('"@type":"FAQPage"', false);
    }
}
