<?php

namespace Tests\Feature;

use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_logo_loads_through_the_uploads_route_when_the_storage_link_is_missing(): void
    {
        $this->assertSame(url('/uploads'), config('filesystems.disks.public.url'), 'public/storage is a plain folder in this repo, so the fallback is active');

        Storage::fake('public', ['url' => config('filesystems.disks.public.url')]);
        Storage::disk('public')->put('homepage/site/logo.png', 'PNG-BYTES');
        app(SettingService::class)->set('general', 'logo_path', 'homepage/site/logo.png', 'string', true);

        $html = (string) $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('class="sg-brand-logo" src="'.url('/uploads/homepage/site/logo.png').'"', $html);
        $this->assertStringContainsString("this.src='".asset('images/eagle-global-hub-logo.png')."'", $html);

        $response = $this->get('/uploads/homepage/site/logo.png')->assertOk();
        $this->assertSame('PNG-BYTES', $response->streamedContent());
    }

    public function test_uploads_route_serves_nothing_hidden_missing_or_outside_the_public_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('.secret', 'x');

        $this->get('/uploads/.secret')->assertNotFound();
        $this->get('/uploads/missing.png')->assertNotFound();
        $this->get('/uploads/..%2F..%2F.env')->assertNotFound();
    }
}
