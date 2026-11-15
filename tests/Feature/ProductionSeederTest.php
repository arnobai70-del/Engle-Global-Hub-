<?php

namespace Tests\Feature;

use App\Models\ContentEntry;
use App\Models\Country;
use App\Models\Destination;
use App\Models\HomepageBlock;
use App\Models\TeamMember;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeder_adds_new_records_without_overwriting_live_data(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Role::findByName('customer')->givePermissionTo('content.view');
        Country::query()->create(['name' => 'Bangladesh (live name)', 'iso2' => 'BD', 'iso3' => 'BGD', 'phone_code' => '+880', 'is_active' => false]);
        $block = HomepageBlock::query()->create(['section' => 'hero', 'key' => 'main', 'title' => 'Live homepage title', 'is_active' => true]);
        DB::table('settings')->insert(['group' => 'general', 'key' => 'site_name', 'value' => 'Live site name', 'type' => 'string', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->seed(ProductionSeeder::class);
        $counts = [DB::table('role_has_permissions')->count(), Country::query()->count(), ContentEntry::query()->count(), TeamMember::query()->count(), DB::table('settings')->count()];
        $this->seed(ProductionSeeder::class);

        $this->assertSame($counts, [DB::table('role_has_permissions')->count(), Country::query()->count(), ContentEntry::query()->count(), TeamMember::query()->count(), DB::table('settings')->count()]);
        $this->assertTrue(Role::findByName('customer')->hasPermissionTo('content.view'));
        $this->assertTrue(Role::findByName('super-admin')->hasPermissionTo('content.approve'));

        $bangladesh = Country::query()->where('iso2', 'BD')->sole();
        $this->assertSame('Bangladesh (live name)', $bangladesh->name);
        $this->assertFalse((bool) $bangladesh->is_active);
        $this->assertTrue(Country::query()->where('iso2', 'SG')->exists());

        $this->assertSame('Live homepage title', $block->fresh()->title);
        $this->assertSame('Live site name', DB::table('settings')->where('group', 'general')->where('key', 'site_name')->value('value'));

        $this->assertFalse(User::query()->where('email', 'test@example.com')->exists());
        $this->assertSame(0, ContentEntry::query()->where('status', ContentEntry::STATUS_PUBLISHED)->count());
        $this->assertSame(0, TeamMember::query()->where('is_published', true)->count());
        $this->assertSame(0, Destination::query()->where('is_published', true)->count());
    }
}
