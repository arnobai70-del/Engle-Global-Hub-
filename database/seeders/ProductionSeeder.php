<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * The only seeder to run on a server that already holds live data:
 * php artisan db:seed --class=ProductionSeeder --force
 *
 * It adds what the new release needs and never overwrites live records:
 * - role permissions are merged (permissions granted on the live site are kept);
 * - countries are inserted only when missing (live names and on/off switches are kept);
 * - settings, team members, destinations and content are only created when missing,
 *   always unpublished, and are never published.
 *
 * Deliberately NOT run here: DatabaseSeeder (creates a local test user),
 * HomepageContentSeeder, CountrySeeder and CitySeeder (they overwrite live edits).
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->mergeRolePermissions();
        $this->addMissingCountries();

        $this->call([
            SettingsSeeder::class,
            HomepageSettingsSeeder::class,
            SeoSettingsSeeder::class,
            CompanyContentSettingsSeeder::class,
            ServiceDestinationSeeder::class,
            TeamMemberSeeder::class,
            BangladeshDestinationSeeder::class,
            SingaporeGuideSeeder::class,
            ContentDraftSeeder::class,
            StarterContentSeeder::class,
            MalaysiaGuidesSeeder::class,
            MenuSeeder::class,
            ExploreBangladeshMenuSeeder::class,
            MenuTidySeeder::class,
        ]);
    }

    /**
     * Creates the new roles and permissions, then gives back any permission a
     * role held before, so changes made in Admin › Roles & Permissions survive.
     */
    private function mergeRolePermissions(): void
    {
        $before = DB::table('role_has_permissions')->get(['role_id', 'permission_id']);

        $this->call(RolePermissionSeeder::class);

        DB::table('role_has_permissions')->insertOrIgnore(
            $before->map(fn (object $row): array => ['role_id' => $row->role_id, 'permission_id' => $row->permission_id])->all()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function addMissingCountries(): void
    {
        foreach (CountrySeeder::countries() as $country) {
            Country::query()->firstOrCreate(['iso2' => $country['iso2']], [...$country, 'is_active' => true]);
        }
    }
}
