<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BusinessService;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ServiceDestination;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceDestinationController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function index(): View
    {
        $destinations = ServiceDestination::query()->with('country:id,name,iso2')
            ->orderBy('sort_order')->orderBy('id')->get()
            ->groupBy(fn (ServiceDestination $destination): string => $destination->service->value);

        return view('admin.service-destinations.index', [
            'services' => BusinessService::cases(),
            'destinations' => $destinations,
            'countries' => Country::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'iso2']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $services = array_map(fn (BusinessService $service): string => $service->value, BusinessService::withDestinations());
        $data = $request->validate([
            'service' => ['required', Rule::in($services)],
            'country_id' => [
                'required', 'integer', Rule::exists('countries', 'id'),
                Rule::unique('service_destinations', 'country_id')->where('service', $request->input('service')),
            ],
        ], ['country_id.unique' => 'This country is already listed for that service.']);

        $destination = ServiceDestination::query()->create([
            ...$data,
            'sort_order' => (int) ServiceDestination::query()->where('service', $data['service'])->max('sort_order') + 1,
        ]);
        $this->activity->record($request->user(), 'service_destination.added', $destination, $data);

        return redirect()->route('admin.service-destinations.index')->with('status', 'Destination added.');
    }

    public function update(Request $request, ServiceDestination $serviceDestination): RedirectResponse
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);

        $serviceDestination->update($data);
        $this->activity->record($request->user(), 'service_destination.updated', $serviceDestination, ['active' => (bool) $data['is_active']]);

        return redirect()->route('admin.service-destinations.index')->with('status', 'Destination updated.');
    }

    public function destroy(Request $request, ServiceDestination $serviceDestination): RedirectResponse
    {
        $this->activity->record($request->user(), 'service_destination.removed', $serviceDestination, [
            'service' => $serviceDestination->service->value,
            'country_id' => $serviceDestination->country_id,
        ]);
        $serviceDestination->delete();

        return redirect()->route('admin.service-destinations.index')->with('status', 'Destination removed.');
    }
}
