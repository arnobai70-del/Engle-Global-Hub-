<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OffersAndReviewsTest extends TestCase
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

    public function test_only_published_offers_within_dates_are_public(): void
    {
        $admin = $this->user('admin');
        $this->get(route('offers.index'))->assertOk()->assertSee('There are no active offers right now');

        $this->actingAs($admin)->post(route('admin.offers.store'), [
            'title' => 'Eid Holiday Deal', 'summary' => 'Current deal', 'sort_order' => 0, 'is_published' => '1',
            'valid_from' => now()->subDay()->toDateString(), 'valid_until' => now()->addWeek()->toDateString(),
        ])->assertRedirect(route('admin.offers.index'));
        Offer::query()->create(['title' => 'Expired', 'slug' => 'expired', 'summary' => 'Old', 'is_published' => true, 'valid_until' => now()->subDay()]);
        Offer::query()->create(['title' => 'Draft Deal', 'slug' => 'draft-deal', 'summary' => 'Draft', 'is_published' => false]);
        Offer::query()->create(['title' => 'Future Deal', 'slug' => 'future-deal', 'summary' => 'Future', 'is_published' => true, 'valid_from' => now()->addWeek()]);

        $this->assertDatabaseHas('offers', ['slug' => 'eid-holiday-deal']);
        $this->get(route('offers.index'))->assertOk()->assertSee('Eid Holiday Deal')
            ->assertDontSee('Expired')->assertDontSee('Draft Deal')->assertDontSee('Future Deal');

        $this->actingAs($admin)->post(route('admin.offers.store'), [
            'title' => 'Bad dates', 'summary' => 'x', 'sort_order' => 0,
            'valid_from' => now()->addWeek()->toDateString(), 'valid_until' => now()->toDateString(),
        ])->assertSessionHasErrors('valid_until');
    }

    public function test_customer_reviews_require_login_and_moderation(): void
    {
        $this->post(route('reviews.store'), [])->assertRedirect(route('login'));
        $this->get(route('reviews.index'))->assertOk()->assertSee('Sign in')->assertSee('No reviews have been published yet.');

        $customer = $this->user('customer');
        $this->actingAs($customer)->post(route('reviews.store'), [
            'rating' => 5, 'title' => 'Helpful team', 'display_name' => 'Rahim',
            'body' => 'They explained every step of my visa file clearly.',
        ])->assertRedirect(route('reviews.index'));

        $review = Review::query()->sole();
        $this->assertSame(Review::STATUS_PENDING, $review->status);
        $this->get(route('reviews.index'))->assertDontSee('Helpful team');

        $this->actingAs($customer)->patch(route('admin.reviews.update', $review), ['status' => 'approved'])->assertForbidden();

        $admin = $this->user('admin');
        $this->actingAs($admin)->get(route('admin.reviews.index'))->assertOk()->assertSee('Helpful team');
        $this->actingAs($admin)->patch(route('admin.reviews.update', $review), ['status' => 'approved'])->assertRedirect();

        $this->get(route('reviews.index'))->assertSee('Helpful team')->assertSee('Rahim')->assertDontSee($customer->email);
    }

    public function test_review_validation(): void
    {
        $this->actingAs($this->user('customer'))->post(route('reviews.store'), [
            'rating' => 9, 'title' => '', 'display_name' => 'X', 'body' => 'short',
        ])->assertSessionHasErrors(['rating', 'title', 'body']);

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_cannot_manage_offers(): void
    {
        $customer = $this->user('customer');

        $this->actingAs($customer)->get(route('admin.offers.index'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.offers.store'), ['title' => 'x'])->assertForbidden();
    }
}
