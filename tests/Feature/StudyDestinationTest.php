<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\Country;
use App\Models\Course;
use App\Models\Institution;
use App\Models\MediaAsset;
use App\Models\StudentEnquiry;
use App\Models\StudyDestination;
use App\Models\StudyDestinationItem;
use Database\Seeders\CountrySeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Database\Seeders\StudyDestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudyDestinationTest extends TestCase
{
    use RefreshDatabase;

    private Country $japan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, ServiceDestinationSeeder::class]);
        $this->japan = Country::query()->where('iso2', 'JP')->firstOrFail();
    }

    private function japanPage(bool $complete = true): StudyDestination
    {
        $factory = StudyDestination::factory()->forCountry($this->japan);

        return ($complete ? $factory->publishedAndComplete() : $factory)->create(['name_bn' => 'জাপান']);
    }

    private function item(StudyDestination $destination, string $section, array $attributes = []): StudyDestinationItem
    {
        return StudyDestinationItem::factory()->for($destination, 'destination')->create(['section' => $section, ...$attributes]);
    }

    public function test_a_public_destination_shows_its_filled_sections_and_hides_empty_ones(): void
    {
        $destination = $this->japanPage();
        $destination->update(['currency_code' => 'JPY', 'bdt_rate' => 0.8, 'bdt_rate_checked_on' => '2026-11-01', 'visa_name' => 'College Student visa', 'intakes' => 'April · October']);
        $this->item($destination, 'why', ['title' => 'Safe cities', 'body' => 'Low crime.']);
        $this->item($destination, 'cost', ['title' => 'Tuition per year', 'amount' => '500,000–800,000', 'body' => null, 'source_url' => 'https://example.gov/fees', 'source_label' => 'Example fees']);
        $this->item($destination, 'faq', ['title' => 'Can I bring my family?', 'body' => 'Ask your consultant.']);

        $html = (string) $this->get(route('study-destinations.show', 'japan'))->assertOk()->getContent();

        $this->assertStringContainsString('Test introduction for this study destination.', $html);
        foreach (['why', 'requirements', 'visa', 'costs', 'faq', 'sources'] as $section) {
            $this->assertStringContainsString('data-section="'.$section.'"', $html, $section);
        }
        foreach (['work', 'documents', 'subjects', 'guides', 'institutions'] as $section) {
            $this->assertStringNotContainsString('data-section="'.$section.'"', $html, $section.' is empty, so hidden');
        }
        $this->assertStringContainsString('Source: Example Ministry', $html);
        $this->assertStringContainsString('JPY 500,000–800,000', $html);
        $this->assertStringContainsString('≈ BDT 400,000–640,000', $html);
        $this->assertStringContainsString('"@type":"FAQPage"', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
        $this->assertStringContainsString('hreflang="bn"', $html);
        $this->assertStringContainsString('data-footer-cta="study"', $html);
        $this->assertStringContainsString('Ready to study in Japan?', $html);
        $this->assertStringNotContainsString('Ready to study in Malaysia?', $html);
        $this->assertStringContainsString('name="destination" value="JP"', $html, 'the apply form is prefilled with the country');

        $this->get(route('study-destinations.show', ['japan', 'lang' => 'bn']))->assertOk()->assertSee('জাপান');
    }

    public function test_an_unpublished_or_incomplete_destination_is_not_found_and_the_menu_keeps_the_quote_form(): void
    {
        $this->seed(MenuSeeder::class);
        $destination = $this->japanPage(complete: false);
        $quoteForm = route('services.show', ['slug' => 'student-visa', 'country' => 'Japan']).'#quote-form';

        $this->get(route('study-destinations.show', 'japan'))->assertNotFound();
        $this->get(route('home'))->assertSee('href="'.e($quoteForm).'"', false);

        // Published, but the only source is a seeded suggestion that was never checked.
        $destination->update(['status' => StudyDestination::STATUS_PUBLISHED, 'intro' => 'Intro']);
        $this->item($destination, 'requirement', ['title' => 'Academic', 'source_url' => null]);
        $this->item($destination, 'visa_step', ['title' => 'Apply', 'source_url' => null]);
        $this->item($destination, 'source', ['title' => null, 'source_url' => 'https://www.isa.go.jp/en/', 'needs_verification' => true]);
        $this->get(route('study-destinations.show', 'japan'))->assertNotFound();
        $this->get(route('home'))->assertSee('href="'.e($quoteForm).'"', false)->assertDontSee('href="'.route('study-destinations.show', 'japan').'"', false);

        $destination->items()->where('section', 'source')->update(['needs_verification' => false]);
        $this->get(route('study-destinations.show', 'japan'))->assertOk();
        $this->get(route('home'))->assertSee('href="'.route('study-destinations.show', 'japan').'"', false);
    }

    public function test_a_hero_image_needs_alt_text_before_publishing(): void
    {
        $destination = $this->japanPage();
        $image = MediaAsset::query()->create(['collection' => 'general', 'path' => 'media/tokyo.webp', 'original_name' => 'tokyo.webp', 'mime_type' => 'image/webp', 'size_bytes' => 1000, 'alt_text' => 'Tokyo', 'is_published' => true]);
        $destination->update(['hero_media_id' => $image->id, 'hero_alt' => null]);

        $this->assertContains('Write the alt text (English) for the hero image.', $destination->refresh()->publishBlockers());
        $this->get(route('study-destinations.show', 'japan'))->assertNotFound();

        $destination->update(['hero_alt' => 'Students on a Tokyo campus']);
        $this->get(route('study-destinations.show', 'japan'))->assertOk()->assertSee('alt="Students on a Tokyo campus"', false);
    }

    public function test_the_apply_form_saves_a_student_enquiry_for_the_country(): void
    {
        Notification::fake();
        $lead = ['full_name' => 'Rahim Uddin', 'phone' => '01712345678', 'consent' => '1', 'form' => 'study-destination', 'destination' => 'JP', 'return_anchor' => 'apply'];

        $this->post(route('leads.consultation'), $lead)->assertSessionHasErrors('destination', null);
        $this->japanPage();

        $this->post(route('leads.consultation'), [...$lead, 'consent' => null])->assertSessionHasErrors('consent');
        $this->post(route('leads.consultation'), [...$lead, 'website' => 'https://spam.example'])->assertSessionHasErrors('website');
        $this->post(route('leads.consultation'), [...$lead, 'full_name' => ''])->assertSessionHasErrors('full_name');
        $this->post(route('leads.consultation'), $lead)->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame($this->japan->id, StudentEnquiry::query()->latest('id')->value('destination_country_id'));
        $this->assertSame(1, StudentEnquiry::query()->count());
        $this->post(route('leads.consultation'), $lead)->assertTooManyRequests();
    }

    public function test_an_old_student_visa_link_moves_permanently_only_when_the_page_is_public(): void
    {
        $destination = $this->japanPage(complete: false);
        $this->get(route('services.show', ['slug' => 'student-visa', 'country' => 'Japan']))->assertOk()->assertSee('id="quote-form"', false);

        $destination->delete();
        $this->japanPage();
        $this->get(route('services.show', ['slug' => 'student-visa', 'country' => 'Japan']))->assertStatus(301)->assertRedirect(route('study-destinations.show', 'japan'));
        $this->get(route('services.show', ['slug' => 'student-visa', 'country' => 'জাপান']))->assertStatus(301);
        $this->get(route('services.show', ['slug' => 'student-visa', 'country' => 'Bulgaria']))->assertOk();
        $this->get(route('services.show', ['slug' => 'student-visa', 'country' => 'Malaysia']))->assertOk();
    }

    public function test_visa_step_cards_wrap_long_text_instead_of_overflowing(): void
    {
        $css = (string) file_get_contents(public_path('css/egh-v2.css'));

        $this->assertStringContainsString('.v2-pass > li { min-width: 0; grid-template-columns: minmax(0, 1fr); }', $css);
        $this->assertStringContainsString('.v2-pass .v2-source { white-space: normal; overflow-wrap: anywhere; }', $css);
        $this->assertStringContainsString('@media (max-width: 1100px) { [data-section="visa"] .v2-pass { grid-template-columns: repeat(2, minmax(0, 1fr)); } }', $css);
    }

    public function test_sentences_use_the_name_in_sentences_and_fall_back_to_the_plain_name(): void
    {
        $uk = StudyDestination::factory()->forCountry(Country::query()->where('iso2', 'GB')->firstOrFail())->publishedAndComplete()
            ->create(['name_in_sentence' => 'the United Kingdom', 'name_bn' => 'যুক্তরাজ্য']);
        $this->item($uk, 'why', ['title' => 'Well-known universities', 'body' => 'Many programmes.']);

        $html = (string) $this->get(route('study-destinations.show', 'united-kingdom'))->assertOk()->getContent();
        foreach (['Why study in the United Kingdom', 'Plan your studies in the United Kingdom', '<title>Study in the United Kingdom from Bangladesh', 'Ready to study in the United Kingdom?', 'authorities of the United Kingdom'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }
        $this->assertStringNotContainsString('in United Kingdom', $html);

        $this->get(route('study-destinations.show', ['united-kingdom', 'lang' => 'bn']))->assertOk()->assertSee('যুক্তরাজ্য')->assertDontSee('the United Kingdom');

        $japan = $this->japanPage();
        $this->item($japan, 'why', ['title' => 'Safe cities']);
        $this->get(route('study-destinations.show', ['japan', 'lang' => 'en']))->assertSee('Why study in Japan')->assertSee('Plan your studies in Japan');
    }

    public function test_the_seeder_fills_the_name_in_sentences_only_while_it_is_empty(): void
    {
        $this->seed(StudyDestinationSeeder::class);
        $uk = StudyDestination::query()->where('slug', 'united-kingdom')->firstOrFail();
        $this->assertSame('the United Kingdom', $uk->name_in_sentence);
        $this->assertNull(StudyDestination::query()->where('slug', 'japan')->value('name_in_sentence'));

        $uk->update(['name_in_sentence' => 'the UK']);
        $this->seed(StudyDestinationSeeder::class);
        $this->assertSame('the UK', $uk->refresh()->name_in_sentence, 'an admin edit is kept');
    }

    public function test_the_visa_section_has_a_short_fixed_heading_with_the_visa_name_underneath(): void
    {
        $this->japanPage()->update(['visa_name' => 'Student visa or entry permit, then temporary residence permit']);

        $html = (string) $this->get(route('study-destinations.show', 'japan'))->assertOk()->getContent();
        $this->assertStringContainsString('<h2 id="visa-title" class="v2-h2">Student visa: step by step</h2>', $html);
        $this->assertStringContainsString('data-visa-name>Student visa or entry permit, then temporary residence permit</p>', $html);
        $this->assertStringNotContainsString('what happens', $html);

        $this->get(route('study-destinations.show', ['japan', 'lang' => 'bn']))->assertSee('স্টুডেন্ট ভিসা: ধাপে ধাপে');
    }

    public function test_the_sitemap_lists_only_public_destinations(): void
    {
        $this->japanPage();
        StudyDestination::factory()->forCountry(Country::query()->where('iso2', 'GB')->firstOrFail())->create();

        $this->get(route('sitemap'))->assertOk()
            ->assertSee('<loc>'.route('study-destinations.show', 'japan').'</loc>', false)
            ->assertDontSee(route('study-destinations.show', 'united-kingdom'), false);
    }

    public function test_publishing_a_destination_moves_only_its_own_items_away_from_singapore(): void
    {
        $singaporeId = (int) Country::query()->where('iso2', 'SG')->value('id');
        $sgInstitution = Institution::query()->create(['name' => 'Example Institute Singapore', 'country_id' => $singaporeId, 'status' => Institution::STATUS_ACTIVE]);
        $sgInstitution->forceFill(['is_public' => true])->save();
        $jpInstitution = Institution::query()->create(['name' => 'Example Institute Japan', 'country_id' => $this->japan->id, 'status' => Institution::STATUS_ACTIVE]);
        $jpInstitution->forceFill(['is_public' => true])->save();
        Course::query()->create(['institution_id' => $sgInstitution->id, 'name' => 'Diploma in Business Singapore', 'is_active' => true]);
        Course::query()->create(['institution_id' => $jpInstitution->id, 'name' => 'Japanese Language Course', 'is_active' => true]);
        $sgGuide = $this->guide('singapore-student-pass', ['country_id' => $singaporeId, 'location' => 'Singapore']);
        $unassignedGuide = $this->guide('general-advice');
        $jpGuide = $this->guide('japan-student-visa', ['country_id' => $this->japan->id]);

        // Before Japan is public, Singapore keeps every non-Malaysian item, exactly as today.
        $this->get(route('consultancy.institutions.index'))->assertSee('Example Institute Singapore')->assertSee('Example Institute Japan');
        $this->get(route('consultancy.guides.index'))->assertSee($jpGuide->title);

        $this->japanPage();

        $this->get(route('consultancy.institutions.index'))->assertOk()->assertSee('Example Institute Singapore')->assertDontSee('Example Institute Japan');
        $this->get(route('consultancy.guides.index'))->assertOk()->assertSee($sgGuide->title)->assertSee($unassignedGuide->title)->assertDontSee($jpGuide->title);
        $this->get(route('consultancy.singapore'))->assertOk()->assertSee('Diploma in Business Singapore')->assertDontSee('Japanese Language Course');
        $this->get(route('consultancy.guides.show', $sgGuide->slug))->assertOk();

        $this->get(route('study-destinations.institutions.index', 'japan'))->assertOk()->assertSee('Example Institute Japan')->assertDontSee('Example Institute Singapore');
        $this->get(route('study-destinations.guides.index', 'japan'))->assertOk()->assertSee($jpGuide->title)->assertDontSee($sgGuide->title);
        $this->get(route('study-destinations.guides.show', ['japan', $jpGuide->slug]))->assertOk();
        $this->assertSame(route('study-destinations.guides.show', ['japan', $jpGuide->slug]), $jpGuide->refresh()->publicUrl());
        $this->get(route('consultancy.guides.show', $jpGuide->slug))->assertNotFound();
    }

    public function test_guides_use_their_study_country_and_fall_back_to_an_exact_location_match(): void
    {
        $this->japanPage();
        $byCountry = $this->guide('by-country', ['country_id' => $this->japan->id, 'location' => 'Tokyo']);
        $byBengaliName = $this->guide('by-bengali-name', ['location' => 'জাপান']);
        $misspelt = $this->guide('misspelt', ['location' => 'Japn']);
        $malaysia = $this->guide('malaysia-guide', ['location' => 'Malaysia']);

        $this->assertStringContainsString('/study-in-japan/guides/', (string) $byCountry->publicUrl());
        $this->assertStringContainsString('/study-in-japan/guides/', (string) $byBengaliName->publicUrl());
        $this->assertStringContainsString('/study-in-singapore/guides/', (string) $misspelt->publicUrl(), 'no exact match: stays with Singapore');
        $this->assertStringContainsString('/study-in-malaysia/guides/', (string) $malaysia->publicUrl());

        $this->seed(StudyDestinationSeeder::class);
        $this->assertSame($this->japan->id, $byBengaliName->refresh()->country_id, 'an exact Bengali name is linked');
        $this->assertNull($misspelt->refresh()->country_id, 'a misspelt Location is not guessed');
        $this->assertSame((int) Country::query()->where('iso2', 'MY')->value('id'), $malaysia->refresh()->country_id);
    }

    public function test_the_seeder_creates_drafts_once_with_suggested_sources_only(): void
    {
        $this->seed(StudyDestinationSeeder::class);
        $this->seed(StudyDestinationSeeder::class);

        $this->assertSame(['bulgaria', 'cyprus', 'japan', 'south-korea', 'united-kingdom'], StudyDestination::query()->orderBy('slug')->pluck('slug')->all());
        $this->assertSame(0, StudyDestination::query()->published()->count());
        $this->assertSame(7, StudyDestinationItem::query()->count(), 'only the suggested sources, once');
        $this->assertSame(0, StudyDestinationItem::query()->where('section', '!=', 'source')->count(), 'no facts are invented');
        $this->assertSame(0, StudyDestinationItem::query()->where('needs_verification', false)->count());
        $this->assertSame('জাপান', StudyDestination::query()->where('slug', 'japan')->value('name_bn'));

        // A suggestion the owner removed is not added back, and an edited page is not touched.
        $japan = StudyDestination::query()->where('slug', 'japan')->firstOrFail();
        $japan->items()->delete();
        $japan->update(['intro' => 'Owner text', 'name_bn' => 'জাপান (সম্পাদিত)']);
        $this->seed(StudyDestinationSeeder::class);
        $this->assertSame(0, $japan->items()->count());
        $this->assertSame('Owner text', $japan->refresh()->intro);
        $this->assertSame('জাপান (সম্পাদিত)', $japan->name_bn);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function guide(string $slug, array $attributes = []): ContentEntry
    {
        $guide = new ContentEntry(['slug' => $slug, 'title' => 'Guide '.$slug, 'summary' => 'Summary', 'body' => 'Body', ...$attributes]);
        $guide->type = ContentType::Guide;
        $guide->status = ContentEntry::STATUS_PUBLISHED;
        $guide->save();

        return $guide;
    }
}
