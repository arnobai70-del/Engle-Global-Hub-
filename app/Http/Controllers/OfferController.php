<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function __invoke(): View
    {
        return view('offers.index', ['offers' => Offer::query()->current()->get()]);
    }
}
