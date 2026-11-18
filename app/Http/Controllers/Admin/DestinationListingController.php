<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveDestinationListingRequest;
use App\Models\Destination;
use App\Models\DestinationListing;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Hotels, restaurants and experiences of a destination (Admin › Destinations › edit).
 * Staff enter only verified listings; nothing shows publicly until it is published.
 */
class DestinationListingController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function store(SaveDestinationListingRequest $request, Destination $destination): RedirectResponse
    {
        $listing = $destination->listings()->create($request->validated());
        $this->activity->record($request->user(), 'destination-listing.created', $listing, ['destination' => $destination->slug, 'type' => $listing->type]);

        return redirect()->to(route('admin.destinations.edit', $destination).'#listings')->with('status', 'Listing added.');
    }

    public function edit(Destination $destination, DestinationListing $listing): View
    {
        $this->ensureBelongs($destination, $listing);

        return view('admin.destinations.listing', ['destination' => $destination, 'listing' => $listing]);
    }

    public function update(SaveDestinationListingRequest $request, Destination $destination, DestinationListing $listing): RedirectResponse
    {
        $this->ensureBelongs($destination, $listing);
        $listing->update($request->validated());
        $this->activity->record($request->user(), 'destination-listing.updated', $listing, ['destination' => $destination->slug, 'published' => $listing->is_published]);

        return redirect()->to(route('admin.destinations.edit', $destination).'#listings')->with('status', 'Listing saved.');
    }

    public function destroy(Request $request, Destination $destination, DestinationListing $listing): RedirectResponse
    {
        $this->ensureBelongs($destination, $listing);
        $this->activity->record($request->user(), 'destination-listing.deleted', $listing, ['destination' => $destination->slug, 'name' => $listing->name]);
        $listing->delete();

        return redirect()->to(route('admin.destinations.edit', $destination).'#listings')->with('status', 'Listing deleted.');
    }

    private function ensureBelongs(Destination $destination, DestinationListing $listing): void
    {
        abort_unless((int) $listing->destination_id === (int) $destination->getKey(), 404);
    }
}
