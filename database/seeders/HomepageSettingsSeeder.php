<?php

namespace Database\Seeders;

use App\Models\HomepageBlock;
use App\Services\NewsTicker;
use App\Services\SettingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HomepageSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $settings = [
            ['general', 'site_tagline', 'Travel & Visa Services'],
            ['general', 'footer_description', 'Your trusted global travel and visa partner. We make travel, work, and study abroad simpler and easier.'],
            // News ticker (Admin › News ticker): off until an admin adds headlines and switches it on.
            ['homepage', 'news_enabled', '0'],
            ['homepage', 'news_label', 'Latest'],
            ['homepage', 'news_speed', 'normal'],
            ['homepage', 'news_label_bn', 'সর্বশেষ খবর'],
            ['homepage', 'hero_eyebrow', 'EXPLORE · BOOK · TRAVEL · GROW'],
            ['homepage', 'hero_title', 'Travel the World with'],
            ['homepage', 'hero_accent', 'Eagle Global Hub'],
            ['homepage', 'hero_subtitle', 'Flights, Hotels, Tours, Visa, Work Visa Processing & More — your trusted global travel and visa partner.'],
            ['homepage', 'hero_side_text', "Discover\nExplore\nWork\nStudy\nTravel Beyond\nBoundaries"],
            ['homepage', 'hero_image', 'https://images.unsplash.com/photo-1505228395891-9a51e7e86bf6?auto=format&fit=crop&w=2560&q=85'],
            ['homepage', 'services_title', 'Our Services'],
            ['homepage', 'services_subtitle', 'Everything you need for a perfect journey'],
            ['homepage', 'destinations_title', 'Popular Destinations'],
            ['homepage', 'destinations_subtitle', 'Discover the most loved destinations around the world'],
            ['homepage', 'benefits_title', 'Why Choose Eagle Global Hub?'],
            ['homepage', 'benefits_subtitle', 'Reliable travel services with secure account and booking steps.'],
            ['homepage', 'testimonials_title', 'What Our Customers Say'],
            ['homepage', 'testimonials_subtitle', 'Published testimonials are managed and approved from the admin panel.'],
            ['homepage', 'newsletter_title', "Let's Make Your Next Journey Amazing"],
            ['homepage', 'newsletter_subtitle', 'Exclusive deals, travel tips and more, straight to your inbox.'],
            ['homepage', 'journey_title', "Let's Make Your Next Journey Amazing"],
            ['homepage', 'journey_copy', 'Create an account or subscribe for travel updates and keep your journey in one place.'],
            ['homepage', 'journey_background', 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=2000&q=85'],
            ['app', 'app_title', 'Download Our Mobile App'],
            ['app', 'app_subtitle', 'Book on the go, anytime, anywhere. Store links appear when published builds are configured.'],
            ['seo', 'home_meta_title', 'Study in Malaysia from Bangladesh | Eagle Global Hub'],
            ['seo', 'home_meta_description', 'Free consultation for studying in Malaysia: university choice, admission, the EMGS Student Pass, air ticket and housing support from our office in Gulshan, Dhaka. Singapore too.'],
            ['seo', 'home_robots', 'index,follow'],
            ['seo', 'home_twitter_card', 'summary_large_image'],
        ];

        // The site now leads with Malaysia. Replace an earlier default homepage title and
        // description, but only if an admin has not already written their own.
        foreach ([
            'home_meta_title' => [['Eagle Global Hub | Flights, Hotels, Tours & Visa Services', 'Study in Singapore from Bangladesh | Eagle Global Hub'], 'Study in Malaysia from Bangladesh | Eagle Global Hub'],
            'home_meta_description' => [['Search flights and explore hotels, tours, visa and work visa services with Eagle Global Hub.', 'Free consultation for studying in Singapore: course choice, admission, Student\'s Pass, air ticket and housing support from our office in Gulshan, Dhaka.'], 'Free consultation for studying in Malaysia: university choice, admission, the EMGS Student Pass, air ticket and housing support from our office in Gulshan, Dhaka. Singapore too.'],
        ] as $key => [$oldDefaults, $newDefault]) {
            DB::table('settings')->where('group', 'seo')->where('key', $key)->whereIn('value', $oldDefaults)
                ->update(['value' => $newDefault, 'updated_at' => now()]);
        }

        foreach ($settings as [$group, $key, $value]) {
            DB::table('settings')->insertOrIgnore([
                'group' => $group, 'key' => $key, 'value' => $value,
                'type' => 'string', 'is_public' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // The Bengali ticker label: also filled when the row exists but is still empty.
        DB::table('settings')->where('group', 'homepage')->where('key', 'news_label_bn')
            ->where(fn ($query) => $query->whereNull('value')->orWhere('value', ''))
            ->update(['value' => 'সর্বশেষ খবর', 'updated_at' => now()]);
        app(SettingService::class)->forget('homepage', 'news_label_bn');

        $this->carryOverOneLineNews();
    }

    /**
     * The ticker used to show one line (news_text). If an admin wrote a real one, it becomes the
     * first headline once, in the same state it had (shown only if it was already shown), so
     * nothing visible changes on deploy. The seeded welcome line is never carried over.
     */
    private function carryOverOneLineNews(): void
    {
        $setting = fn (string $key): string => trim((string) DB::table('settings')->where('group', 'homepage')->where('key', $key)->value('value'));
        $text = $setting('news_text');
        if ($text === '' || str_starts_with($text, 'Welcome to Eagle Global Hub') || $setting('news_legacy_carried_over') === '1'
            || HomepageBlock::query()->where('section', NewsTicker::SECTION)->exists()) {
            return;
        }
        $url = $setting('news_url');
        HomepageBlock::query()->create([
            'section' => NewsTicker::SECTION, 'title' => mb_substr($text, 0, 140),
            'url' => str_starts_with($url, 'https://') || (str_starts_with($url, '/') && ! str_starts_with($url, '//')) ? $url : null,
            'is_active' => ! in_array(strtolower($setting('news_enabled')), ['', '0', 'false', 'off', 'no'], true),
            'sort_order' => 10,
        ]);
        DB::table('settings')->insertOrIgnore(['group' => 'homepage', 'key' => 'news_legacy_carried_over', 'value' => '1', 'type' => 'string', 'is_public' => false, 'created_at' => now(), 'updated_at' => now()]);
        app(NewsTicker::class)->forget();
    }
}
