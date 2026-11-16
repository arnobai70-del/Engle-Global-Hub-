<?php

namespace Tests\Feature;

use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BengaliPagesAndAuthSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class]);
    }

    public function test_auth_pages_are_noindex_with_a_clean_self_canonical(): void
    {
        foreach (['login', 'register', 'password.request'] as $routeName) {
            $this->get(route($routeName, ['lang' => 'bn']))->assertOk()
                ->assertSee('<meta name="robots" content="noindex,follow">', false)
                ->assertSee('<link rel="canonical" href="'.route($routeName).'">', false)
                ->assertSee('<meta property="og:url" content="'.route($routeName).'">', false)
                ->assertDontSee('&amp;amp;', false);
            $this->flushSession();
        }

        $this->get(route('password.reset', ['token' => 'secret-reset-token', 'email' => 'someone@example.com']))->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertDontSee('rel="canonical"', false)
            ->assertDontSee('og:url', false);
    }

    public function test_previously_english_only_pages_render_in_bengali(): void
    {
        $pages = [
            'visa.index' => ['ভিসা সহায়তা', 'ডেমো প্রিভিউ', 'আপনারা কি ভিসা অনুমোদনের নিশ্চয়তা দেন?'],
            'work-visa.index' => ['ওয়ার্ক ভিসা প্রসেসিং', 'দক্ষ কর্মী', 'কোনো নিশ্চয়তা নেই:'],
            'hotels.index' => ['প্রতিটি যাত্রার জন্য সঠিক থাকার জায়গা খুঁজুন', 'চেক-ইন'],
            'tours.index' => ['প্রতিটি গন্তব্যকে করে তুলুন অভিজ্ঞতা', 'শহর ভ্রমণ'],
            'support' => ['যোগাযোগের তথ্য', 'অফিসের ঠিকানা'],
            'terms' => ['গোপনীয়তা নোটিশ', 'বুকিং নিশ্চিতকরণ'],
            'about' => ['আমরা যেসব বিষয়ে সহায়তা করি', 'নিরাপদ অ্যাকাউন্ট'],
        ];

        foreach ($pages as $routeName => $expected) {
            $response = $this->get(route($routeName, ['lang' => 'bn']))->assertOk();
            foreach ($expected as $text) {
                $response->assertSee($text);
            }
            $this->flushSession();
        }

        $this->get(route('visa.index', ['lang' => 'bn']))->assertDontSee('VISA ASSISTANCE')->assertDontSee('Passport nationality');
    }

    public function test_validation_messages_follow_the_bengali_language(): void
    {
        $this->get(route('service-enquiries.create', ['service' => 'tourist-visa', 'lang' => 'bn']))->assertOk();

        $this->from(route('service-enquiries.create', 'tourist-visa'))
            ->post(route('service-enquiries.store', 'tourist-visa'), ['full_name' => '', 'email' => 'not-an-email'])
            ->assertSessionHasErrors([
                'full_name' => 'পূর্ণ নাম দেওয়া আবশ্যক।',
                'email' => 'ইমেইল অবশ্যই সঠিক ইমেইল ঠিকানা হতে হবে।',
                'consent' => 'আপনার অনুসন্ধানের বিষয়ে যোগাযোগের জন্য অনুগ্রহ করে সম্মতি দিন।',
            ]);
    }
}
