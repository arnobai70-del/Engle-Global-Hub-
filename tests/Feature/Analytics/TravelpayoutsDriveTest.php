<?php

namespace Tests\Feature\Analytics;

use Tests\TestCase;

final class TravelpayoutsDriveTest extends TestCase
{
    public function test_travelpayouts_drive_is_absent_from_public_and_login_pages(): void
    {
        foreach (['/', '/terms', '/work-visa', '/login'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertDontSee('tpembars.com/NTk1NTQ0.js', false)
                ->assertDontSee('data-cmp-ab="2"', false);
        }
    }
}
