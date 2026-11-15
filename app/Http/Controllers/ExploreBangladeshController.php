<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\View\View;

class ExploreBangladeshController extends Controller
{
    public function index(): View
    {
        return view('explore.index', [
            'destinations' => Destination::query()->published()
                ->whereHas('country', fn ($query) => $query->where('iso2', 'BD'))
                ->get(['id', 'name', 'slug', 'region', 'summary', 'cover_image_path', 'cover_image_alt']),
        ]);
    }

    public function show(string $slug): View
    {
        $destination = Destination::query()->published()->where('slug', $slug)
            ->whereHas('country', fn ($query) => $query->where('iso2', 'BD'))
            ->with(['images', 'recommendations' => fn ($query) => $query->where('is_published', true)])->firstOrFail();

        return view('explore.show', compact('destination'));
    }
}
