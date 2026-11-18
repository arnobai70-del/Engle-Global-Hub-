<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function tile(array $attributes): MenuItem
    {
        return MenuItem::query()->create(array_merge(['location' => 'home_tiles', 'icon' => 'globe', 'visibility' => 'all', 'is_active' => true, 'sort_order' => 10], $attributes));
    }
}
