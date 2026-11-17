<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\Country;
use App\Models\Institution;
use App\Models\StudentEnquiry;
use App\Services\SettingService;
use Database\Seeders\MalaysiaGuidesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class MalaysiaStudySectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_malaysia_page_follows_the_official_emgs_process_with_sources(): void
    {
        $html = (string) $this->get(route('consultancy.malaysia'))->assertOk()->getContent();

        $this->assertStringContainsString('<title>Study in Malaysia from Bangladesh | Eagle Global Hub LTD</title>', $html);
        foreach (['#courses', '#requirements', '#documents', '#student-pass', '#living-cost', '#apply'] as $tab) {
            $this->assertStringContainsString('href="'.$tab.'"', $html);
        }
        $this->assertSame(8, substr_count($html, '<li><span>Step '), 'the eight EMGS steps');
        foreach (['eVAL', 'Malaysia Digital Arrival Card (MDAC)', 'within 7 working days', 'valid for 6 months', '14 working days', 'Bangladeshi nationals can apply for an e-Visa'] as $fact) {
            $this->assertStringContainsString($fact, $html);
        }
        $this->assertStringContainsString('https://visa.educationmalaysia.gov.my/guidelines', $html);
        $this->assertStringContainsString('https://educationmalaysia.gov.my/get-in-touch/faq', $html);
        $this->assertStringContainsString('https://visa.educationmalaysia.gov.my/student-visa/calculate-application-fees', $html);
        $this->assertDoesNotMatchRegularExpression('/MYR [0-9]/', $html, 'no MYR figures until the owner enters them');
        $this->assertStringContainsString('name="destination" value="MY"', $html);
    }

    public function test_living_costs_and_intakes_are_shown_in_myr_only_once_entered(): void
    {
        app(SettingService::class)->set('facts', 'my_living_room', '600', 'string', true);
        app(SettingService::class)->set('facts', 'my_intakes', 'Jan · May · Sep', 'string', true);

        $this->get(route('consultancy.malaysia'))->assertSee('MYR 600')->assertSee('Intakes: Jan · May · Sep');
    }

    public function test_a_consultation_from_the_malaysia_page_is_saved_as_a_malaysia_lead(): void
    {
        Notification::fake();
        Country::query()->create(['name' => 'Malaysia', 'iso2' => 'MY', 'iso3' => 'MYS', 'phone_code' => '+60', 'is_active' => true]);

        $this->post(route('leads.consultation'), ['full_name' => 'Karim', 'phone' => '01712345678', 'consent' => '1', 'destination' => 'MY', 'form' => 'malaysia'])
            ->assertSessionHasNoErrors();

        $this->assertSame('MY', StudentEnquiry::query()->sole()->destinationCountry->iso2);
    }

    public function test_guides_and_institutions_stay_on_their_own_country_pages(): void
    {
        $this->seed(MalaysiaGuidesSeeder::class);
        $singaporeGuide = new ContentEntry(['slug' => 'ica-guide', 'title' => 'Singapore ICA guide', 'summary' => 'S', 'body' => 'B', 'source_urls' => ['https://www.ica.gov.sg']]);
        $singaporeGuide->type = ContentType::Guide;
        $singaporeGuide->status = ContentEntry::STATUS_PUBLISHED;
        $singaporeGuide->save();

        $this->get(route('consultancy.malaysia.guides.index'))->assertOk()->assertSee('Malaysia Student Pass: the EMGS process step by step')->assertDontSee('Singapore ICA guide');
        $this->get(route('consultancy.guides.index'))->assertOk()->assertSee('Singapore ICA guide')->assertDontSee('Malaysia Student Pass');
        $this->get(route('consultancy.malaysia.guides.show', 'malaysia-student-pass-documents'))->assertOk()->assertSee('35 mm × 45 mm');
        $this->get(route('consultancy.guides.show', 'malaysia-student-pass-documents'))->assertNotFound();
        $this->get(route('consultancy.malaysia.guides.show', 'ica-guide'))->assertNotFound();

        $malaysia = Country::query()->create(['name' => 'Malaysia', 'iso2' => 'MY', 'iso3' => 'MYS', 'phone_code' => '+60', 'is_active' => true]);
        $kl = Institution::query()->create(['name' => 'KL Test University', 'status' => Institution::STATUS_ACTIVE, 'country_id' => $malaysia->id]);
        $kl->forceFill(['is_public' => true, 'publicly_verified_at' => now()])->save();

        $this->get(route('consultancy.malaysia.institutions.index'))->assertOk()->assertSee('KL Test University');
        $this->get(route('consultancy.institutions.index'))->assertOk()->assertDontSee('KL Test University');
    }

    public function test_malaysia_guides_are_published_with_emgs_sources_once_and_never_recreated(): void
    {
        $this->seed(MalaysiaGuidesSeeder::class);

        $guides = ContentEntry::query()->where('location', 'Malaysia')->get();
        $this->assertCount(3, $guides);
        foreach ($guides as $guide) {
            $this->assertSame([], $guide->publishBlockers(), $guide->slug);
            foreach ($guide->source_urls as $url) {
                $this->assertMatchesRegularExpression('#^https://(visa\.)?educationmalaysia\.gov\.my/#', $url, 'only official EMGS sources');
            }
        }

        $guides->first()->delete();
        $this->seed(MalaysiaGuidesSeeder::class);
        $this->assertCount(2, ContentEntry::query()->where('location', 'Malaysia')->get());
    }
}
