<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\TeamMember;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class EmptyPageRedirectAndTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_pages_redirect_to_the_homepage_until_content_is_published(): void
    {
        foreach (['success-stories.index', 'reviews.index', 'map', 'mobile-app'] as $page) {
            $this->get(route($page))->assertRedirect(route('home'));
        }

        $story = new ContentEntry(['slug' => 'first', 'title' => 'First story', 'summary' => 'S', 'body' => 'B']);
        $story->type = ContentType::SuccessStory;
        $story->status = ContentEntry::STATUS_PUBLISHED;
        $story->save();
        $this->get(route('success-stories.index'))->assertOk()->assertSee('First story');

        app(SettingService::class)->set('homepage', 'google_play_url', 'https://play.google.com/store/apps/details?id=example', 'string', true);
        $this->get(route('mobile-app'))->assertOk();
    }

    public function test_team_section_is_hidden_without_photos_and_shows_only_members_with_a_photo(): void
    {
        $withoutPhoto = TeamMember::query()->create(['name' => 'No Photo Person', 'role_title' => 'Consultant', 'is_published' => true]);

        $this->get(route('home'))->assertOk()->assertDontSee('Real people, real office')->assertDontSee('No Photo Person');
        $this->get(route('about'))->assertOk()->assertDontSee('Our team')->assertDontSee('No Photo Person');

        $withPhoto = TeamMember::query()->create(['name' => 'Photo Person', 'role_title' => 'Managing Director', 'is_published' => true]);
        DB::table('team_members')->where('id', $withPhoto->id)->update(['photo_path' => 'team/photo-person.webp']);

        $this->get(route('home'))->assertSee('Real people, real office')->assertSee('Photo Person')->assertDontSee('No Photo Person');
        $this->get(route('about'))->assertSee('Photo Person')->assertDontSee($withoutPhoto->name);
    }
}
