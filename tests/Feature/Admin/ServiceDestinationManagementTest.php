<?php

namespace Tests\Feature\Admin;

use App\Enums\BusinessService;
use App\Models\Country;
use App\Models\ServiceDestination;
use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceDestinationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, ServiceDestinationSeeder::class]);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    /** @return list<string> */
    private function codes(BusinessService $service): array
    {
        return ServiceDestination::query()->where('service', $service->value)->orderBy('sort_order')
            ->with('country')->get()->map(fn (ServiceDestination $destination): string => $destination->country->iso2)->all();
    }

    public function test_pdf_destination_lists_are_seeded_once_with_singapore_first_for_students(): void
    {
        $this->assertCount(21, $this->codes(BusinessService::TouristVisa));
        $this->assertSame(['SG', 'MY', 'BG', 'GB', 'JP', 'KR', 'CY'], $this->codes(BusinessService::StudentVisa));
        $this->assertSame(['AE', 'MY', 'JP', 'JO', 'SA', 'CA', 'BY'], $this->codes(BusinessService::WorkVisa));

        $total = ServiceDestination::query()->count();
        $this->seed(ServiceDestinationSeeder::class);
        $this->assertSame($total, ServiceDestination::query()->count());
    }

    public function test_admin_adds_hides_and_removes_destinations_without_duplicates(): void
    {
        $admin = $this->user('admin');
        $nepal = Country::query()->where('iso2', 'NP')->value('id');
        $singapore = Country::query()->where('iso2', 'SG')->value('id');

        $this->actingAs($admin)->get(route('admin.service-destinations.index'))
            ->assertOk()->assertSee('Tourist Visa destinations')->assertSee('Bike');

        $this->actingAs($admin)->post(route('admin.service-destinations.store'), ['service' => 'business_visa', 'country_id' => $nepal])
            ->assertRedirect();
        $this->actingAs($admin)->post(route('admin.service-destinations.store'), ['service' => 'student_visa', 'country_id' => $singapore])
            ->assertSessionHasErrors('country_id');
        $this->actingAs($admin)->post(route('admin.service-destinations.store'), ['service' => 'bike', 'country_id' => $nepal])
            ->assertSessionHasErrors('service');

        $this->assertSame(['NP'], $this->codes(BusinessService::BusinessVisa));

        $cyprus = ServiceDestination::query()->where('service', 'student_visa')
            ->whereHas('country', fn ($query) => $query->where('iso2', 'CY'))->sole();
        $this->actingAs($admin)->patch(route('admin.service-destinations.update', $cyprus), ['is_active' => 0, 'sort_order' => 9])->assertRedirect();
        $this->assertFalse($cyprus->refresh()->is_active);

        $this->get(route('consultancy.singapore'))->assertOk()->assertDontSee('Cyprus')->assertSee('Bulgaria');

        $this->actingAs($admin)->delete(route('admin.service-destinations.destroy', $cyprus))->assertRedirect();
        $this->assertDatabaseMissing('service_destinations', ['id' => $cyprus->id]);
    }

    public function test_customer_cannot_manage_destinations(): void
    {
        $customer = $this->user('customer');

        $this->actingAs($customer)->get(route('admin.service-destinations.index'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.service-destinations.store'), ['service' => 'tourist_visa', 'country_id' => 1])->assertForbidden();
    }
}
