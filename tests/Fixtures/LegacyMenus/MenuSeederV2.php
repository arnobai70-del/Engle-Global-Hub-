<?php

namespace Tests\Fixtures\LegacyMenus;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Test fixture: MenuSeeder as released in commit 17c6fbf, used to rebuild a database shaped like the live site.
 *
 * Seeds the navigation that the website shows today, so the menu builder starts
 * from the current design. Safe to re-run: items are matched by key and never
 * overwritten, so admin edits are kept.
 */
class MenuSeederV2 extends Seeder
{
    public function run(): void
    {
        $bengali = json_decode((string) @file_get_contents(lang_path('bn.json')), true) ?: [];

        foreach ($this->menus() as $location => $items) {
            foreach ($items as $position => $item) {
                $parent = $this->place($location, null, $position, $item, $bengali);
                foreach ($item['children'] ?? [] as $childPosition => $child) {
                    $this->place($location, $parent->id, $childPosition, $child, $bengali);
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, string>  $bengali
     */
    private function place(string $location, ?int $parentId, int $position, array $item, array $bengali): MenuItem
    {
        return MenuItem::query()->firstOrCreate(
            ['location' => $location, 'key' => $item['key']],
            [
                'parent_id' => $parentId,
                'label_en' => $item['label'],
                'label_bn' => $bengali[$item['label']] ?? null,
                'target' => $item['target'] ?? null,
                'fragment' => $item['fragment'] ?? null,
                'icon' => $item['icon'] ?? null,
                'feature' => $item['feature'] ?? null,
                'visibility' => $item['visibility'] ?? 'all',
                'is_active' => true,
                'sort_order' => ($position + 1) * 10,
            ],
        );
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function menus(): array
    {
        $r = static fn (string $name, ?string $parameter = null): string => 'route:'.$name.($parameter !== null ? '|'.$parameter : '');

        return [
            // At most six top-level items (DESIGN_BRIEF.md). Pages without published content hide themselves.
            'header' => [
                ['key' => 'study', 'label' => 'Study in Singapore', 'feature' => 'student-consultancy', 'children' => [
                    ['key' => 'study-main', 'label' => 'Study in Singapore', 'target' => $r('consultancy.singapore')],
                    ['key' => 'study-student-visa', 'label' => 'Student Visa', 'target' => $r('services.show', 'student-visa')],
                    ['key' => 'study-guides', 'label' => 'Singapore Guides', 'target' => $r('consultancy.guides.index')],
                    ['key' => 'study-institutions', 'label' => 'Institutions', 'target' => $r('consultancy.institutions.index')],
                    ['key' => 'study-apply', 'label' => 'Free Consultation', 'target' => $r('consultancy.singapore'), 'fragment' => 'apply'],
                    ['key' => 'study-applications', 'label' => 'My Applications', 'target' => $r('student.applications.index'), 'visibility' => 'auth'],
                ]],
                ['key' => 'travel', 'label' => 'Visa & Travel', 'children' => [
                    ['key' => 'travel-tourist-visa', 'label' => 'Tourist Visa', 'target' => $r('services.show', 'tourist-visa')],
                    ['key' => 'travel-business-visa', 'label' => 'Business Visa', 'target' => $r('services.show', 'business-visa')],
                    ['key' => 'travel-work-visa', 'label' => 'Work Visa', 'target' => $r('work-visa.index')],
                    ['key' => 'travel-air', 'label' => 'Air Tickets', 'target' => $r('services.show', 'air-ticket')],
                    ['key' => 'travel-hotels', 'label' => 'Hotels', 'target' => $r('hotels.index')],
                    ['key' => 'travel-holiday', 'label' => 'Holiday Packages', 'target' => $r('services.show', 'holiday-packages')],
                    ['key' => 'travel-group', 'label' => 'Group Tours', 'target' => $r('services.show', 'group-tours')],
                    ['key' => 'travel-foreigner', 'label' => 'Foreigner Support', 'target' => $r('services.show', 'foreigner-support')],
                    ['key' => 'travel-all', 'label' => 'All travel services', 'target' => $r('travel-services.index')],
                ]],
                ['key' => 'how-it-works', 'label' => 'How it works', 'target' => $r('home'), 'fragment' => 'how-it-works'],
                ['key' => 'success', 'label' => 'Success stories', 'target' => $r('success-stories.index')],
                ['key' => 'about', 'label' => 'About', 'children' => [
                    ['key' => 'about-us', 'label' => 'About Us', 'target' => $r('about')],
                    ['key' => 'about-why-us', 'label' => 'Why Us', 'target' => $r('why-us')],
                ]],
                ['key' => 'contact', 'label' => 'Contact', 'target' => $r('support')],
            ],
            'footer' => [
                ['key' => 'footer-study', 'label' => 'Study', 'children' => [
                    ['key' => 'footer-singapore', 'label' => 'Study in Singapore', 'target' => $r('consultancy.singapore')],
                    ['key' => 'footer-institutions', 'label' => 'Institutions', 'target' => $r('consultancy.institutions.index')],
                    ['key' => 'footer-students-pass', 'label' => 'Student\'s Pass', 'target' => $r('services.show', 'student-visa')],
                    ['key' => 'footer-guides', 'label' => 'Guides', 'target' => $r('consultancy.guides.index')],
                    ['key' => 'footer-success', 'label' => 'Success stories', 'target' => $r('success-stories.index')],
                ]],
                ['key' => 'footer-services', 'label' => 'Services', 'children' => [
                    ['key' => 'footer-visa', 'label' => 'Visa services', 'target' => $r('visa.index')],
                    ['key' => 'footer-air', 'label' => 'Air tickets', 'target' => $r('services.show', 'air-ticket')],
                    ['key' => 'footer-hotels-tours', 'label' => 'Hotels & tours', 'target' => $r('travel-services.index')],
                    ['key' => 'footer-work-visa', 'label' => 'Work visa', 'target' => $r('work-visa.index')],
                ]],
                ['key' => 'footer-company', 'label' => 'Company', 'children' => [
                    ['key' => 'footer-about', 'label' => 'About us', 'target' => $r('about')],
                    ['key' => 'footer-why-us', 'label' => 'Why Us', 'target' => $r('why-us')],
                    ['key' => 'footer-contact', 'label' => 'Contact', 'target' => $r('support')],
                    ['key' => 'footer-privacy', 'label' => 'Privacy Policy', 'target' => $r('privacy')],
                    ['key' => 'footer-terms', 'label' => 'Terms & Conditions', 'target' => $r('terms')],
                ]],
                ['key' => 'footer-more', 'label' => 'More from us', 'children' => [
                    ['key' => 'footer-export', 'label' => 'Export & Import', 'target' => $r('services.show', 'export-import')],
                    ['key' => 'footer-civil', 'label' => 'Civil & Construction Work', 'target' => $r('services.show', 'civil-construction')],
                    ['key' => 'footer-b2b', 'label' => 'Become a partner (B2B)', 'target' => $r('b2b.info')],
                    ['key' => 'footer-search', 'label' => 'Search', 'target' => $r('search')],
                    ['key' => 'footer-explore', 'label' => 'Explore Bangladesh', 'target' => $r('explore-bangladesh.index')],
                    ['key' => 'footer-offers', 'label' => 'Offers & Promotions', 'target' => $r('offers.index')],
                    ['key' => 'footer-reviews', 'label' => 'Reviews', 'target' => $r('reviews.index')],
                    ['key' => 'footer-forums', 'label' => 'Forums', 'target' => $r('forums.index')],
                    ['key' => 'footer-inspired', 'label' => 'Get Inspired', 'target' => $r('inspiration.index')],
                    ['key' => 'footer-restaurants', 'label' => 'Restaurants', 'target' => $r('restaurants.index')],
                    ['key' => 'footer-rewards', 'label' => 'Rewards', 'target' => $r('rewards.index')],
                    ['key' => 'footer-map', 'label' => 'Map', 'target' => $r('map')],
                    ['key' => 'footer-app', 'label' => 'Mobile App', 'target' => $r('mobile-app')],
                ]],
            ],
            // The six service cards on the homepage (title, icon and link; the short text is built in).
            'home_tiles' => [
                ['key' => 'tile-study', 'label' => 'Study in Singapore', 'target' => $r('consultancy.singapore'), 'icon' => 'cap'],
                ['key' => 'tile-students-pass', 'label' => "Student's Pass (ICA)", 'target' => $r('services.show', 'student-visa'), 'icon' => 'id'],
                ['key' => 'tile-visa', 'label' => 'Visa services', 'target' => $r('services.show', 'tourist-visa'), 'icon' => 'passport'],
                ['key' => 'tile-flights', 'label' => 'Air tickets', 'target' => $r('services.show', 'air-ticket'), 'icon' => 'plane'],
                ['key' => 'tile-housing', 'label' => 'Housing & arrival', 'target' => $r('hotels.index'), 'icon' => 'bed'],
                ['key' => 'tile-tours', 'label' => 'Tours & holidays', 'target' => $r('services.show', 'holiday-packages'), 'icon' => 'mountain'],
            ],
            'header_cta' => [
                ['key' => 'cta-consultation', 'label' => 'Free Consultation', 'target' => $r('consultancy.singapore'), 'fragment' => 'apply'],
            ],
        ];
    }
}
