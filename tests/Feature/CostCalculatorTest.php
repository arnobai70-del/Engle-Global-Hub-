<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Consultancy\MalaysiaCostCalculator;
use App\Services\SettingService;
use App\Services\SiteFacts;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CostCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculator_is_hidden_without_an_exchange_rate_or_any_amount(): void
    {
        $this->get(route('consultancy.malaysia.cost-calculator'))->assertRedirect(route('consultancy.malaysia').'#apply');

        $this->fact('my_cost_tuition', '20,000');
        $this->get(route('consultancy.malaysia.cost-calculator'))->assertRedirect(route('consultancy.malaysia').'#apply');

        $this->fact('my_cost_tuition', '');
        $this->fact('my_bdt_rate', '27.5');
        $this->get(route('consultancy.malaysia.cost-calculator'))->assertRedirect(route('consultancy.malaysia').'#apply');
    }

    public function test_calculator_converts_admin_amounts_with_the_admin_rate(): void
    {
        $this->fact('my_bdt_rate', '27.5');
        $this->fact('my_bdt_rate_date', '18 Nov 2026');
        $this->fact('my_cost_tuition', '20,000');
        $this->fact('my_cost_housing', 'MYR 6,000–8,000');
        $this->fact('my_cost_food', 'ask us');

        $this->get(route('consultancy.malaysia.cost-calculator'))->assertOk()
            ->assertSee('data-rate="27.5"', false)
            ->assertSee('BDT 715,000')
            ->assertSee('MYR 26,000')
            ->assertSee('BDT 715,000 – 770,000')
            ->assertSee('Rate used: 1 MYR = 27.5 BDT')
            ->assertSee('checked on 18 Nov 2026')
            ->assertSee('Housing (shared room)')
            ->assertDontSee('Food &amp; transport', false);
    }

    public function test_amount_parsing_keeps_ranges_and_rejects_text(): void
    {
        $calculator = app(MalaysiaCostCalculator::class);

        $this->assertSame([18000.0, 18000.0], $calculator->parseAmount('18,000'));
        $this->assertSame([18000.0, 25000.0], $calculator->parseAmount('18,000–25,000'));
        $this->assertNull($calculator->parseAmount('around twenty thousand'));
        $this->assertNull($calculator->parseAmount('1, 2 or 3'));
    }

    public function test_admin_can_set_the_rate_and_it_must_be_a_number(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)->patch(route('admin.facts.update'), ['my_bdt_rate' => 'about 27'])->assertSessionHasErrors('my_bdt_rate');
        $this->actingAs($admin)->patch(route('admin.facts.update'), ['my_bdt_rate' => '27.50'])->assertSessionHasNoErrors();
        $this->assertSame('27.50', app(SiteFacts::class)->get('my_bdt_rate'));
    }

    private function fact(string $key, string $value): void
    {
        app(SettingService::class)->set(SiteFacts::GROUP, $key, $value, 'string', true);
        app(SiteFacts::class)->forget();
    }
}
