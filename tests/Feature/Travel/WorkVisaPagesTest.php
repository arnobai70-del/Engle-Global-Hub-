<?php

namespace Tests\Feature\Travel;

use App\Services\Feature\FeatureManager;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkVisaPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_work_visa_pages_are_reachable_without_signing_in(): void
    {
        $this->get(route('work-visa.index'))
            ->assertOk()
            ->assertSee('Work Visa Processing')
            ->assertSee('Your global career starts here');

        $this->get(route('work-visa.apply'))
            ->assertOk()
            ->assertSee('Select Visa')
            ->assertSee('Visa Category');
    }

    public function test_work_visa_processing_page_states_that_it_is_not_configured(): void
    {
        $this->get(route('work-visa.index'))
            ->assertOk()
            ->assertSee('Not Configured')
            ->assertSee('Work visa processing is not configured')
            ->assertDontSee('application submitted')
            ->assertDontSee('you are eligible')
            ->assertDontSee('approved');
    }

    public function test_work_visa_application_page_states_that_it_is_not_configured(): void
    {
        $this->get(route('work-visa.apply'))
            ->assertOk()
            ->assertSee('Not Configured')
            ->assertSee('Work visa applications are not configured')
            ->assertDontSee('application submitted')
            ->assertDontSee('payment successful')
            ->assertDontSee('you are eligible')
            ->assertDontSee('approved');
    }

    public function test_local_preview_shows_service_outline_and_destination_tiles(): void
    {
        $this->app['env'] = 'local';

        $this->get(route('work-visa.index'))
            ->assertOk()
            ->assertSee('Layout preview')
            ->assertSee('Job Visa &amp; Work Permits', false)
            ->assertSee('Skilled Worker Visa')
            ->assertSee('Employer Sponsored Visa')
            ->assertSee('Document Assistance')
            ->assertSee('Popular Work Visa Destinations')
            ->assertSee('Canada')
            ->assertSee('Sample')
            ->assertSee(route('work-visa.apply'), false)
            ->assertDontSee('processing time guaranteed');
    }

    public function test_local_preview_shows_disabled_application_form_and_sample_summary(): void
    {
        $this->app['env'] = 'local';

        $this->get(route('work-visa.apply'))
            ->assertOk()
            ->assertSee('Layout preview')
            ->assertSee('Personal information')
            ->assertSee('Gender')
            ->assertSee('Application summary')
            ->assertSee('Estimated processing')
            ->assertSee('BDT 75,000')
            ->assertSee('Save and continue')
            ->assertSee('disabled', false)
            ->assertSeeText('Nothing on this page can be submitted')
            ->assertDontSee('application submitted')
            ->assertDontSee('payment successful');
    }

    public function test_work_visa_pages_follow_visa_feature_visibility(): void
    {
        app(FeatureManager::class)->update(
            'visa',
            $this->state(publicVisible: false),
        );

        $this->get(route('work-visa.index'))->assertNotFound();
        $this->get(route('work-visa.apply'))->assertNotFound();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee(route('work-visa.index'));
    }

    public function test_work_visa_pages_accept_no_submission(): void
    {
        $this->post(route('work-visa.index'))->assertStatus(405);
        $this->post(route('work-visa.apply'))->assertStatus(405);
    }

    /**
     * @return array<string, bool|string|null>
     */
    private function state(
        bool $enabled = true,
        bool $publicVisible = true,
        bool $authenticatedVisible = true,
        bool $adminVisible = true,
        ?string $message = null,
    ): array {
        return [
            'enabled' => $enabled,
            'public_visible' => $publicVisible,
            'authenticated_visible' => $authenticatedVisible,
            'admin_visible' => $adminVisible,
            'message' => $message,
        ];
    }
}
