<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Country;
use App\Enums\BusinessService;
use App\Models\ServiceDestination;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomepageServiceCardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cards_with_the_same_link_or_title_are_shown_once(): void
    {
        $this->tile(['label_en' => 'Air tickets', 'target' => 'route:services.show|air-ticket', 'description_en' => 'Student fares from Dhaka.', 'sort_order' => 1]);
        $this->tile(['label_en' => 'Cheap flights', 'target' => 'url:'.route('services.show', 'air-ticket'), 'sort_order' => 2]);
        $this->tile(['label_en' => 'Hotels', 'target' => 'route:hotels.index', 'description_en' => 'Rooms near campus.', 'sort_order' => 3]);
        $this->tile(['label_en' => '  hotels ', 'target' => 'route:support', 'sort_order' => 4]);

        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'class="v2-service-card"'));
        $this->assertStringContainsString('Student fares from Dhaka.', $html);
        $this->assertStringContainsString('Rooms near campus.', $html);
        $this->assertStringNotContainsString('Cheap flights', $html);
    }

    public function test_tile_description_is_editable_in_both_languages(): void
    {
        $this->tile(['label_en' => 'Air tickets', 'label_bn' => 'বিমান টিকিট', 'target' => 'route:services.show|air-ticket', 'description_en' => 'Student fares from Dhaka.', 'description_bn' => 'ঢাকা থেকে ছাত্র ভাড়া।']);

        $this->get(route('home', ['lang' => 'bn']))->assertOk()->assertSee('ঢাকা থেকে ছাত্র ভাড়া।');
    }

    public function test_admin_sees_warnings_for_missing_descriptions_and_duplicates(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->tile(['label_en' => 'Air tickets', 'target' => 'route:services.show|air-ticket', 'description_en' => 'Student fares.', 'sort_order' => 1]);
        $this->tile(['label_en' => 'Flights again', 'target' => 'route:services.show|air-ticket', 'description_en' => 'Same page.', 'sort_order' => 2]);
        $this->tile(['label_en' => 'Work visa', 'target' => 'route:work-visa.index', 'sort_order' => 3]);

        $this->actingAs($admin)->get(route('admin.menus.index', ['location' => 'home_tiles']))->assertOk()
            ->assertSee('Homepage cards need attention')
            ->assertSee('Work visa: No description: the card shows only its title.', false)
            ->assertSee('Flights again: Same link as &quot;Air tickets&quot;: only the first card is shown.', false);

        $this->actingAs($admin)->get(route('admin.menus.index', ['location' => 'header']))->assertOk()->assertDontSee('Homepage cards need attention');
    }

    public function test_admin_can_save_a_tile_description(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->post(route('admin.menus.store'), [
            'location' => 'home_tiles', 'label_en' => 'Work visa', 'link_type' => 'route', 'route_target' => 'route:work-visa.index',
            'icon' => MenuItem::ICONS[0], 'visibility' => 'all', 'is_active' => '1', 'sort_order' => 10,
            'description_en' => 'Work permits and job offers checked.', 'description_bn' => 'ওয়ার্ক পারমিট যাচাই।',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('menu_items', ['label_en' => 'Work visa', 'description_en' => 'Work permits and job offers checked.', 'description_bn' => 'ওয়ার্ক পারমিট যাচাই।']);
    }

    public function test_first_release_tiles_have_descriptions_and_no_admin_warning(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->tile(['key' => 'tile-study', 'label_en' => 'Study in Singapore', 'target' => 'route:consultancy.singapore', 'sort_order' => 1]);
        $this->tile(['key' => 'tile-hotels', 'label_en' => 'Hotels & Homes', 'target' => 'route:hotels.index', 'sort_order' => 2]);
        $this->tile(['label_en' => 'Work visa', 'target' => 'route:work-visa.index', 'sort_order' => 3]);

        $html = (string) $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('Diplomas and degrees in Singapore, with Student&#039;s Pass (ICA) guidance.', $html);
        $this->assertStringContainsString('Hotels, student hostels and shared rooms, confirmed with you before booking.', $html);

        $this->actingAs($admin)->get(route('admin.menus.index', ['location' => 'home_tiles']))->assertOk()
            ->assertDontSee('Study in Singapore: No description')
            ->assertDontSee('Hotels &amp; Homes: No description', false)
            ->assertSee('Work visa: No description');
    }

    public function test_singapore_seeded_home_card_is_hidden_but_its_page_remains(): void
    {
        $this->seed(MenuSeeder::class);
        $this->assertDatabaseHas('menu_items', ['location' => 'home_tiles', 'key' => 'tile-singapore', 'is_active' => false]);
        $this->get(route('home'))->assertOk()->assertDontSee('Study in Singapore</h3>', false);
        $this->get(route('consultancy.singapore'))->assertOk();
    }

    public function test_japan_home_card_can_be_enabled_then_hidden_in_admin(): void
    {
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, ServiceDestinationSeeder::class, MenuSeeder::class]);
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        $japan = ServiceDestination::query()->where('service', BusinessService::StudentVisa->value)
            ->whereHas('country', fn ($query) => $query->where('iso2', 'JP'))->sole();

        $this->assertFalse($japan->show_on_homepage);
        $this->get(route('home'))->assertDontSee('Study options: Japan');

        $this->actingAs($admin)->patch(route('admin.service-destinations.homepage', $japan), [
            'show_on_homepage' => 1,
        ])->assertRedirect();
        $this->assertDatabaseHas('study_destinations', ['country_id' => $japan->country_id, 'status' => 'draft']);
        $url = route('services.show', ['slug' => 'student-visa', 'country' => 'Japan']).'#quote-form';
        $this->get(route('home'))->assertOk()->assertSee('Study options: Japan')->assertSee($url);
        $this->get(route('home', ['lang' => 'bn']))->assertSee('পড়াশোনার সুযোগ: জাপান');

        $this->actingAs($admin)->patch(route('admin.service-destinations.homepage', $japan), [
            'show_on_homepage' => 0,
        ])->assertRedirect();
        $this->get(route('home'))->assertDontSee('Study options: Japan');
    }

    public function test_new_study_country_gets_draft_template_and_one_home_card(): void
    {
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, ServiceDestinationSeeder::class, MenuSeeder::class]);
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        $country = Country::query()->where('iso2', 'NP')->sole();

        $this->actingAs($admin)->post(route('admin.service-destinations.store'), [
            'service' => 'student_visa', 'country_id' => $country->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('service_destinations', [
            'service' => 'student_visa', 'country_id' => $country->id, 'show_on_homepage' => true,
        ]);
        $this->assertDatabaseHas('study_destinations', ['country_id' => $country->id, 'status' => 'draft']);

        $this->actingAs($admin)->post(route('admin.service-destinations.store'), [
            'service' => 'student_visa', 'country_id' => $country->id,
        ])->assertSessionHasErrors('country_id');
        $this->assertSame(1, ServiceDestination::query()
            ->where('service', 'student_visa')->where('country_id', $country->id)->count());

        $html = (string) $this->get(route('home'))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'Study options: Nepal'));
        $this->assertStringContainsString('country=Nepal', $html);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function tile(array $attributes): MenuItem
    {
        return MenuItem::query()->create(array_merge(['location' => 'home_tiles', 'icon' => 'globe', 'visibility' => 'all', 'is_active' => true, 'sort_order' => 10], $attributes));
    }
}
