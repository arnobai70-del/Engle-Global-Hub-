<?php

namespace Tests\Feature;

use App\Models\ContentEntry;
use App\Models\Country;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Institution;
use App\Models\User;
use App\Services\Feature\FeatureManager;
use Database\Seeders\ContentDraftSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SingaporeGuideSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, SingaporeGuideSeeder::class, ContentDraftSeeder::class]);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    private function publishGuide(string $slug, ?string $category = null): ContentEntry
    {
        $guide = ContentEntry::query()->where('type', 'guide')->where('slug', $slug)->sole();
        $guide->forceFill(['status' => ContentEntry::STATUS_PUBLISHED, 'review_status' => ContentEntry::REVIEW_APPROVED, 'published_at' => now(), 'category' => $category])->save();

        return $guide;
    }

    public function test_guides_index_lists_only_published_approved_guides_and_never_drafts(): void
    {
        $this->get(route('consultancy.guides.index'))->assertOk()
            ->assertSee('Guides are on the way')
            ->assertDontSee('Singapore Student&#039;s Pass', false);

        $this->publishGuide('tuition-fees');
        $locked = ContentEntry::query()->where('slug', 'student-accommodation')->sole();
        $locked->forceFill(['status' => ContentEntry::STATUS_PUBLISHED, 'review_status' => 'needs_source_verification'])->save();

        $html = $this->get(route('consultancy.guides.index'))->assertOk()->getContent();
        $this->assertStringContainsString(route('consultancy.guides.show', 'tuition-fees'), $html);
        $this->assertStringNotContainsString(route('consultancy.guides.show', 'student-accommodation'), $html, 'a guide still under source verification must never be listed');
        $this->assertStringNotContainsString(route('consultancy.guides.show', 'pre-departure'), $html, 'draft guides must never be listed');
        $this->assertStringContainsString('Courses &amp; costs', $html);
    }

    public function test_guides_index_search_and_topic_filters_work_and_filtered_views_are_not_indexed(): void
    {
        $this->publishGuide('tuition-fees');
        $this->publishGuide('pre-departure');

        $this->get(route('consultancy.guides.index', ['topic' => 'students-pass']))->assertOk()
            ->assertSee(route('consultancy.guides.show', 'pre-departure'), false)
            ->assertDontSee(route('consultancy.guides.show', 'tuition-fees'), false)
            ->assertSee('<meta name="robots" content="noindex,follow">', false);

        $this->get(route('consultancy.guides.index', ['q' => 'tuition']))->assertOk()
            ->assertSee(route('consultancy.guides.show', 'tuition-fees'), false)
            ->assertDontSee(route('consultancy.guides.show', 'pre-departure'), false);

        $this->get(route('consultancy.guides.index', ['q' => 'zzz-no-match']))->assertOk()->assertSee('No guides match your search');
    }

    public function test_institutions_page_shows_only_owner_verified_institutions_and_no_private_fields(): void
    {
        $singapore = Country::query()->where('iso2', 'SG')->value('id');
        $listed = Institution::query()->create(['name' => 'Listed Polytechnic', 'email' => 'private@listed.test', 'registration_number' => 'REG-SECRET-1', 'notes' => 'Internal note', 'website_url' => 'https://listed.example.sg', 'country_id' => $singapore, 'status' => Institution::STATUS_ACTIVE]);
        $listed->forceFill(['is_public' => true, 'publicly_verified_at' => now()])->save();
        Institution::query()->create(['name' => 'Private Prospect College', 'status' => Institution::STATUS_ACTIVE]);
        $inactive = Institution::query()->create(['name' => 'Inactive Listed Institute', 'status' => Institution::STATUS_INACTIVE]);
        $inactive->forceFill(['is_public' => true])->save();

        $course = Course::query()->create(['institution_id' => $listed->id, 'name' => 'Diploma in Business', 'study_level' => 'diploma', 'duration_months' => 36, 'is_active' => true]);
        CourseIntake::query()->create(['course_id' => $course->id, 'starts_on' => now()->addMonths(4)->startOfMonth(), 'is_open' => true]);
        Course::query()->create(['institution_id' => $listed->id, 'name' => 'Withdrawn Course', 'is_active' => false]);

        $this->get(route('consultancy.institutions.index'))->assertOk()
            ->assertSee('Listed Polytechnic')
            ->assertSee('Diploma in Business')
            ->assertSee('36 months')
            ->assertSee('does not mean a partnership')
            ->assertDontSee('Private Prospect College')
            ->assertDontSee('Inactive Listed Institute')
            ->assertDontSee('Withdrawn Course')
            ->assertDontSee('private@listed.test')
            ->assertDontSee('REG-SECRET-1')
            ->assertDontSee('Internal note');
    }

    public function test_only_the_owner_can_list_an_institution_publicly(): void
    {
        $institution = Institution::query()->create(['name' => 'Candidate Institute', 'status' => Institution::STATUS_ACTIVE]);
        $payload = ['name' => 'Candidate Institute', 'status' => Institution::STATUS_ACTIVE, 'is_public' => '1'];

        $this->actingAs($this->user('admin'))->patch(route('admin.institutions.update', $institution), $payload)->assertRedirect();
        $this->assertFalse($institution->fresh()->is_public);

        $this->actingAs($this->user('super-admin'))->patch(route('admin.institutions.update', $institution), $payload)->assertRedirect();
        $this->assertTrue($institution->fresh()->is_public);
        $this->assertNotNull($institution->fresh()->publicly_verified_at);

        $this->actingAs($this->user('super-admin'))->patch(route('admin.institutions.update', $institution), [...$payload, 'is_public' => '0'])->assertRedirect();
        $this->assertFalse($institution->fresh()->is_public);
        $this->assertNull($institution->fresh()->publicly_verified_at);
    }

    public function test_travel_services_page_labels_each_service_honestly_and_follows_feature_switches(): void
    {
        $html = $this->get(route('travel-services.index'))->assertOk()->getContent();

        foreach (['Air Ticket', 'Hotels &amp; Homes', 'Tours', 'Tourist Visa', 'Work Visa', 'Business Visa', 'Business Class', 'Holiday Packages', 'Group Tours', 'Private Tours', 'Trains', 'Bus', 'Cars', 'Bike'] as $service) {
            $this->assertStringContainsString('<h3>'.$service.'</h3>', $html);
        }
        $this->assertStringContainsString('Enquiry only', $html);
        $this->assertStringContainsString('Waiting for API', $html);
        $this->assertStringContainsString(route('services.show', 'trains'), $html);
        $this->assertStringContainsString(route('services.show', 'business-visa'), $html);
        $this->assertStringContainsString(route('services.show', 'business-class'), $html);

        app(FeatureManager::class)->update('flights', ['enabled' => true, 'public_visible' => false, 'authenticated_visible' => false, 'admin_visible' => true, 'message' => null]);

        $this->get(route('travel-services.index'))->assertOk()
            ->assertDontSee('data-service="flights"', false)
            ->assertDontSee('data-service="business-class"', false);
    }

    public function test_new_pages_render_in_bengali_with_language_aware_canonical_and_hreflang(): void
    {
        $this->get(route('travel-services.index', ['lang' => 'bn']))->assertOk()
            ->assertSee('শুধু অনুসন্ধান')
            ->assertSee('<link rel="canonical" href="'.route('travel-services.index').'?lang=bn">', false)
            ->assertSee('hreflang="bn" href="'.route('travel-services.index').'?lang=bn"', false)
            ->assertSee('hreflang="x-default" href="'.route('travel-services.index').'"', false);

        $this->flushSession();
        $this->get(route('consultancy.institutions.index', ['lang' => 'en']))->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('consultancy.institutions.index').'">', false);
    }

    public function test_sitemap_lists_new_pages_only_when_they_have_public_content(): void
    {
        $this->get(route('sitemap'))->assertOk()
            ->assertSee(route('travel-services.index'), false)
            ->assertDontSee(route('consultancy.guides.index'), false)
            ->assertDontSee(route('consultancy.institutions.index'), false);

        $this->publishGuide('tuition-fees');
        $institution = Institution::query()->create(['name' => 'Listed', 'status' => Institution::STATUS_ACTIVE]);
        $institution->forceFill(['is_public' => true])->save();

        $this->get(route('sitemap'))->assertOk()
            ->assertSee(route('consultancy.guides.index'), false)
            ->assertSee(route('consultancy.institutions.index'), false);
    }
}
