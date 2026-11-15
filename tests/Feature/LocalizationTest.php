<?php

namespace Tests\Feature;

use Database\Seeders\CountrySeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CountrySeeder::class, ServiceDestinationSeeder::class]);
    }

    public function test_english_is_default_and_page_declares_hreflang_alternates(): void
    {
        $this->get(route('consultancy.singapore'))->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Study in Singapore from Bangladesh')
            ->assertSee('hreflang="bn" href="'.route('consultancy.singapore', ['lang' => 'bn']).'"', false)
            ->assertSee('hreflang="x-default"', false);
    }

    public function test_bengali_is_selected_by_query_and_remembered_in_session(): void
    {
        $this->get(route('consultancy.singapore', ['lang' => 'bn']))->assertOk()
            ->assertSee('<html lang="bn">', false)
            ->assertSee('বাংলাদেশ থেকে সিঙ্গাপুরে পড়াশোনা')
            ->assertSee('ইমিগ্রেশন অ্যান্ড চেকপয়েন্টস অথরিটি (ICA)')
            ->assertSessionHas('locale', 'bn');

        $this->get(route('consultancy.singapore'))->assertSee('<html lang="bn">', false);
        $this->get(route('consultancy.singapore', ['lang' => 'en']))->assertSee('<html lang="en">', false);
    }

    public function test_unsupported_language_is_ignored(): void
    {
        $this->get(route('consultancy.singapore', ['lang' => 'xx']))->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSessionMissing('locale');
    }
}
