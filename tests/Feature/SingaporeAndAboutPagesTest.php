<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Institution;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SingaporeAndAboutPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_singapore_page_has_tabs_requirements_checklist_pass_steps_and_apply_form(): void
    {
        $html = (string) $this->get(route('consultancy.singapore'))->assertOk()->getContent();

        foreach (['#courses', '#requirements', '#documents', '#students-pass', '#living-cost', '#apply'] as $tab) {
            $this->assertStringContainsString('href="'.$tab.'"', $html);
        }
        $this->assertStringContainsString('Entry requirements', $html);
        $this->assertStringContainsString('data-print-checklist', $html);
        $this->assertSame(4, substr_count($html, '<li><span>Step '));
        $this->assertStringContainsString('(ICA) decides every Student&#039;s Pass', $html);
        $this->assertStringContainsString('action="'.route('leads.consultation').'"', $html);
        $this->assertStringContainsString('name="highest_qualification"', $html);
        $this->assertStringContainsString('Get my course shortlist', $html, 'without listed courses the page offers a shortlist instead');
        $this->assertStringNotContainsString('SGD [X]', $html);
    }

    public function test_course_finder_lists_only_courses_of_publicly_listed_institutions(): void
    {
        $listed = Institution::query()->create(['name' => 'Listed Institute', 'status' => Institution::STATUS_ACTIVE]);
        $listed->forceFill(['is_public' => true, 'publicly_verified_at' => now()])->save();
        $hidden = Institution::query()->create(['name' => 'Unlisted Institute', 'status' => Institution::STATUS_ACTIVE]);
        Course::query()->create(['institution_id' => $listed->id, 'name' => 'Diploma in Business', 'study_level' => 'diploma', 'duration_months' => 16, 'tuition_note' => 'From SGD 18,000 / year', 'is_active' => true]);
        Course::query()->create(['institution_id' => $hidden->id, 'name' => 'Hidden Course', 'study_level' => 'master', 'is_active' => true]);

        $this->get(route('consultancy.singapore'))->assertOk()
            ->assertSee('Diploma in Business')->assertSee('Listed Institute')->assertSee('From SGD 18,000 / year')
            ->assertSee('data-course-filter', false)->assertSee('data-course-level="diploma"', false)
            ->assertDontSee('Hidden Course');
    }

    public function test_living_costs_and_intakes_come_from_business_facts(): void
    {
        $this->get(route('consultancy.singapore'))->assertDontSee('Intakes:');

        app(SettingService::class)->set('facts', 'living_room', '700', 'string', true);
        app(SettingService::class)->set('facts', 'intakes', 'Jan · Apr · Jul · Oct', 'string', true);

        $this->get(route('consultancy.singapore'))->assertSee('SGD 700')->assertSee('Intakes: Jan · Apr · Jul · Oct');
    }

    public function test_about_page_shows_facts_only_once_entered_and_company_redirects_to_it(): void
    {
        $this->get(route('about'))->assertOk()
            ->assertSee('A Dhaka team helping students and travellers')
            ->assertDontSee('since')->assertDontSee('Trade licence')->assertDontSee('[YEAR]')
            ->assertSee('How we work')->assertSee('Written quotes');

        app(SettingService::class)->set('facts', 'founded_year', '2019', 'string', true);
        app(SettingService::class)->set('facts', 'trade_licence_no', 'TRAD/123', 'string', true);
        app(SettingService::class)->set('facts', 'about_story', 'We started to help students from our own neighbourhood.', 'string', true);

        $this->get(route('about'))->assertSee('since 2019')->assertSee('TRAD/123')->assertSee('We started to help students from our own neighbourhood.');
        $this->get('/company')->assertRedirect('/about');
    }
}
