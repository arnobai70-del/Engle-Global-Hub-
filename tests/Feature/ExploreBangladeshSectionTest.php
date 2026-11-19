<?php

namespace Tests\Feature;

use App\Enums\BusinessService;
use App\Models\Country;
use App\Models\Destination;
use App\Models\DestinationListing;
use App\Models\MenuItem;
use App\Models\ServiceEnquiry;
use App\Models\User;
use Database\Seeders\ExploreBangladeshMenuSeeder;
use Database\Seeders\ProductionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class ExploreBangladeshSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_published_bangladesh_destinations_with_featured_first(): void
    {
        Destination::factory()->published()->create(['name' => 'Plain Place', 'slug' => 'plain-place', 'sort_order' => 1]);
        Destination::factory()->published()->create(['name' => 'Featured Place', 'slug' => 'featured-place', 'sort_order' => 9, 'is_featured' => true]);
        Destination::factory()->create(['name' => 'Draft Place', 'slug' => 'draft-place']);
        $thailand = Country::query()->create(['name' => 'Thailand', 'iso2' => 'TH', 'iso3' => 'THA', 'is_active' => true]);
        Destination::factory()->published()->create(['name' => 'Foreign Place', 'slug' => 'foreign-place', 'country_id' => $thailand->id]);

        $this->assertSame(['Featured Place', 'Plain Place'], $this->cards(route('explore-bangladesh.index')));
    }

    public function test_category_filter_works_and_an_unknown_category_shows_everything(): void
    {
        Destination::factory()->published()->category('beach')->create(['name' => 'Sea Place', 'slug' => 'sea-place']);
        Destination::factory()->published()->category('historical')->create(['name' => 'Old Place', 'slug' => 'old-place']);

        $this->assertSame(['Sea Place'], $this->cards(route('explore-bangladesh.index', ['category' => 'beach'])));
        $this->assertSame(['Old Place', 'Sea Place'], $this->cards(route('explore-bangladesh.index', ['category' => 'volcano'])));
        $this->get(route('explore-bangladesh.index', ['category' => 'beach']))->assertSee('<meta name="robots" content="noindex,follow">', false);
    }

    public function test_index_always_has_essentials_plan_my_trip_and_a_travel_cta(): void
    {
        $html = (string) $this->get(route('explore-bangladesh.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Our destination guides are being prepared', $html, 'friendly empty state');
        $this->assertStringContainsString('id="essentials"', $html);
        $this->assertStringContainsString('999', $html);
        $this->assertStringContainsString('Bangladeshi Taka (BDT)', $html);
        $this->assertStringContainsString('id="plan-trip"', $html);
        $this->assertStringContainsString('action="'.route('explore-bangladesh.enquire').'"', $html);
        $this->assertStringContainsString('Planning a trip to Bangladesh?', $html);
        $this->assertStringNotContainsString('Ready to study in Malaysia?', $html, 'the study CTA stays on study pages only');
        $this->assertStringNotContainsString('images/malaysia/', (string) strstr($html, '<main>'), 'no Malaysia photo on Explore pages');
        $this->assertSame(1, substr_count($html, '<h1'));

        $this->assertStringContainsString('Ready to study in Malaysia?', (string) $this->get(route('services.show', 'student-visa'))->getContent());
    }

    public function test_show_page_404s_for_drafts_and_other_countries(): void
    {
        Destination::factory()->create(['slug' => 'draft-place']);
        $thailand = Country::query()->create(['name' => 'Thailand', 'iso2' => 'TH', 'iso3' => 'THA', 'is_active' => true]);
        Destination::factory()->published()->create(['slug' => 'foreign-place', 'country_id' => $thailand->id]);

        $this->get(route('explore-bangladesh.show', 'draft-place'))->assertNotFound();
        $this->get(route('explore-bangladesh.show', 'foreign-place'))->assertNotFound();
        $this->get(route('explore-bangladesh.show', 'no-such-place'))->assertNotFound();
    }

    public function test_show_page_lists_only_published_listings_and_hides_empty_blocks(): void
    {
        $place = Destination::factory()->published()->category('beach')->create(['name' => 'Sea Place', 'slug' => 'sea-place', 'best_time_to_visit' => 'November to February']);
        DestinationListing::factory()->published()->for($place)->create(['type' => 'hotel', 'name' => 'Verified Sea Hotel', 'website_url' => 'https://hotel.example.com']);
        DestinationListing::factory()->for($place)->create(['type' => 'hotel', 'name' => 'Unchecked Hotel']);
        DestinationListing::factory()->for($place)->create(['type' => 'restaurant', 'name' => 'Draft Restaurant']);

        $html = (string) $this->get(route('explore-bangladesh.show', 'sea-place'))->assertOk()->getContent();

        $this->assertStringContainsString('Verified Sea Hotel', $html);
        $this->assertStringContainsString('href="https://hotel.example.com" target="_blank" rel="noopener nofollow"', $html);
        $this->assertStringNotContainsString('Unchecked Hotel', $html);
        $this->assertStringContainsString('Where to stay', $html);
        $this->assertStringNotContainsString('Where to eat', $html, 'a block without published listings is hidden');
        $this->assertStringNotContainsString('Things to do', $html);
        $this->assertStringContainsString('November to February', $html);
        $this->assertStringNotContainsString('<iframe', $html, 'no map without coordinates');
        $this->assertStringContainsString('"@type":"TouristAttraction"', $html);
        $this->assertStringContainsString('value="'.$place->id.'" checked', $html, 'the trip form is prefilled with this place');

        $place->update(['latitude' => 21.4272, 'longitude' => 92.0058]);
        $this->assertStringContainsString('loading="lazy"', (string) strstr((string) $this->get(route('explore-bangladesh.show', 'sea-place'))->getContent(), '<iframe'));
    }

    public function test_trip_enquiry_is_saved_with_destinations_for_staff(): void
    {
        Notification::fake();
        $place = Destination::factory()->published()->create(['name' => 'Sea Place', 'slug' => 'sea-place']);
        $uk = Country::query()->create(['name' => 'United Kingdom', 'iso2' => 'GB', 'iso3' => 'GBR', 'is_active' => true]);

        $this->from(route('explore-bangladesh.index'))->post(route('explore-bangladesh.enquire'), $this->trip([
            'destination_ids' => [$place->id], 'needs' => ['hotel', 'car_with_driver'], 'residence_country_id' => $uk->id,
            'travel_date' => now()->addMonth()->toDateString(), 'travellers' => 2,
        ]))->assertRedirect(route('explore-bangladesh.index').'#plan-trip')->assertSessionHas('lead_reference');

        $enquiry = ServiceEnquiry::query()->with('destinations')->sole();
        $this->assertSame(BusinessService::ForeignerSupport, $enquiry->service);
        $this->assertSame('travel_assistance', $enquiry->category);
        $this->assertSame('+44 7700 900123', $enquiry->phone);
        $this->assertSame(['Sea Place'], $enquiry->destinations->pluck('name')->all());
        $this->assertStringContainsString('Country of residence: United Kingdom', (string) $enquiry->message);
        $this->assertStringContainsString('Needs: Hotel, Car with driver', (string) $enquiry->message);

        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin)->get(route('admin.service-enquiries.show', $enquiry))->assertOk()->assertSee('Places to visit')->assertSee('Sea Place');
    }

    public function test_trip_enquiry_validates_consent_honeypot_and_draft_places(): void
    {
        $draft = Destination::factory()->create(['slug' => 'draft-place']);

        $this->post(route('explore-bangladesh.enquire'), ['travellers' => 1])->assertSessionHasErrors(['full_name', 'email', 'phone', 'consent']);
        $this->post(route('explore-bangladesh.enquire'), $this->trip(['website' => 'spam']))->assertSessionHasErrors('website');
        $this->post(route('explore-bangladesh.enquire'), $this->trip(['destination_ids' => [$draft->id]]))->assertSessionHasErrors('destination_ids.0');
        $this->post(route('explore-bangladesh.enquire'), $this->trip(['phone' => 'call me']))->assertSessionHasErrors('phone');

        $this->assertSame(0, ServiceEnquiry::query()->count());
    }

    public function test_trip_enquiry_is_throttled(): void
    {
        Notification::fake();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('explore-bangladesh.enquire'), $this->trip())->assertRedirect();
        }

        $this->post(route('explore-bangladesh.enquire'), $this->trip())->assertTooManyRequests();
    }

    public function test_authorised_staff_manage_destinations_and_listings(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('content-editor');
        $bangladesh = Country::query()->where('iso2', 'BD')->value('id') ?? Country::query()->create(['name' => 'Bangladesh', 'iso2' => 'BD', 'iso3' => 'BGD', 'is_active' => true])->id;

        $this->actingAs($editor)->post(route('admin.destinations.store'), [
            'country_id' => $bangladesh, 'name' => 'Ratargul', 'slug' => 'ratargul', 'region' => 'Sylhet', 'category' => 'nature',
            'summary' => 'Swamp forest.', 'best_time_to_visit' => 'Monsoon', 'latitude' => '25.0', 'longitude' => '91.9', 'sort_order' => 1, 'is_featured' => '1',
        ])->assertSessionHasNoErrors();
        $place = Destination::query()->where('slug', 'ratargul')->sole();
        $this->assertSame('nature', $place->category);
        $this->assertTrue($place->is_featured);
        $this->assertSame(25.0, $place->latitude);

        $this->actingAs($editor)->patch(route('admin.destinations.update', $place), ['country_id' => $bangladesh, 'name' => 'Ratargul', 'slug' => 'ratargul', 'category' => 'volcano', 'sort_order' => 1])
            ->assertSessionHasErrors('category');

        $this->actingAs($editor)->post(route('admin.destinations.listings.store', $place), ['type' => 'hotel', 'name' => 'Swamp Lodge', 'website_url' => 'http://insecure.example.com', 'sort_order' => 0])
            ->assertSessionHasErrors('website_url');
        $this->actingAs($editor)->post(route('admin.destinations.listings.store', $place), ['type' => 'hotel', 'name' => 'Swamp Lodge', 'website_url' => 'https://lodge.example.com', 'sort_order' => 0, 'is_published' => '1'])
            ->assertSessionHasNoErrors();
        $listing = DestinationListing::query()->sole();
        $this->assertTrue($listing->is_published);

        $this->actingAs($editor)->get(route('admin.destinations.edit', $place))->assertOk()->assertSee('Swamp Lodge');
        $this->actingAs($editor)->patch(route('admin.destinations.listings.update', [$place, $listing]), ['type' => 'experience', 'name' => 'Boat ride', 'sort_order' => 2])->assertSessionHasNoErrors();
        $this->assertSame('experience', $listing->fresh()->type);
        $this->assertFalse($listing->fresh()->is_published);

        $other = Destination::factory()->create();
        $this->actingAs($editor)->get(route('admin.destinations.listings.edit', [$other, $listing]))->assertNotFound();
        $this->actingAs($editor)->delete(route('admin.destinations.listings.destroy', [$place, $listing]))->assertRedirect();
        $this->assertSame(0, DestinationListing::query()->count());
        $this->actingAs($editor)->delete(route('admin.destinations.destroy', $place))->assertRedirect();
        $this->assertNull(Destination::query()->find($place->id));
    }

    public function test_guests_are_sent_to_login_and_staff_without_permission_get_403(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $place = Destination::factory()->create();
        $officer = User::factory()->create();
        $officer->assignRole('visa-officer');

        $this->get(route('admin.destinations.edit', $place))->assertRedirect(route('login'));
        $this->post(route('admin.destinations.listings.store', $place), ['type' => 'hotel', 'name' => 'X', 'sort_order' => 0])->assertRedirect(route('login'));
        $this->actingAs($officer)->get(route('admin.destinations.edit', $place))->assertForbidden();
        $this->actingAs($officer)->post(route('admin.destinations.listings.store', $place), ['type' => 'hotel', 'name' => 'X', 'sort_order' => 0])->assertForbidden();
    }

    public function test_production_seeder_twice_creates_no_duplicates_and_publishes_nothing(): void
    {
        $this->seed(ProductionSeeder::class);
        $counts = [Destination::query()->count(), MenuItem::query()->count()];
        $this->seed(ProductionSeeder::class);

        $this->assertSame($counts, [Destination::query()->count(), MenuItem::query()->count()]);
        $this->assertGreaterThanOrEqual(14, $counts[0]);
        $this->assertSame(0, Destination::query()->where('is_published', true)->count());
        $this->assertSame(0, DestinationListing::query()->count(), 'no hotels or restaurants are seeded');
        $this->assertSame(1, MenuItem::query()->where('key', ExploreBangladeshMenuSeeder::KEY)->count());
        $this->assertSame("Saint Martin's Island", Destination::query()->where('slug', 'saint-martins-island')->value('name'));
        $this->assertStringContainsString('restricted by the government', (string) Destination::query()->where('slug', 'saint-martins-island')->value('travel_tips'));
    }

    public function test_seeder_keeps_admin_edits_to_destinations_and_the_menu_item(): void
    {
        $this->seed(ProductionSeeder::class);
        Destination::query()->where('slug', 'coxs-bazar')->update(['name' => 'Admin Name', 'summary' => 'Admin summary.', 'is_published' => true]);
        MenuItem::query()->where('key', ExploreBangladeshMenuSeeder::KEY)->update(['label_en' => 'Visit Bangladesh', 'is_active' => false]);
        MenuItem::query()->where('key', ExploreBangladeshMenuSeeder::KEY.'-beach')->delete();

        $this->seed(ProductionSeeder::class);

        $coxs = Destination::query()->where('slug', 'coxs-bazar')->sole();
        $this->assertSame(['Admin Name', 'Admin summary.', true], [$coxs->name, $coxs->summary, $coxs->is_published]);
        $this->assertSame('Visit Bangladesh', MenuItem::query()->where('key', ExploreBangladeshMenuSeeder::KEY)->value('label_en'));
        $this->assertFalse(MenuItem::query()->where('key', ExploreBangladeshMenuSeeder::KEY.'-beach')->exists(), 'a deleted sub-item is not brought back');
    }

    public function test_header_shows_the_explore_bangladesh_menu_in_english_and_bengali(): void
    {
        $this->seed(ProductionSeeder::class);

        $english = (string) $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('<summary>Explore Bangladesh</summary>', $english);
        $this->assertStringContainsString('href="'.route('explore-bangladesh.index', ['category' => 'historical']).'"', $english);
        $this->assertStringContainsString('href="'.route('explore-bangladesh.index').'#plan-trip"', $english);
        $this->assertLessThan(strpos($english, '<summary>Explore Bangladesh</summary>'), strpos($english, '<summary>Visa &amp; Travel</summary>'), 'placed after the travel item');

        $this->get(route('home', ['lang' => 'bn']))->assertOk()->assertSee('<summary>বাংলাদেশ ঘুরে দেখুন</summary>', false)->assertSee('ঐতিহাসিক ও ঐতিহ্য');
    }

    public function test_sitemap_lists_published_destinations_only(): void
    {
        Destination::factory()->published()->create(['slug' => 'public-place']);
        Destination::factory()->create(['slug' => 'draft-place']);

        $this->get(route('sitemap'))->assertOk()
            ->assertSee(route('explore-bangladesh.show', 'public-place'), false)
            ->assertDontSee(route('explore-bangladesh.show', 'draft-place'), false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function trip(array $overrides = []): array
    {
        return array_merge(['full_name' => 'Emma Clarke', 'email' => 'emma@example.com', 'phone' => '+44 7700 900123', 'travellers' => 1, 'consent' => '1'], $overrides);
    }

    /**
     * @return list<string>
     */
    private function cards(string $url): array
    {
        preg_match_all('#<article class="v2-explore-card">.*?<h3><a [^>]*>(.*?)</a></h3>#s', (string) $this->get($url)->assertOk()->getContent(), $matches);

        return array_map(fn (string $name): string => html_entity_decode($name, ENT_QUOTES), $matches[1]);
    }
}
