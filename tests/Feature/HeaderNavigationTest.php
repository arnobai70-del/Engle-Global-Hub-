<?php

namespace Tests\Feature;

use App\Enums\BusinessService;
use App\Models\Country;
use App\Models\MenuItem;
use App\Models\ServiceDestination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HeaderNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_like_menu_with_eight_items_and_three_buttons_renders_one_red_cta_an_icon_login_and_the_mega_menu(): void
    {
        $this->seedLiveLikeMenu();

        $html = (string) $this->get(route('home'))->assertOk()->getContent();
        $header = (string) strstr((string) strstr($html, '<header class="sg-site-header">'), '</header>', true);

        foreach (['Home', 'Study in Malaysia', 'Study in Singapore', 'Visa Services', 'Travel Services', 'How it works', 'About', 'Contact'] as $label) {
            $this->assertStringContainsString($label, $header, $label.' is missing from the header');
        }
        $this->assertSame(1, substr_count($header, 'sg-btn-red'), 'the header keeps exactly one red call-to-action');
        $this->assertStringContainsString('Free Consultation', $header);
        $this->assertStringNotContainsString('Book a call', $header, 'extra admin buttons stay hidden');
        $this->assertStringContainsString('class="sg-icon-btn sg-login-btn" aria-label="Login"', $header);
        $this->assertSame(1, substr_count($header, 'sg-dd-mega'), 'Study in Malaysia is the mega-menu');
        $this->assertStringContainsString('<details class="sg-dd sg-dd-more" data-sg-more hidden>', $header);
    }

    public function test_menu_fitting_moves_extra_items_into_more_and_keeps_the_burger_for_small_screens(): void
    {
        $script = (string) file_get_contents(public_path('js/egh-sg-nav.js'));
        $siteCss = (string) file_get_contents(public_path('css/egh-sg.css'));

        $this->assertStringContainsString("header.classList.add('sg-nav-tight');", $script);
        $this->assertStringContainsString("visible[index].classList.add('sg-overflowed');", $script, 'items that do not fit go into More instead of hiding the whole menu');
        $this->assertStringContainsString("document.fonts.addEventListener('loadingdone', schedule);", $script, 'the row is re-fitted after Bengali fonts load');
        $this->assertStringContainsString('@media (max-width: 1179px) {', $siteCss);
    }

    public function test_header_has_one_student_visa_item_listing_every_study_destination(): void
    {
        $destinations = $this->seedStudyDestinations(['MY' => 'Malaysia', 'SG' => 'Singapore', 'JP' => 'Japan']);

        $html = (string) $this->get(route('home'))->assertOk()->getContent();
        $header = (string) strstr((string) strstr($html, '<header class="sg-site-header">'), '</header>', true);
        $mega = (string) strstr((string) strstr($header, '<details class="sg-dd sg-dd-mega"'), '</details>', true);

        $this->assertStringContainsString('<summary>Student Visa</summary>', $mega);
        $this->assertStringNotContainsString('<summary>Study in Malaysia</summary>', $header);
        $this->assertStringNotContainsString('<summary>Study in Singapore</summary>', $header);
        foreach ([route('consultancy.malaysia') => 'Malaysia', route('consultancy.singapore') => 'Singapore', route('services.show', ['slug' => 'student-visa', 'country' => 'Japan']).'#quote-form' => 'Japan'] as $url => $country) {
            $this->assertMatchesRegularExpression('#href="'.preg_quote($url, '#').'"\s*>'.$country.'</a>#', $mega, $country.' is missing from the Student Visa menu');
        }

        $destinations['JP']->update(['is_active' => false]);
        $this->assertStringNotContainsString('>Japan</a>', (string) $this->get(route('home'))->getContent(), 'switched-off destinations leave the menu');
    }

    public function test_country_link_preselects_the_country_in_the_student_visa_enquiry(): void
    {
        $this->seedStudyDestinations(['MY' => 'Malaysia', 'JP' => 'Japan']);

        $this->get(route('services.show', ['slug' => 'student-visa', 'country' => 'Japan']))->assertOk()
            ->assertSee('<option value="Japan" selected>Japan</option>', false);
    }

    public function test_migration_turns_the_live_study_menus_into_one_student_visa_item(): void
    {
        $item = static fn (array $attributes): MenuItem => MenuItem::query()->create(array_merge(['location' => 'header', 'visibility' => 'all', 'is_active' => true], $attributes));
        $malaysia = $item(['key' => 'study-malaysia', 'label_en' => 'Study in Malaysia', 'sort_order' => 1]);
        $item(['parent_id' => $malaysia->id, 'label_en' => 'Malaysia Guides', 'target' => 'route:consultancy.malaysia.guides.index', 'sort_order' => 1]);
        $singapore = $item(['key' => 'study', 'label_en' => 'Study in Singapore', 'sort_order' => 2]);

        $migration = require collect(glob(database_path('migrations/*merge_study_menus_into_student_visa.php')))->sole();
        $migration->up();

        $this->assertSame('Student Visa', $malaysia->fresh()->label_en);
        $this->assertSame('স্টুডেন্ট ভিসা', $malaysia->fresh()->label_bn);
        $this->assertSame('route:services.show|student-visa', $malaysia->fresh()->target);
        $this->assertFalse($singapore->fresh()->is_active, 'Singapore item is hidden, not deleted');

        $this->get(route('home'))->assertOk()->assertSee('<summary>Student Visa</summary>', false)->assertDontSee('<summary>Study in Singapore</summary>', false);
    }

    /**
     * @param  array<string, string>  $countries  ISO2 => name, in display order
     * @return array<string, ServiceDestination>
     */
    private function seedStudyDestinations(array $countries): array
    {
        $destinations = [];
        foreach (array_keys($countries) as $position => $iso2) {
            $country = Country::query()->create(['name' => $countries[$iso2], 'iso2' => $iso2, 'iso3' => $iso2.'X', 'is_active' => true]);
            $destinations[$iso2] = ServiceDestination::query()->create(['service' => BusinessService::StudentVisa->value, 'country_id' => $country->id, 'is_active' => true, 'sort_order' => $position]);
        }

        return $destinations;
    }

    private function seedLiveLikeMenu(): void
    {
        $item = static fn (array $attributes): MenuItem => MenuItem::query()->create(array_merge(['location' => 'header', 'visibility' => 'all', 'is_active' => true], $attributes));

        $item(['label_en' => 'Home', 'target' => 'route:home', 'sort_order' => 1]);
        $malaysia = $item(['key' => 'study-malaysia', 'label_en' => 'Study in Malaysia', 'sort_order' => 2]);
        $item(['parent_id' => $malaysia->id, 'label_en' => 'Malaysia overview', 'target' => 'route:consultancy.malaysia', 'sort_order' => 1]);
        $singapore = $item(['label_en' => 'Study in Singapore', 'sort_order' => 3]);
        $item(['parent_id' => $singapore->id, 'label_en' => 'Singapore overview', 'target' => 'route:consultancy.singapore', 'sort_order' => 1]);
        $visa = $item(['label_en' => 'Visa Services', 'sort_order' => 4]);
        $item(['parent_id' => $visa->id, 'label_en' => 'Tourist Visa', 'target' => 'route:services.show|tourist-visa', 'sort_order' => 1]);
        $travel = $item(['label_en' => 'Travel Services', 'sort_order' => 5]);
        $item(['parent_id' => $travel->id, 'label_en' => 'Air Tickets', 'target' => 'route:services.show|air-ticket', 'sort_order' => 1]);
        $item(['label_en' => 'How it works', 'target' => 'route:home', 'fragment' => 'how-it-works', 'sort_order' => 6]);
        $about = $item(['label_en' => 'About', 'sort_order' => 7]);
        $item(['parent_id' => $about->id, 'label_en' => 'About Us', 'target' => 'route:about', 'sort_order' => 1]);
        $item(['label_en' => 'Contact', 'target' => 'route:support', 'sort_order' => 8]);

        $item(['location' => 'header_cta', 'label_en' => 'Free Consultation', 'target' => 'route:consultancy.malaysia', 'sort_order' => 1]);
        $item(['location' => 'header_cta', 'label_en' => 'Book a call', 'target' => 'route:support', 'sort_order' => 2]);
    }
}
