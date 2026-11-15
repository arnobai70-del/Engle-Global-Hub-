<?php

namespace Tests\Feature;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\RewardProgram;
use App\Models\RewardTransaction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityAndRewardsTest extends TestCase
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

    private function category(string $slug, string $visibility = 'public', bool $approval = false): ForumCategory
    {
        return ForumCategory::query()->create(['name' => ucfirst($slug), 'slug' => $slug, 'visibility' => $visibility, 'requires_approval' => $approval, 'is_active' => true]);
    }

    public function test_forum_visibility_by_category(): void
    {
        $this->category('study');
        $this->category('members-lounge', 'members');
        $this->category('staff-room', 'staff');

        $this->get(route('forums.index'))->assertOk()->assertSee('Study')->assertDontSee('Members-lounge')->assertDontSee('Staff-room');
        $this->get(route('forums.category', 'members-lounge'))->assertNotFound();
        $customer = $this->user('customer');
        $this->actingAs($customer)->get(route('forums.category', 'members-lounge'))->assertOk();
        $this->actingAs($customer)->get(route('forums.category', 'staff-room'))->assertForbidden();
        $this->actingAs($this->user('crm-staff'))->get(route('forums.category', 'staff-room'))->assertOk();
    }

    public function test_posting_masks_personal_details_and_blocks_spam(): void
    {
        $this->category('study');
        $customer = $this->user('customer');

        $this->post(route('forums.threads.store', 'study'), ['title' => 'A guest topic', 'body' => str_repeat('x', 30)])->assertRedirect(route('login'));
        $this->actingAs($customer)->post(route('forums.threads.store', 'study'), [
            'title' => 'Question about Singapore intakes',
            'body' => 'Please call me on +8801712345678 or mail me at me@example.com, passport A1234567. Which intake is best?',
        ])->assertRedirect();

        $post = ForumPost::query()->sole();
        $this->assertStringContainsString('[phone removed]', $post->body);
        $this->assertStringContainsString('[email removed]', $post->body);
        $this->assertStringContainsString('[document number removed]', $post->body);
        $this->assertStringNotContainsString('me@example.com', $post->body);

        $thread = ForumThread::query()->sole();
        $this->actingAs($customer)->post(route('forums.replies.store', ['study', $thread->slug]), ['body' => 'see https://a.test https://b.test https://c.test'])->assertSessionHasErrors('body');
        $this->actingAs($customer)->post(route('forums.replies.store', ['study', $thread->slug]), ['body' => 'Thank you all!'])->assertSessionHasNoErrors();
        $this->actingAs($customer)->post(route('forums.replies.store', ['study', $thread->slug]), ['body' => 'Thank you all!'])->assertSessionHasErrors('body');
        $this->actingAs($customer)->post(route('forums.replies.store', ['study', $thread->slug]), ['body' => 'Valid reply text', 'website' => 'spam'])->assertSessionHasErrors('website');
        $this->assertSame(2, ForumPost::query()->count());
    }

    public function test_moderation_queue_reports_hiding_and_locking(): void
    {
        $this->category('moderated', 'public', true);
        $customer = $this->user('customer');
        $this->actingAs($customer)->post(route('forums.threads.store', 'moderated'), ['title' => 'Needs approval first', 'body' => 'This post should wait for a moderator.'])->assertRedirect(route('forums.category', 'moderated'));

        $thread = ForumThread::query()->sole();
        $this->assertSame(ForumPost::STATUS_PENDING, $thread->status);
        $this->get(route('forums.category', 'moderated'))->assertDontSee('Needs approval first');
        $this->actingAs($customer)->get(route('admin.forums.index'))->assertForbidden();

        $moderator = $this->user('content-editor');
        $post = ForumPost::query()->sole();
        $this->actingAs($moderator)->get(route('admin.forums.index'))->assertOk()->assertSee('Needs approval first');
        $this->actingAs($moderator)->patch(route('admin.forums.posts.moderate', $post), ['status' => 'visible'])->assertRedirect();
        $this->get(route('forums.category', 'moderated'))->assertSee('Needs approval first');

        $reporter = $this->user('customer');
        $this->actingAs($reporter)->post(route('forums.posts.report', $post), ['reason' => 'Off topic'])->assertRedirect();
        $this->actingAs($moderator)->get(route('admin.forums.index'))->assertSee('Off topic');
        $this->actingAs($moderator)->patch(route('admin.forums.posts.moderate', $post), ['status' => 'hidden'])->assertRedirect();
        $this->get(route('forums.thread', ['moderated', $thread->slug]))->assertNotFound();

        $this->actingAs($moderator)->patch(route('admin.forums.threads.lock', $thread), ['is_locked' => 1])->assertRedirect();
        $this->actingAs($moderator)->patch(route('admin.forums.posts.moderate', $post), ['status' => 'visible']);
        $this->actingAs($customer)->post(route('forums.replies.store', ['moderated', $thread->slug]), ['body' => 'Reply after lock'])->assertForbidden();
    }

    public function test_rewards_stay_disabled_until_published_with_terms(): void
    {
        $admin = $this->user('admin');
        $customer = $this->user('customer');
        $this->get(route('rewards.index'))->assertOk()->assertSee('There is no active rewards programme');

        $this->actingAs($admin)->post(route('admin.rewards.store'), ['name' => 'Eagle Points'])->assertRedirect();
        $program = RewardProgram::query()->sole();
        $this->assertFalse($program->is_published);

        $this->actingAs($admin)->patch(route('admin.rewards.update', $program), ['name' => 'Eagle Points', 'is_published' => '1'])->assertSessionHasErrors('terms');
        $this->actingAs($admin)->post(route('admin.rewards.adjust'), ['reward_program_id' => $program->id, 'customer_email' => $customer->email, 'points' => 10, 'reason' => 'x'])->assertSessionHasErrors('reward_program_id');

        $this->actingAs($admin)->patch(route('admin.rewards.update', $program), [
            'name' => 'Eagle Points', 'terms' => 'Owner approved terms.', 'earning_rules' => 'Owner approved rules.', 'points_expire_after_days' => 365, 'is_published' => '1',
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.rewards.adjust'), ['reward_program_id' => $program->id, 'customer_email' => $customer->email, 'points' => 100, 'reason' => 'Welcome'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.rewards.adjust'), ['reward_program_id' => $program->id, 'customer_email' => $customer->email, 'points' => -150, 'reason' => 'Too much'])->assertSessionHasErrors('points');

        RewardTransaction::query()->create(['user_id' => $customer->id, 'reward_program_id' => $program->id, 'points' => 50, 'reason' => 'Expired', 'expires_at' => now()->subDay()]);
        $this->actingAs($customer)->get(route('rewards.index'))->assertOk()->assertSee('Your balance: 100 points')->assertSee('Owner approved terms.');
        $this->assertDatabaseHas('activity_logs', ['action' => 'reward.adjusted']);
        $this->actingAs($this->user('content-editor'))->get(route('admin.rewards.index'))->assertForbidden();
    }
}
