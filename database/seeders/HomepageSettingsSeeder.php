<?php

namespace Database\Seeders;

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
            ['seo', 'home_meta_title', 'Eagle Global Hub | Flights, Hotels, Tours & Visa Services'],
            ['seo', 'home_meta_description', 'Search flights and explore hotels, tours, visa and work visa services with Eagle Global Hub.'],
            ['seo', 'home_robots', 'index,follow'],
            ['seo', 'home_twitter_card', 'summary_large_image'],
        ];

        foreach ($settings as [$group, $key, $value]) {
            DB::table('settings')->insertOrIgnore([
                'group' => $group, 'key' => $key, 'value' => $value,
                'type' => 'string', 'is_public' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }
}
