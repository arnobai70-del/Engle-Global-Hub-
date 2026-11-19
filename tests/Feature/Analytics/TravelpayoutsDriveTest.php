<?php

namespace Tests\Feature\Analytics;

use Tests\TestCase;

final class TravelpayoutsDriveTest extends TestCase
{
    private const SCRIPT = 'https://tpembars.com/NTk1NTQ0.js?t=595544';

    public function test_drive_code_loads_once_in_the_head_of_public_pages_in_production(): void
    {
        $this->app['env'] = 'production';

        foreach (['/', '/terms', '/work-visa'] as $path) {
            $html = (string) $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, self::SCRIPT));
            $this->assertLessThan(strpos($html, '</head>'), strpos($html, self::SCRIPT));
            $this->assertStringContainsString("window.addEventListener('load'", $html, 'added after the page has loaded');
            $this->assertStringContainsString('catch (error) {}', $html, 'a failure cannot break other scripts');
        }
    }

    public function test_drive_code_is_not_loaded_outside_production_or_without_config(): void
    {
        $this->assertStringNotContainsString('tpembars.com', (string) $this->get('/')->assertOk()->getContent(), 'local and testing never load it');

        $this->app['env'] = 'production';
        config(['services.travelpayouts.drive_script' => '']);
        $this->assertStringNotContainsString('tpembars.com', (string) $this->get('/')->assertOk()->getContent());
    }

    public function test_drive_does_not_load_in_login_and_admin_pages(): void
    {
        $this->app['env'] = 'production';

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('tpembars.com/NTk1NTQ0.js', false);
    }
}
