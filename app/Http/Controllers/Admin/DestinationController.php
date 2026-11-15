<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveDestinationRequest;
use App\Models\Country;
use App\Models\Destination;
use App\Models\DestinationImage;
use App\Services\ActivityRecorder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('admin.destinations.index', [
            'destinations' => Destination::query()->with('country:id,name')->withCount('images')
                ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                ->orderBy('sort_order')->orderBy('name')->paginate(25)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.destinations.form', [
            'destination' => new Destination([
                'country_id' => Country::query()->where('iso2', 'BD')->value('id'),
                'sort_order' => (int) Destination::query()->max('sort_order') + 1,
            ]),
            'countries' => $this->countries(),
        ]);
    }

    public function store(SaveDestinationRequest $request): RedirectResponse
    {
        $destination = new Destination;
        $this->save($destination, $request);
        $this->activity->record($request->user(), 'destination.created', $destination, ['slug' => $destination->slug]);

        return redirect()->route('admin.destinations.edit', $destination)->with('status', 'Destination saved.');
    }

    public function edit(Destination $destination): View
    {
        return view('admin.destinations.form', [
            'destination' => $destination->load(['images', 'recommendations:id']),
            'countries' => $this->countries(),
            'otherDestinations' => Destination::query()->whereKeyNot($destination->getKey())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(SaveDestinationRequest $request, Destination $destination): RedirectResponse
    {
        $this->save($destination, $request);
        $this->activity->record($request->user(), 'destination.updated', $destination, [
            'slug' => $destination->slug,
            'published' => $destination->is_published,
        ]);

        return redirect()->route('admin.destinations.edit', $destination)->with('status', 'Destination updated.');
    }

    public function destroy(Request $request, Destination $destination): RedirectResponse
    {
        $paths = $destination->images()->pluck('path')->push($destination->cover_image_path)->filter()->all();
        $this->activity->record($request->user(), 'destination.deleted', $destination, ['slug' => $destination->slug]);
        $destination->delete();
        Storage::disk('public')->delete($paths);

        return redirect()->route('admin.destinations.index')->with('status', 'Destination deleted.');
    }

    public function destroyImage(Destination $destination, DestinationImage $image): RedirectResponse
    {
        abort_unless((int) $image->destination_id === (int) $destination->getKey(), 404);
        $image->delete();
        Storage::disk('public')->delete($image->path);

        return redirect()->route('admin.destinations.edit', $destination)->with('status', 'Image removed.');
    }

    private function save(Destination $destination, SaveDestinationRequest $request): void
    {
        $destination->fill($request->safe()->except(['cover_image', 'gallery_image', 'gallery_alt', 'recommended_ids']));
        $oldCover = $destination->cover_image_path;

        if ($request->hasFile('cover_image')) {
            $destination->cover_image_path = $request->file('cover_image')->store('destinations', 'public');
        }

        $destination->save();

        if ($oldCover !== null && $oldCover !== $destination->cover_image_path) {
            Storage::disk('public')->delete($oldCover);
        }

        if ($request->has('recommended_ids') || $request->boolean('recommendations_submitted')) {
            $destination->recommendations()->sync(collect($request->validated('recommended_ids') ?? [])->values()->mapWithKeys(fn ($id, int $index): array => [(int) $id => ['sort_order' => $index]])->all());
        }

        if ($request->hasFile('gallery_image')) {
            $destination->images()->create([
                'path' => $request->file('gallery_image')->store('destinations', 'public'),
                'alt_text' => $request->validated('gallery_alt'),
                'sort_order' => (int) $destination->images()->max('sort_order') + 1,
            ]);
        }
    }

    /**
     * @return Collection<int, Country>
     */
    private function countries(): Collection
    {
        return Country::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }
}
