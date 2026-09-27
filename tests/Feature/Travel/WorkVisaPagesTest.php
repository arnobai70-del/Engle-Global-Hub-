<?php

namespace Tests\Feature\Travel;

use App\Services\Feature\FeatureManager;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkVisaPagesTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->seed(RolePermissionSeeder::class); }

    public function test_work_visa_pages_are_reachable_without_signing_in(): void
    {
        $this->get(route('work-visa.index'))->assertOk()->assertSee('Work Visa Processing')->assertSee('Demo Preview');
        $this->get(route('work-visa.apply'))->assertOk()->assertSee('WORK VISA CONSULTATION')->assertSee('Demo Preview');
    }

    public function test_work_visa_page_has_safe_service_and_destination_previews(): void
    {
        $this->get(route('work-visa.index'))->assertOk()->assertSee('Skilled Worker')->assertSee('Employer Sponsored')->assertSee('Job Visa &amp; Work Permits', false)->assertSee('Sample destination')->assertSee('No guarantee')->assertDontSee('application submitted')->assertDontSee('guaranteed sponsorship');
    }

    public function test_work_visa_preview_does_not_claim_real_jobs_or_sponsorship(): void
    {
        $this->get(route('work-visa.index'))->assertOk()->assertSee('No fake vacancies or employers are listed')->assertSee('do not promise employment, sponsorship, eligibility, visa approval or a processing time');
    }

    public function test_work_visa_consultation_preview_is_disabled_and_has_no_payment(): void
    {
        $this->get(route('work-visa.apply'))->assertOk()->assertSee('Consultation information preview')->assertSee('disabled', false)->assertSee('Nothing entered here is submitted, stored, charged')->assertDontSee('BDT 75,000')->assertDontSee('payment successful');
    }

    public function test_work_visa_pages_follow_visa_feature_visibility(): void
    {
        app(FeatureManager::class)->update('visa', $this->state(publicVisible:false));
        $this->get(route('work-visa.index'))->assertNotFound();
        $this->get(route('work-visa.apply'))->assertNotFound();
        $this->get(route('home'))->assertOk()->assertDontSee(route('work-visa.index'));
    }

    public function test_work_visa_pages_accept_no_submission(): void
    {
        $this->post(route('work-visa.index'))->assertStatus(405);
        $this->post(route('work-visa.apply'))->assertStatus(405);
    }

    private function state(bool $enabled=true,bool $publicVisible=true,bool $authenticatedVisible=true,bool $adminVisible=true,?string $message=null): array
    {
        return ['enabled'=>$enabled,'public_visible'=>$publicVisible,'authenticated_visible'=>$authenticatedVisible,'admin_visible'=>$adminVisible,'message'=>$message];
    }
}
