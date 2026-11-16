<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Seeds the navigation that the website shows today, so the menu builder starts
 * from the current design. Safe to re-run: items are matched by key and never
 * overwritten, so admin edits are kept.
 */
class MenuSeeder extends Seeder
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
            'header' => [
                ['key' => 'home', 'label' => 'Home', 'target' => $r('home')],
                ['key' => 'study', 'label' => 'Study in Singapore', 'feature' => 'student-consultancy', 'children' => [
                    ['key' => 'study-main', 'label' => 'Study in Singapore', 'target' => $r('consultancy.singapore')],
                    ['key' => 'study-student-visa', 'label' => 'Student Visa', 'target' => $r('services.show', 'student-visa')],
                    ['key' => 'study-guides', 'label' => 'Singapore Guides', 'target' => $r('consultancy.guides.index')],
                    ['key' => 'study-institutions', 'label' => 'Institutions', 'target' => $r('consultancy.institutions.index')],
                    ['key' => 'study-consultation', 'label' => 'Free Consultation', 'target' => $r('consultancy.singapore'), 'fragment' => 'consultation'],
                    ['key' => 'study-success', 'label' => 'Success Stories', 'target' => $r('success-stories.index')],
                    ['key' => 'study-applications', 'label' => 'My Applications', 'target' => $r('student.applications.index'), 'visibility' => 'auth'],
                ]],
                ['key' => 'visa', 'label' => 'Visa Services', 'feature' => 'visa', 'children' => [
                    ['key' => 'visa-info', 'label' => 'Visa information', 'target' => $r('visa.index')],
                    ['key' => 'visa-tourist', 'label' => 'Tourist Visa', 'target' => $r('services.show', 'tourist-visa')],
                    ['key' => 'visa-business', 'label' => 'Business Visa', 'target' => $r('services.show', 'business-visa')],
                    ['key' => 'visa-work', 'label' => 'Work Visa', 'target' => $r('work-visa.index')],
                    ['key' => 'visa-foreigner', 'label' => 'Foreigner Support', 'target' => $r('services.show', 'foreigner-support')],
                ]],
                ['key' => 'travel', 'label' => 'Travel Services', 'children' => [
                    ['key' => 'travel-all', 'label' => 'All travel services', 'target' => $r('travel-services.index')],
                    ['key' => 'travel-air', 'label' => 'Air Ticket', 'target' => $r('services.show', 'air-ticket'), 'feature' => 'flights'],
                    ['key' => 'travel-business-class', 'label' => 'Business Class', 'target' => $r('services.show', 'business-class'), 'feature' => 'flights'],
                    ['key' => 'travel-hotels', 'label' => 'Hotels', 'target' => $r('hotels.index')],
                    ['key' => 'travel-tours', 'label' => 'Tours', 'target' => $r('tours.index')],
                    ['key' => 'travel-holiday', 'label' => 'Holiday Packages', 'target' => $r('services.show', 'holiday-packages')],
                    ['key' => 'travel-group', 'label' => 'Group Tours', 'target' => $r('services.show', 'group-tours')],
                    ['key' => 'travel-private', 'label' => 'Private Tours', 'target' => $r('services.show', 'private-tours')],
                    ['key' => 'travel-transport', 'label' => 'Trains, Bus, Cars & Bike', 'target' => $r('travel-services.index'), 'fragment' => 'travel-group-2'],
                    ['key' => 'travel-offers', 'label' => 'Offers & Promotions', 'target' => $r('offers.index')],
                    ['key' => 'travel-rewards', 'label' => 'Rewards', 'target' => $r('rewards.index')],
                    ['key' => 'travel-manage-booking', 'label' => 'Manage Booking', 'target' => $r('dashboard'), 'visibility' => 'auth', 'feature' => 'bookings'],
                ]],
                ['key' => 'explore', 'label' => 'Explore Bangladesh', 'children' => [
                    ['key' => 'explore-main', 'label' => 'Explore Bangladesh', 'target' => $r('explore-bangladesh.index')],
                    ['key' => 'explore-inspired', 'label' => 'Get Inspired', 'target' => $r('inspiration.index')],
                    ['key' => 'explore-restaurants', 'label' => 'Restaurants', 'target' => $r('restaurants.index')],
                    ['key' => 'explore-reviews', 'label' => 'Reviews', 'target' => $r('reviews.index')],
                    ['key' => 'explore-forums', 'label' => 'Forums', 'target' => $r('forums.index')],
                    ['key' => 'explore-map', 'label' => 'Map', 'target' => $r('map')],
                ]],
                ['key' => 'about', 'label' => 'About', 'children' => [
                    ['key' => 'about-us', 'label' => 'About Us', 'target' => $r('about')],
                    ['key' => 'about-company', 'label' => 'Company Profile', 'target' => $r('company.index')],
                    ['key' => 'about-export', 'label' => 'Export & Import', 'target' => $r('services.show', 'export-import')],
                    ['key' => 'about-civil', 'label' => 'Civil & Construction Work', 'target' => $r('services.show', 'civil-construction')],
                    ['key' => 'about-b2b', 'label' => 'Egabe B2B', 'target' => $r('b2b.register')],
                    ['key' => 'about-app', 'label' => 'Mobile App', 'target' => $r('mobile-app')],
                    ['key' => 'about-contact', 'label' => 'Contact Us', 'target' => $r('support')],
                    ['key' => 'about-terms', 'label' => 'Terms & Conditions', 'target' => $r('terms')],
                ]],
            ],
            'footer' => [
                ['key' => 'quick-links', 'label' => 'Quick Links', 'children' => [
                    ['key' => 'quick-home', 'label' => 'Home', 'target' => $r('home')],
                    ['key' => 'quick-about', 'label' => 'About Us', 'target' => $r('about')],
                    ['key' => 'quick-contact', 'label' => 'Contact Us', 'target' => $r('support')],
                    ['key' => 'quick-terms', 'label' => 'Terms & Conditions', 'target' => $r('terms')],
                    ['key' => 'quick-map', 'label' => 'Map', 'target' => $r('map')],
                    ['key' => 'quick-app', 'label' => 'Mobile App', 'target' => $r('mobile-app')],
                    ['key' => 'quick-search', 'label' => 'Search', 'target' => $r('search')],
                ]],
                ['key' => 'footer-study', 'label' => 'Study in Singapore', 'children' => [
                    ['key' => 'footer-consultancy', 'label' => 'Student Consultancy', 'target' => $r('consultancy.singapore')],
                    ['key' => 'footer-guides', 'label' => 'Singapore Guides', 'target' => $r('consultancy.guides.index')],
                    ['key' => 'footer-institutions', 'label' => 'Institutions', 'target' => $r('consultancy.institutions.index')],
                    ['key' => 'footer-success', 'label' => 'Success Stories', 'target' => $r('success-stories.index')],
                    ['key' => 'footer-explore', 'label' => 'Explore Bangladesh', 'target' => $r('explore-bangladesh.index')],
                ]],
                ['key' => 'footer-services', 'label' => 'Our Services', 'children' => [
                    ['key' => 'footer-travel', 'label' => 'Travel Services', 'target' => $r('travel-services.index')],
                    ['key' => 'footer-air', 'label' => 'Air Ticket', 'target' => $r('services.show', 'air-ticket'), 'feature' => 'flights'],
                    ['key' => 'footer-hotels', 'label' => 'Hotels', 'target' => $r('hotels.index')],
                    ['key' => 'footer-tours', 'label' => 'Tours', 'target' => $r('tours.index')],
                    ['key' => 'footer-visa', 'label' => 'Visa', 'target' => $r('visa.index')],
                    ['key' => 'footer-work-visa', 'label' => 'Work Visa Processing', 'target' => $r('work-visa.index')],
                    ['key' => 'footer-enquiry', 'label' => 'Send an Enquiry', 'target' => $r('service-enquiries.create', 'tourist-visa')],
                ]],
            ],
            'home_tiles' => [
                ['key' => 'tile-study', 'label' => 'Study in Singapore', 'target' => $r('consultancy.singapore'), 'icon' => 'cap'],
                ['key' => 'tile-students-pass', 'label' => "Student's Pass (ICA)", 'target' => $r('consultancy.guides.index'), 'icon' => 'id'],
                ['key' => 'tile-visa', 'label' => 'Visa Services', 'target' => $r('visa.index'), 'icon' => 'passport'],
                ['key' => 'tile-flights', 'label' => 'Flight Services', 'target' => $r('services.show', 'air-ticket'), 'icon' => 'plane', 'feature' => 'flights'],
                ['key' => 'tile-hotels', 'label' => 'Hotels & Homes', 'target' => $r('hotels.index'), 'icon' => 'bed'],
                ['key' => 'tile-tours', 'label' => 'Tour Packages', 'target' => $r('tours.index'), 'icon' => 'mountain'],
                ['key' => 'tile-explore', 'label' => 'Explore Bangladesh', 'target' => $r('explore-bangladesh.index'), 'icon' => 'leaf'],
            ],
            'header_cta' => [
                ['key' => 'cta-apply', 'label' => 'Apply Now', 'target' => $r('consultancy.singapore'), 'fragment' => 'consultation'],
            ],
        ];
    }
}
