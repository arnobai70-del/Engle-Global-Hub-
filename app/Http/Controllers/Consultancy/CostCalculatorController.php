<?php

namespace App\Http\Controllers\Consultancy;

use App\Http\Controllers\Controller;
use App\Services\Consultancy\MalaysiaCostCalculator;
use Illuminate\View\View;

/**
 * Study in Malaysia cost calculator: yearly MYR amounts and the MYR→BDT rate from
 * Admin › Business facts. The route redirects home while either is missing.
 */
class CostCalculatorController extends Controller
{
    public function __invoke(MalaysiaCostCalculator $calculator): View
    {
        return view('consultancy.cost-calculator', [
            'items' => $calculator->items(),
            'rate' => (float) $calculator->rate(),
            'rateDate' => $calculator->rateDate(),
            'totals' => $calculator->totals(),
        ]);
    }
}
