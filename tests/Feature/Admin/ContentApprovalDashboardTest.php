<?php

namespace Tests\Feature\Admin;

use App\Models\ContentEntry;
use App\Models\User;
use Database\Seeders\BangladeshDestinationSeeder;
use Database\Seeders\ContentDraftSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SingaporeGuideSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentApprovalDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, BangladeshDestinationSeeder::class, SingaporeGuideSeeder::class, ContentDraftSeeder::class]);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    private function guide(string $slug): ContentEntry
    {
        return ContentEntry::query()->where('type', 'guide')->where('slug', $slug)->sole();
    }

    public function test_only_the_owner_can_open_the_approval_dashboard(): void
    {
        $this->actingAs($this->user('content-editor'))->get(route('admin.content.approvals'))->assertForbidden();
        $this->actingAs($this->user('admin'))->get(route('admin.content.approvals'))->assertForbidden();

        $this->actingAs($this->user('super-admin'))->get(route('admin.content.approvals'))->assertOk()
            ->assertSee('Content Approval')
            ->assertSee('Student&#039;s Pass', false)
            ->assertSee('ica.gov.sg')
            ->assertSee('Needs source verification')
            ->assertSee('Approval is locked until the sources are confirmed')
            ->assertSee('Other items waiting for an owner decision');
    }

    public function test_approving_requires_confirmation_and_never_publishes(): void
    {
        $owner = $this->user('super-admin');
        $guide = $this->guide('tuition-fees');
        $this->assertSame('needs_owner_review', $guide->review_status);

        $this->actingAs($owner)->post(route('admin.content.approvals.approve', $guide), ['confirm_reviewed' => '1'])
            ->assertSessionHasErrors('confirm_sources');
        $this->assertSame('needs_owner_review', $guide->fresh()->review_status);

        $this->actingAs($owner)->post(route('admin.content.approvals.approve', $guide), ['confirm_reviewed' => '1', 'confirm_sources' => '1'])
            ->assertRedirect(route('admin.content.approvals'));

        $guide->refresh();
        $this->assertSame(ContentEntry::REVIEW_APPROVED, $guide->review_status);
        $this->assertSame(ContentEntry::STATUS_DRAFT, $guide->status);
        $this->assertNull($guide->published_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'content.review-approved']);
    }

    public function test_entries_awaiting_source_verification_cannot_be_approved(): void
    {
        $guide = $this->guide('student-accommodation');

        $this->actingAs($this->user('super-admin'))->post(route('admin.content.approvals.approve', $guide), ['confirm_reviewed' => '1', 'confirm_sources' => '1'])
            ->assertSessionHasErrors('review_status');

        $this->assertSame('needs_source_verification', $guide->fresh()->review_status);
    }

    public function test_company_profile_approval_also_approves_claims(): void
    {
        $profile = ContentEntry::query()->where('type', 'company_profile')->sole();
        $profile->forceFill(['review_status' => 'needs_owner_review'])->save();

        $this->actingAs($this->user('super-admin'))->post(route('admin.content.approvals.approve', $profile), ['confirm_reviewed' => '1'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.content.approvals'));

        $profile->refresh();
        $this->assertSame(ContentEntry::REVIEW_APPROVED, $profile->review_status);
        $this->assertNotNull($profile->owner_approved_at);
        $this->assertSame(ContentEntry::STATUS_DRAFT, $profile->status);
    }

    public function test_requesting_changes_records_the_reason_and_removes_approval(): void
    {
        $owner = $this->user('super-admin');
        $guide = $this->guide('pre-departure');
        $guide->forceFill(['review_status' => ContentEntry::REVIEW_APPROVED])->save();

        $this->actingAs($owner)->post(route('admin.content.approvals.reject', $guide), ['reason' => ''])->assertSessionHasErrors('reason');

        $this->actingAs($owner)->post(route('admin.content.approvals.reject', $guide), ['reason' => 'Mention the <b>arrival</b> card deadline earlier.'])
            ->assertRedirect(route('admin.content.approvals'));

        $guide->refresh();
        $this->assertSame('changes_requested', $guide->review_status);
        $this->assertStringContainsString('Mention the arrival card deadline earlier.', $guide->review_notes);
        $this->assertStringContainsString('All sources fetched and checked', $guide->review_notes);
        $this->assertNotSame([], $guide->publishBlockers());

        $this->actingAs($this->user('content-editor'))->post(route('admin.content.approvals.reject', $guide), ['reason' => 'Editors cannot do this.'])->assertForbidden();
    }
}
