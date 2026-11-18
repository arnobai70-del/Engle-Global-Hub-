<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Destination;
use App\Models\User;
use Database\Seeders\BangladeshDestinationSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExploreBangladeshTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, BangladeshDestinationSeeder::class]);
        Storage::fake('public');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('admin');

        return $user;
    }

    public function test_pdf_destinations_are_seeded_as_drafts_and_stay_hidden(): void
    {
        $expected = collect(BangladeshDestinationSeeder::destinations())->pluck('slug')->sort()->values()->all();
        $this->assertSame($expected, Destination::query()->orderBy('slug')->pluck('slug')->all());
        $this->assertSame(0, Destination::query()->where('is_published', true)->count());

        $this->seed(BangladeshDestinationSeeder::class);
        $this->assertSame(count($expected), Destination::query()->count());

        $this->get(route('explore-bangladesh.index'))->assertOk()
            ->assertSee('Our destination guides are being prepared')->assertDontSee("Cox's Bazar");
        $this->get(route('explore-bangladesh.show', 'sylhet'))->assertNotFound();
        $this->get(route('sitemap'))->assertDontSee(route('explore-bangladesh.index'), false);
    }

    public function test_publishing_requires_summary_and_cover_needs_alt_text(): void
    {
        $sylhet = Destination::query()->where('slug', 'sylhet')->sole();
        $payload = ['name' => 'Sylhet', 'slug' => 'sylhet', 'country_id' => $sylhet->country_id, 'sort_order' => 2];

        $this->actingAs($this->admin())->patch(route('admin.destinations.update', $sylhet), [...$payload, 'is_published' => '1'])
            ->assertSessionHasErrors('summary');
        $this->actingAs($this->admin())->patch(route('admin.destinations.update', $sylhet), [
            ...$payload, 'cover_image' => UploadedFile::fake()->image('tea.jpg', 800, 600),
        ])->assertSessionHasErrors('cover_image_alt');

        $this->assertFalse($sylhet->refresh()->is_published);
    }

    public function test_admin_publishes_destination_with_images_and_it_appears_publicly_with_seo(): void
    {
        $admin = $this->admin();
        $sylhet = Destination::query()->where('slug', 'sylhet')->sole();

        $this->actingAs($admin)->patch(route('admin.destinations.update', $sylhet), [
            'name' => 'Sylhet', 'slug' => 'sylhet', 'country_id' => $sylhet->country_id, 'sort_order' => 1,
            'region' => 'Sylhet Division', 'summary' => 'Owner verified summary.',
            'description' => "First paragraph.\n\nSecond <script>x</script> paragraph.",
            'meta_title' => 'Visit Sylhet', 'is_published' => '1',
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 800), 'cover_image_alt' => 'Tea garden hills',
            'gallery_image' => UploadedFile::fake()->image('g.jpg', 800, 600), 'gallery_alt' => 'Boat on the river',
        ])->assertRedirect(route('admin.destinations.edit', $sylhet));

        $sylhet->refresh();
        Storage::disk('public')->assertExists($sylhet->cover_image_path);
        $this->assertSame(1, $sylhet->images()->count());

        $this->get(route('explore-bangladesh.index'))->assertOk()->assertSee('Owner verified summary.')->assertSee('alt="Tea garden hills"', false);
        $this->get(route('explore-bangladesh.show', 'sylhet'))->assertOk()
            ->assertSee('<title>Visit Sylhet | Eagle Global Hub LTD</title>', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('alt="Boat on the river"', false)
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>x</script>', false);
        $this->get(route('sitemap'))->assertSee(route('explore-bangladesh.show', 'sylhet'), false);

        $image = $sylhet->images()->sole();
        $this->actingAs($admin)->delete(route('admin.destinations.images.destroy', [$sylhet, $image]))->assertRedirect();
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_admin_creates_destination_with_generated_unique_slug(): void
    {
        $bangladesh = Country::query()->where('iso2', 'BD')->value('id');

        $this->actingAs($this->admin())->post(route('admin.destinations.store'), [
            'name' => "St. Martin's Island", 'country_id' => $bangladesh, 'sort_order' => 4,
        ])->assertRedirect();
        $this->assertDatabaseHas('destinations', ['slug' => 'st-martins-island', 'is_published' => false]);

        $this->actingAs($this->admin())->post(route('admin.destinations.store'), [
            'name' => 'Another', 'slug' => 'sylhet', 'country_id' => $bangladesh, 'sort_order' => 5,
        ])->assertSessionHasErrors('slug');
    }

    public function test_customer_cannot_manage_destinations(): void
    {
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $customer->assignRole('customer');
        $sylhet = Destination::query()->where('slug', 'sylhet')->sole();

        $this->actingAs($customer)->get(route('admin.destinations.create'))->assertForbidden();
        $this->actingAs($customer)->patch(route('admin.destinations.update', $sylhet), ['name' => 'x'])->assertForbidden();
        $this->actingAs($customer)->delete(route('admin.destinations.destroy', $sylhet))->assertForbidden();
    }
}
