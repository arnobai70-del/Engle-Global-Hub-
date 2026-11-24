<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\User;
use Database\Seeders\CompanyContentSettingsSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TeamMemberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    public function test_pdf_team_is_seeded_as_unpublished_drafts_and_reseeding_keeps_owner_changes(): void
    {
        $this->seed(TeamMemberSeeder::class);

        $this->assertSame(5, TeamMember::query()->count());
        $this->assertSame(0, TeamMember::query()->where('is_published', true)->count());
        $this->assertDatabaseHas('team_members', ['name' => 'MD. Jahirul Islam Jubel', 'role_title' => 'Founder & CEO']);

        TeamMember::query()->where('name', 'Tajul Islam')->update(['is_published' => true, 'bio' => 'Verified bio']);
        $this->seed(TeamMemberSeeder::class);

        $this->assertSame(5, TeamMember::query()->count());
        $tajul = TeamMember::query()->where('name', 'Tajul Islam')->sole();
        $this->assertTrue($tajul->is_published);
        $this->assertSame('Verified bio', $tajul->bio);
    }

    public function test_admin_manages_team_members_with_photo(): void
    {
        Storage::fake('public');
        $admin = $this->user('admin');
        $this->actingAs($admin)->get(route('admin.team-members.index'))->assertOk()->assertSee('Add Team Member');

        $this->actingAs($admin)->post(route('admin.team-members.store'), [
            'name' => 'New Consultant', 'role_title' => 'Admission Consultant', 'sort_order' => 6,
            'photo' => UploadedFile::fake()->image('face.jpg', 300, 300),
        ])->assertRedirect(route('admin.team-members.index'));

        $member = TeamMember::query()->sole();
        $this->assertFalse($member->is_published);
        Storage::disk('public')->assertExists($member->photo_path);

        $photoUrl = Storage::disk('public')->url($member->photo_path);
        $this->actingAs($admin)->get(route('admin.team-members.index'))
            ->assertOk()->assertSee($photoUrl)->assertSee('Photo of New Consultant');
        $this->actingAs($admin)->get(route('admin.team-members.edit', $member))
            ->assertOk()->assertSee('Current photo')->assertSee($photoUrl)
            ->assertSee('Current photo of New Consultant');

        $this->actingAs($admin)->patch(route('admin.team-members.update', $member), [
            'name' => 'New Consultant', 'role_title' => 'Admission Consultant', 'sort_order' => 2,
            'is_published' => '1', 'remove_photo' => '1',
        ])->assertRedirect();

        $member->refresh();
        $this->assertTrue($member->is_published);
        $this->assertNull($member->photo_path);
        $this->assertDatabaseHas('activity_logs', ['action' => 'team_member.updated', 'user_id' => $admin->id]);

        $this->actingAs($admin)->delete(route('admin.team-members.destroy', $member))->assertRedirect();
        $this->assertDatabaseCount('team_members', 0);
    }

    public function test_published_team_member_without_photo_uses_initial_and_drafts_remain_hidden(): void
    {
        TeamMember::query()->create([
            'name' => 'Approved Consultant',
            'role_title' => 'Advisor',
            'sort_order' => 1,
            'is_published' => true,
        ]);
        TeamMember::query()->create([
            'name' => 'Unverified Consultant',
            'role_title' => 'Draft Advisor',
            'sort_order' => 2,
            'is_published' => false,
        ]);

        $this->get(route('about'))->assertOk()
            ->assertSee('Our team')
            ->assertSee('Approved Consultant')
            ->assertSee('v2-team-initial')
            ->assertDontSee('Unverified Consultant');
    }

    public function test_photo_selection_preview_script_is_in_admin_rendered_page(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin)->get(route('admin.team-members.create'))
            ->assertOk()
            ->assertSee('team-photo-input')
            ->assertSee('team-selected-photo-preview')
            ->assertSee('URL.createObjectURL')
            ->assertSee('New photo preview (not saved yet)');
    }

    public function test_team_member_validation_rejects_duplicates_and_non_images(): void
    {
        $admin = $this->user('admin');
        TeamMember::query()->create(['name' => 'Tajul Islam', 'role_title' => 'Managing Director']);

        $this->actingAs($admin)->post(route('admin.team-members.store'), [
            'name' => 'Tajul Islam', 'role_title' => 'Managing Director', 'sort_order' => 1,
        ])->assertSessionHasErrors('role_title');

        $this->actingAs($admin)->post(route('admin.team-members.store'), [
            'name' => 'Someone', 'role_title' => 'Role', 'sort_order' => 1,
            'photo' => UploadedFile::fake()->create('photo.jpg', 10, 'application/pdf'),
        ])->assertSessionHasErrors('photo');

        $this->assertSame(1, TeamMember::query()->count());
    }

    public function test_customer_cannot_access_team_management(): void
    {
        $customer = $this->user('customer');

        $this->actingAs($customer)->get(route('admin.team-members.index'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.team-members.store'), ['name' => 'x', 'role_title' => 'y', 'sort_order' => 1])->assertForbidden();
    }

    public function test_mission_and_vision_slots_are_seeded_empty_and_editable_in_settings(): void
    {
        $this->seed(CompanyContentSettingsSeeder::class);
        Setting::query()->where('group', 'company')->where('key', 'mission')->update(['value' => 'Owner written mission']);
        $this->seed(CompanyContentSettingsSeeder::class);

        $this->assertSame('Owner written mission', Setting::query()->where('group', 'company')->where('key', 'mission')->value('value'));
        $this->assertSame('', Setting::query()->where('group', 'company')->where('key', 'vision')->value('value'));

        $this->actingAs($this->user('admin'))->get(route('admin.settings.manage', ['group' => 'company']))
            ->assertOk()->assertSee('mission')->assertSee('vision');
    }
}
