<?php

namespace Tests\Feature\Admin;

use App\Enums\BusinessService;
use App\Models\Country;
use App\Models\ServiceDestination;
use App\Models\StudyDestination;
use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Database\Seeders\StudyDestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyDestinationAdminTest extends TestCase
{
    use RefreshDatabase;

    private StudyDestination $japan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, ServiceDestinationSeeder::class, StudyDestinationSeeder::class]);
        $this->japan = StudyDestination::query()->where('slug', 'japan')->firstOrFail();
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    /** @return array<string, mixed> */
    private function completePayload(): array
    {
        $sourceId = $this->japan->items()->where('section', 'source')->value('id');

        return [
            'slug' => 'japan', 'name_bn' => 'জাপান',
            'intro' => 'Owner-written introduction.',
            'visa_name' => 'Student visa', 'processing_time' => '', 'currency_code' => 'jpy', 'bdt_rate' => '0.80', 'bdt_rate_checked_on' => now()->toDateString(),
            'items' => [
                'requirement' => [['title' => 'Academic', 'body' => 'Higher secondary.', 'source_url' => 'https://www.mofa.go.jp/j_info/visit/visa/', 'source_label' => 'MOFA']],
                'visa_step' => [['title' => 'Certificate of Eligibility', 'body' => 'The school applies.', 'source_url' => 'https://www.isa.go.jp/en/', 'source_label' => 'ISA']],
                'work_rule' => [['title' => 'Part-time work', 'body' => 'Rule from the official page.']],
                'source' => [['id' => $sourceId, 'source_url' => 'https://www.isa.go.jp/en/', 'source_label' => 'Immigration Services Agency of Japan', 'needs_verification' => '0']],
            ],
        ];
    }

    public function test_a_content_manager_can_edit_and_publish_a_complete_destination(): void
    {
        $editor = $this->user('content-editor');

        $this->actingAs($editor)->get(route('admin.study-destinations.index'))->assertOk()->assertSee('Study destinations')->assertSee('/study-in-japan');
        $this->actingAs($editor)->get(route('admin.study-destinations.edit', $this->japan))->assertOk()
            ->assertSee('Verify before publishing')->assertSee('data-publish-blockers', false);

        $this->actingAs($editor)->patch(route('admin.study-destinations.status', $this->japan), ['status' => 'published'])->assertSessionHasErrors('status');
        $this->assertSame(StudyDestination::STATUS_DRAFT, $this->japan->refresh()->status);

        $this->actingAs($editor)->patch(route('admin.study-destinations.update', $this->japan), $this->completePayload())
            ->assertRedirect(route('admin.study-destinations.edit', $this->japan))
            ->assertSessionHas('status', fn (string $status): bool => str_contains($status, '1 fact(s) have no source'));

        $this->japan->refresh();
        $this->assertSame('JPY', $this->japan->currency_code);
        $this->assertSame(['Part-time work'], $this->japan->factsWithoutSource()->pluck('title')->all());
        $this->assertTrue($this->japan->isComplete());

        $this->actingAs($editor)->patch(route('admin.study-destinations.status', $this->japan), ['status' => 'published'])->assertSessionHasNoErrors();
        $this->get(route('study-destinations.show', 'japan'))->assertOk()->assertSee('Owner-written introduction.');
    }

    public function test_guests_are_sent_to_sign_in_and_roles_without_the_permission_are_forbidden(): void
    {
        $this->get(route('admin.study-destinations.index'))->assertRedirect(route('login'));
        $this->patch(route('admin.study-destinations.update', $this->japan), $this->completePayload())->assertRedirect(route('login'));

        $this->actingAs($this->user('crm-staff'))->get(route('admin.study-destinations.index'))->assertForbidden();

        $viewer = $this->user('seo-manager');
        $this->actingAs($viewer)->get(route('admin.study-destinations.index'))->assertOk();
        $this->actingAs($viewer)->get(route('admin.study-destinations.edit', $this->japan))->assertForbidden();
        $this->actingAs($viewer)->patch(route('admin.study-destinations.update', $this->japan), $this->completePayload())->assertForbidden();
        $this->actingAs($viewer)->patch(route('admin.study-destinations.status', $this->japan), ['status' => 'published'])->assertForbidden();
        $this->assertNull($this->japan->refresh()->intro);

        $this->actingAs($this->user('customer'))->get(route('admin.study-destinations.index'))->assertForbidden();
    }

    public function test_invalid_values_are_rejected(): void
    {
        $editor = $this->user('content-editor');
        $payload = $this->completePayload();

        $this->actingAs($editor)->patch(route('admin.study-destinations.update', $this->japan), [...$payload, 'slug' => 'malaysia'])->assertSessionHasErrors('slug');
        $this->actingAs($editor)->patch(route('admin.study-destinations.update', $this->japan), [...$payload, 'slug' => 'Japan Page'])->assertSessionHasErrors('slug');
        $payload['items']['requirement'][0]['source_url'] = 'http://insecure.example';
        $this->actingAs($editor)->patch(route('admin.study-destinations.update', $this->japan), $payload)->assertSessionHasErrors('items.requirement.0.source_url');
    }

    public function test_reordering_uses_the_existing_student_visa_order(): void
    {
        $editor = $this->user('content-editor');
        $order = fn (): array => ServiceDestination::query()->where('service', BusinessService::StudentVisa->value)->orderBy('sort_order')->orderBy('id')
            ->with('country')->get()->pluck('country.iso2')->all();
        $before = $order();
        $japanIndex = array_search('JP', $before, true);

        $this->actingAs($editor)->patch(route('admin.study-destinations.move', $this->japan), ['direction' => 'up'])->assertSessionHasNoErrors();

        $after = $order();
        $this->assertSame('JP', $after[$japanIndex - 1]);
        $this->assertSame($before[$japanIndex - 1], $after[$japanIndex]);
        $this->assertCount(count($before), $after, 'no rows added');
        $this->assertSame(0, StudyDestination::query()->where('country_id', Country::query()->where('iso2', 'MY')->value('id'))->count(), 'Malaysia keeps its own page');
    }
}
