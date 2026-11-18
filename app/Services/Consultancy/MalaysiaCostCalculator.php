<?php

namespace App\Services\Consultancy;

use App\Services\SiteFacts;

/**
 * Yearly cost of studying in Malaysia in MYR and Taka, built only from Admin › Business facts:
 * the yearly MYR amounts and the MYR→BDT exchange rate. An amount entered as a range
 * ("18,000–25,000") stays a range. Without a rate or any amount the calculator is hidden.
 */
class MalaysiaCostCalculator
{
    public function __construct(private SiteFacts $facts) {}

    public function isAvailable(): bool
    {
        return $this->rate() !== null && $this->items() !== [];
    }

    public function rate(): ?float
    {
        $rate = $this->facts->get('my_bdt_rate');

        return $rate !== null && is_numeric($rate) && (float) $rate > 0 ? (float) $rate : null;
    }

    public function rateDate(): ?string
    {
        return $this->facts->get('my_bdt_rate_date');
    }

    /**
     * Yearly cost lines that have a usable amount.
     *
     * @return list<array{key: string, label: string, min: float, max: float}>
     */
    public function items(): array
    {
        $items = [];
        foreach ($this->labels() as $key => $label) {
            $amount = $this->parseAmount($this->facts->get($key));
            if ($amount !== null) {
                $items[] = ['key' => $key, 'label' => $label, 'min' => $amount[0], 'max' => $amount[1]];
            }
        }

        return $items;
    }

    /**
     * Totals for the given number of years, in MYR and BDT.
     *
     * @return array{myr_min: float, myr_max: float, bdt_min: float, bdt_max: float}
     */
    public function totals(int $years = 1): array
    {
        $rate = (float) $this->rate();
        $min = array_sum(array_column($this->items(), 'min')) * $years;
        $max = array_sum(array_column($this->items(), 'max')) * $years;

        return ['myr_min' => $min, 'myr_max' => $max, 'bdt_min' => $min * $rate, 'bdt_max' => $max * $rate];
    }

    /**
     * "18000", "18,000", "MYR 18,000" or "18,000–25,000" → [min, max]; anything else → null.
     *
     * @return array{0: float, 1: float}|null
     */
    public function parseAmount(?string $value): ?array
    {
        if ($value === null || preg_match_all('/\d[\d,]*(?:\.\d+)?/', $value, $matches) === 0 || count($matches[0]) > 2) {
            return null;
        }
        $numbers = array_map(static fn (string $number): float => (float) str_replace(',', '', $number), $matches[0]);
        if (min($numbers) <= 0) {
            return null;
        }

        return [min($numbers), max($numbers)];
    }

    /**
     * @return array<string, string>
     */
    private function labels(): array
    {
        return [
            'my_cost_tuition' => __('Tuition (diploma, private)'),
            'my_cost_housing' => __('Housing (shared room)'),
            'my_cost_food' => __('Food & transport'),
            'my_cost_visa' => __('Student Pass, insurance & medical'),
        ];
    }
}
