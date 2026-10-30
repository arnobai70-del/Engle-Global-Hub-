<?php

namespace Tests\Feature\Admin;

use App\Models\HomepageBlock;
use App\Services\HomepageContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomepageDefaultImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_is_dry_run_by_default_and_preserves_existing_records(): void
    {
        $expected = count(app(HomepageContentService::class)->defaultEditableBlocks());
        $this->assertGreaterThan(0, $expected);

        $this->artisan('homepage:import-default-blocks')->assertSuccessful();
        $this->assertSame(0, HomepageBlock::count());

        HomepageBlock::query()->create([
            'section' => 'service',
            'key' => 'flights',
            'title' => 'Custom flights title',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $this->artisan('homepage:import-default-blocks', ['--apply' => true])->assertSuccessful();
        $this->assertSame($expected, HomepageBlock::count());
        $this->assertSame('Custom flights title', HomepageBlock::query()->where('section', 'service')->where('key', 'flights')->value('title'));

        $this->artisan('homepage:import-default-blocks', ['--apply' => true])->assertSuccessful();
        $this->assertSame($expected, HomepageBlock::count());
    }

    public function test_import_keeps_public_copy_and_destination_metadata(): void
    {
        $this->artisan('homepage:import-default-blocks', ['--apply' => true])->assertSuccessful();

        $service = app(HomepageContentService::class);
        $home = $service->home();

        $this->assertSame('UAE', $home['destinations'][0]['country']);
        $this->assertSame('Dubai', $home['destinations'][0]['tag']);
        $this->assertSame('Domestic & International', $home['services'][0]['copy']);
        $this->assertSame('Flights', $home['promotions'][0]['badge']);
        $this->assertSame('FLAT 20% OFF on International Flights', $home['promotions'][0]['title']);
        $this->assertNotEmpty($home['assurances'][0]['icon']);
    }
}
