<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Course;
use App\Models\Institution;
use App\Models\StudentEnquiry;
use App\Models\User;
use App\Services\SettingService;
use App\Services\SiteFacts;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class CourseFinderTest extends TestCase
{
    use RefreshDatabase;

    private Institution $university;

    protected function setUp(): void
    {
        parent::setUp();
        $malaysia = Country::query()->create(['name' => 'Malaysia', 'iso2' => 'MY', 'iso3' => 'MYS', 'phone_code' => '+60', 'is_active' => true]);
        $this->university = Institution::query()->create(['name' => 'KL Test University', 'status' => Institution::STATUS_ACTIVE, 'country_id' => $malaysia->id]);
        $this->university->forceFill(['is_public' => true, 'publicly_verified_at' => now()])->save();
    }

    public function test_finder_is_hidden_until_a_malaysian_course_is_listed(): void
    {
        $this->get(route('consultancy.malaysia.courses'))->assertRedirect(route('consultancy.malaysia').'#apply');

        $this->course('Diploma in Business', 'diploma', 'Business', 18000);
        $this->get(route('consultancy.malaysia.courses'))->assertOk()->assertSee('Diploma in Business')->assertSee('KL Test University');
    }

    public function test_courses_filter_by_level_subject_budget_and_intake(): void
    {
        $business = $this->course('Diploma in Business', 'diploma', 'Business', 18000);
        $computing = $this->course('BSc Computing', 'bachelor', 'Computing', 32000);
        $this->course('Diploma in Hospitality', 'diploma', 'Hospitality', null);
        $business->intakes()->create(['starts_on' => now()->addMonths(2)->startOfMonth(), 'is_open' => true]);
        $computing->intakes()->create(['starts_on' => now()->addMonths(5)->startOfMonth(), 'is_open' => true]);

        $this->assertSame(['Diploma in Business', 'Diploma in Hospitality'], $this->found(['level' => 'diploma']));
        $this->assertSame(['BSc Computing'], $this->found(['subject' => 'Computing']));
        $this->assertSame(['Diploma in Business'], $this->found(['budget' => 20000]), 'courses above budget or without a fee are left out');
        $this->assertSame(['BSc Computing'], $this->found(['intake' => now()->addMonths(5)->format('Y-m')]));
        $this->assertSame([], $this->found(['level' => 'bachelor', 'budget' => 20000]));
    }

    public function test_fees_are_converted_to_taka_when_the_admin_rate_is_set(): void
    {
        $this->course('Diploma in Business', 'diploma', 'Business', 18000);
        app(SettingService::class)->set(SiteFacts::GROUP, 'my_bdt_rate', '27.5', 'string', true);

        $this->get(route('consultancy.malaysia.courses'))->assertOk()->assertSee('MYR 18,000 per year')->assertSee('BDT 495,000');
    }

    public function test_asking_about_a_course_creates_a_student_enquiry(): void
    {
        Notification::fake();
        $course = $this->course('Diploma in Business', 'diploma', 'Business', 18000);

        $this->from(route('consultancy.malaysia.courses'))->post(route('consultancy.malaysia.courses.enquire'), [
            'course_id' => $course->id, 'full_name' => 'Karim', 'phone' => '01712345678', 'consent' => '1',
        ])->assertRedirect(route('consultancy.malaysia.courses').'#ask')->assertSessionHas('lead_reference');

        $lead = StudentEnquiry::query()->sole();
        $this->assertSame('diploma', $lead->study_level);
        $this->assertStringContainsString('"Diploma in Business" at KL Test University', (string) $lead->message);
    }

    public function test_admin_can_set_subject_and_yearly_fee(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'institution_id' => $this->university->id, 'name' => 'Foundation in Science', 'study_level' => 'foundation',
            'subject' => 'Science', 'tuition_fee_myr' => '15000', 'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('courses', ['name' => 'Foundation in Science', 'subject' => 'Science', 'tuition_fee_myr' => 15000]);
    }

    private function course(string $name, string $level, string $subject, ?int $fee): Course
    {
        return Course::query()->create(['institution_id' => $this->university->id, 'name' => $name, 'study_level' => $level, 'subject' => $subject, 'tuition_fee_myr' => $fee, 'is_active' => true]);
    }

    /**
     * @param  array<string, string|int>  $filters
     * @return list<string>
     */
    private function found(array $filters): array
    {
        $html = (string) $this->get(route('consultancy.malaysia.courses', $filters))->assertOk()->getContent();
        preg_match_all('#<article class="v2-course">.*?<h3>(.*?)</h3>#s', $html, $matches);

        return $matches[1];
    }
}
