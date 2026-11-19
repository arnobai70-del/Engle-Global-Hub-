<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\EligibilityRule;
use App\Models\StudentEnquiry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class EligibilityCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checker_is_hidden_until_admin_adds_an_active_rule(): void
    {
        $this->get(route('consultancy.malaysia.eligibility'))->assertRedirect(route('consultancy.malaysia').'#apply');

        EligibilityRule::factory()->create(['is_active' => false]);
        $this->get(route('consultancy.malaysia.eligibility'))->assertRedirect(route('consultancy.malaysia').'#apply');

        EligibilityRule::factory()->create();
        $this->get(route('consultancy.malaysia.eligibility'))->assertOk()->assertSee('Eligibility checker');
    }

    public function test_result_lists_only_course_levels_whose_admin_rules_are_met(): void
    {
        EligibilityRule::factory()->create(['study_level' => 'diploma', 'qualifications' => ['ssc'], 'min_gpa' => 3.0, 'note_en' => 'Interview may be required.']);
        EligibilityRule::factory()->create(['study_level' => 'bachelor', 'qualifications' => ['hsc'], 'min_gpa' => 3.5, 'min_english' => 6.0]);
        EligibilityRule::factory()->create(['study_level' => 'foundation', 'qualifications' => ['hsc'], 'max_gap_years' => 2]);

        $this->assertSame(['Diploma'], $this->matchedLevels(['qualification' => 'ssc', 'gpa' => '3.20', 'gap_years' => 0]));
        $this->assertSame([], $this->matchedLevels(['qualification' => 'ssc', 'gpa' => '2.90', 'gap_years' => 0]), 'below the minimum result');
        $this->assertSame(['Foundation'], $this->matchedLevels(['qualification' => 'hsc', 'gpa' => '4.00', 'gap_years' => 1]), 'no English score: bachelor needs 6.0');
        $this->assertSame(["Bachelor's degree"], $this->matchedLevels(['qualification' => 'hsc', 'gpa' => '4.00', 'english' => '6.5', 'gap_years' => 3]), 'gap too long for foundation');

        $this->get(route('consultancy.malaysia.eligibility', ['qualification' => 'ssc', 'gpa' => '3.20', 'gap_years' => 0]))->assertSee('Interview may be required.');
        $this->get(route('consultancy.malaysia.eligibility', ['qualification' => 'master', 'gpa' => '3.00', 'gap_years' => 0]))->assertOk()
            ->assertSee('We could not match a course level yet');
    }

    /**
     * @param  array<string, string|int>  $answers
     * @return list<string>
     */
    private function matchedLevels(array $answers): array
    {
        $html = (string) $this->get(route('consultancy.malaysia.eligibility', $answers))->assertOk()->getContent();
        preg_match('#<ul class="v2-tool-matches">(.*?)</ul>#s', $html, $list);
        preg_match_all('#<strong>(.*?)</strong>#', $list[1] ?? '', $levels);

        return array_map(fn (string $level): string => html_entity_decode($level, ENT_QUOTES), $levels[1]);
    }

    public function test_student_can_leave_name_and_phone_and_it_becomes_a_student_enquiry(): void
    {
        Notification::fake();
        Country::query()->create(['name' => 'Malaysia', 'iso2' => 'MY', 'iso3' => 'MYS', 'is_active' => true]);
        EligibilityRule::factory()->create(['study_level' => 'diploma', 'qualifications' => ['ssc']]);

        $this->post(route('consultancy.malaysia.eligibility.store'), [
            'qualification' => 'ssc', 'gpa' => '4.25', 'english' => '5.5', 'gap_years' => 1,
            'full_name' => 'Rahim Uddin', 'phone' => '01712345678', 'consent' => '1',
        ])->assertRedirect()->assertSessionHas('lead_reference');

        $lead = StudentEnquiry::query()->sole();
        $this->assertSame('Rahim Uddin', $lead->full_name);
        $this->assertSame('diploma', $lead->study_level);
        $this->assertSame('MY', $lead->destinationCountry?->iso2);
        $this->assertStringContainsString('Result (GPA/CGPA): 4.25', (string) $lead->message);
        $this->assertStringContainsString('Matching course levels: Diploma', (string) $lead->message);
    }

    public function test_lead_needs_consent_and_a_valid_phone(): void
    {
        EligibilityRule::factory()->create();

        $this->post(route('consultancy.malaysia.eligibility.store'), ['qualification' => 'hsc', 'gpa' => '4', 'gap_years' => 0, 'full_name' => 'A', 'phone' => 'abc'])
            ->assertSessionHasErrors(['phone', 'consent']);
        $this->assertSame(0, StudentEnquiry::query()->count());
    }

    public function test_only_course_managers_can_change_rules(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $payload = ['study_level' => 'bachelor', 'qualifications' => ['hsc', 'diploma'], 'min_gpa' => '3.5', 'min_english' => '6', 'max_gap_years' => '', 'sort_order' => 10, 'is_active' => '1'];
        $this->actingAs($customer)->post(route('admin.eligibility-rules.store'), $payload)->assertForbidden();
        $this->actingAs($admin)->post(route('admin.eligibility-rules.store'), $payload)->assertRedirect(route('admin.eligibility-rules.index'));

        $rule = EligibilityRule::query()->sole();
        $this->assertSame(['hsc', 'diploma'], $rule->qualifications);
        $this->assertSame(3.5, $rule->min_gpa);
        $this->assertNull($rule->max_gap_years);

        $this->actingAs($admin)->get(route('admin.eligibility-rules.index'))->assertOk()->assertSee('HSC / A-Level, Diploma');
        $this->actingAs($admin)->delete(route('admin.eligibility-rules.destroy', $rule))->assertRedirect();
        $this->assertSame(0, EligibilityRule::query()->count());
    }
}
