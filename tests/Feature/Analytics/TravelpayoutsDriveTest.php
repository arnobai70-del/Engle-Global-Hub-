<?php

namespace Tests\Feature\Analytics;

use Tests\TestCase;

final class TravelpayoutsDriveTest extends TestCase
{
    public function test_drive_code_is_present_once_in_the_head_of_public_pages(): void
    {
        foreach (['/', '/terms', '/work-visa'] as $path) {
            $response = $this->get($path)->assertOk();
            $html = $response->getContent();

            $this->assertSame(1, substr_count($html, 'https://tpembars.com/NTk1NTQ0.js?t=595544'));
            $this->assertLessThan(strpos($html, '</head>'), strpos($html, 'https://tpembars.com/NTk1NTQ0.js?t=595544'));
            $this->assertStringContainsString('data-cmp-ab="2"', $html);
        }
    }

    public function test_drive_does_not_load_in_login_and_admin_pages(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('tpembars.com/NTk1NTQ0.js', false);
    }
}
