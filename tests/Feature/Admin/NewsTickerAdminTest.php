<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\HomepageBlock;
use App\Models\User;
use App\Services\NewsTicker;
use App\Services\SettingService;
use Database\Seeders\HomepageSettingsSeeder;
use Database\Seeders\ProductionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NewsTickerAdminTest extends TestCase
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

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return ['text' => 'May intake applications are open.', 'text_bn' => 'মে ইনটেকের আবেদন চলছে।', 'url' => '/study-in-malaysia', 'badge' => 'Intake', 'is_published' => '1', ...$overrides];
    }

    private function headline(): HomepageBlock
    {
        return HomepageBlock::query()->create(['section' => NewsTicker::SECTION, 'title' => 'Existing headline', 'is_active' => true, 'sort_order' => 10]);
    }

    public function test_a_content_manager_can_add_edit_reorder_and_delete_headlines(): void
    {
        $editor = $this->user('content-editor');
        $this->actingAs($editor)->get(route('admin.news-ticker.index'))->assertOk()->assertSee('News ticker');
        $this->actingAs($editor)->get(route('admin.news-ticker.create'))->assertOk();

        $this->actingAs($editor)->post(route('admin.news-ticker.store'), $this->payload([
            'starts_at' => '2026-12-01T09:00', 'ends_at' => '2026-12-31T18:00',
        ]))->assertRedirect(route('admin.news-ticker.index'));

        $item = HomepageBlock::query()->where('section', NewsTicker::SECTION)->sole();
        $this->assertSame('May intake applications are open.', $item->title);
        $this->assertSame('মে ইনটেকের আবেদন চলছে।', $item->subtitle);
        $this->assertSame('Intake', $item->cta_label);
        $this->assertTrue($item->is_active);
        $this->assertSame('2026-12-01 03:00:00', $item->starts_at->utc()->format('Y-m-d H:i:s'), 'entered in Bangladesh time (UTC+6)');

        $this->actingAs($editor)->get(route('admin.news-ticker.edit', $item))->assertOk()->assertSee('value="2026-12-01T09:00"', false);
        $this->actingAs($editor)->patch(route('admin.news-ticker.update', $item), $this->payload(['text' => 'Updated headline', 'is_published' => '0']))->assertRedirect();
        $this->assertSame('Updated headline', $item->refresh()->title);
        $this->assertFalse($item->is_active);

        $first = $this->headline();
        $this->actingAs($editor)->patch(route('admin.news-ticker.move', $item), ['direction' => 'up'])->assertRedirect();
        $this->assertSame([$item->id, $first->id], HomepageBlock::query()->where('section', NewsTicker::SECTION)->orderBy('sort_order')->pluck('id')->all());

        $this->actingAs($editor)->delete(route('admin.news-ticker.destroy', $item))->assertRedirect();
        $this->assertModelMissing($item);
        $this->assertTrue(ActivityLog::query()->where('action', 'news_ticker.item_created')->exists());
    }

    public function test_a_content_manager_can_change_the_settings(): void
    {
        $this->actingAs($this->user('admin'))->patch(route('admin.news-ticker.settings'), [
            'news_enabled' => '1', 'news_speed' => 'slow', 'news_label' => 'Latest', 'news_label_bn' => 'সর্বশেষ',
        ])->assertRedirect(route('admin.news-ticker.index'));

        $ticker = app(NewsTicker::class);
        $this->assertTrue($ticker->isEnabled());
        $this->assertSame(35, $ticker->speed());
        $this->actingAs($this->user('admin'))->patch(route('admin.news-ticker.settings'), ['news_speed' => 'warp'])->assertSessionHasErrors('news_speed');
    }

    public function test_guests_are_sent_to_sign_in_and_roles_without_permission_are_forbidden(): void
    {
        $item = $this->headline();
        $this->get(route('admin.news-ticker.index'))->assertRedirect(route('login'));
        $this->post(route('admin.news-ticker.store'), $this->payload())->assertRedirect(route('login'));

        $this->actingAs($this->user('crm-staff'))->get(route('admin.news-ticker.index'))->assertForbidden();
        $viewer = $this->user('seo-manager');
        $this->actingAs($viewer)->get(route('admin.news-ticker.index'))->assertOk();
        $this->actingAs($viewer)->post(route('admin.news-ticker.store'), $this->payload())->assertForbidden();
        $this->actingAs($viewer)->patch(route('admin.news-ticker.update', $item), $this->payload())->assertForbidden();
        $this->actingAs($viewer)->delete(route('admin.news-ticker.destroy', $item))->assertForbidden();
        $this->actingAs($viewer)->patch(route('admin.news-ticker.settings'), ['news_speed' => 'fast'])->assertForbidden();
        $this->assertModelExists($item);

        $other = HomepageBlock::query()->create(['section' => 'service', 'title' => 'Not a headline']);
        $this->actingAs($this->user('admin'))->get(route('admin.news-ticker.edit', $other))->assertNotFound();
    }

    public function test_unsafe_links_long_text_and_reversed_dates_are_rejected(): void
    {
        $editor = $this->user('content-editor');
        foreach (['javascript:alert(1)', 'http://example.com', '//evil.example', 'data:text/html,hi'] as $url) {
            $this->actingAs($editor)->post(route('admin.news-ticker.store'), $this->payload(['url' => $url]))->assertSessionHasErrors('url');
        }
        $this->actingAs($editor)->post(route('admin.news-ticker.store'), $this->payload(['text' => str_repeat('a', 141)]))->assertSessionHasErrors('text');
        $this->actingAs($editor)->post(route('admin.news-ticker.store'), $this->payload(['text' => '']))->assertSessionHasErrors('text');
        $this->actingAs($editor)->post(route('admin.news-ticker.store'), $this->payload(['starts_at' => '2026-12-10T10:00', 'ends_at' => '2026-12-01T10:00']))->assertSessionHasErrors('ends_at');
        $this->assertSame(0, HomepageBlock::query()->where('section', NewsTicker::SECTION)->count());

        $this->actingAs($editor)->post(route('admin.news-ticker.store'), $this->payload(['url' => 'https://www.example.com/offer']))->assertSessionHasNoErrors();
    }

    public function test_saving_a_headline_or_the_settings_clears_the_cache(): void
    {
        $editor = $this->user('content-editor');
        $item = $this->headline();

        app(NewsTicker::class)->published();
        $this->assertTrue(Cache::has(NewsTicker::CACHE_KEY));
        $this->actingAs($editor)->patch(route('admin.news-ticker.update', $item), $this->payload());
        $this->assertFalse(Cache::has(NewsTicker::CACHE_KEY));

        app(NewsTicker::class)->published();
        $this->actingAs($editor)->post(route('admin.news-ticker.store'), $this->payload());
        $this->assertFalse(Cache::has(NewsTicker::CACHE_KEY));

        app(NewsTicker::class)->published();
        $this->assertFalse(app(NewsTicker::class)->isEnabled());
        $this->actingAs($editor)->patch(route('admin.news-ticker.settings'), ['news_enabled' => '1', 'news_speed' => 'normal']);
        $this->assertFalse(Cache::has(NewsTicker::CACHE_KEY));
        $this->assertTrue(app(NewsTicker::class)->isEnabled(), 'the settings cache is cleared too');
    }

    public function test_the_production_seeder_enables_nothing_and_creates_no_duplicates(): void
    {
        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class);

        $this->assertFalse(app(NewsTicker::class)->isEnabled());
        $this->assertSame(0, HomepageBlock::query()->where('section', NewsTicker::SECTION)->count());
        $this->assertSame(1, DB::table('settings')->where('group', 'homepage')->where('key', 'news_enabled')->count());
        $this->get(route('home'))->assertDontSee('data-news-ticker', false);
    }

    public function test_a_real_one_line_headline_is_carried_over_once_and_an_admin_choice_is_kept(): void
    {
        $settings = app(SettingService::class);
        $settings->set('homepage', 'news_enabled', '1', 'string', true);
        $settings->set('homepage', 'news_text', 'Visa desk hours have changed.', 'string', true);
        $settings->set('homepage', 'news_url', 'https://example.com/hours', 'string', true);

        $this->seed(HomepageSettingsSeeder::class);
        $this->seed(HomepageSettingsSeeder::class);

        $item = HomepageBlock::query()->where('section', NewsTicker::SECTION)->sole();
        $this->assertSame('Visa desk hours have changed.', $item->title);
        $this->assertTrue($item->is_active, 'it was already shown, so it stays shown');
        $this->assertSame('1', DB::table('settings')->where('group', 'homepage')->where('key', 'news_enabled')->value('value'), 'an existing setting is never overwritten');

        $item->delete();
        $this->seed(HomepageSettingsSeeder::class);
        $this->assertSame(0, HomepageBlock::query()->where('section', NewsTicker::SECTION)->count(), 'not added back after the admin deleted it');
    }
}
