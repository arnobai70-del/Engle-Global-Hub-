<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BusinessService;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ServiceDestination;
use App\Models\StudyDestination;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
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
            // New Student Visa countries can show an enquiry card immediately,
            // while the study-country page remains a draft until verified.
            'show_on_homepage' => $data['service'] === BusinessService::StudentVisa->value,
            'sort_order' => (int) ServiceDestination::query()->where('service', $data['service'])->max('sort_order') + 1,
        ]);

        if ($data['service'] === BusinessService::StudentVisa->value) {
            $this->ensureDraftStudyPage(Country::query()->findOrFail($data['country_id']));
        }
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

    public function homepage(Request $request, ServiceDestination $serviceDestination): RedirectResponse
    {
        abort_unless($serviceDestination->service === BusinessService::StudentVisa, 404);
        $data = $request->validate(['show_on_homepage' => ['required', 'boolean']]);

        if ((bool) $data['show_on_homepage'] && ! $serviceDestination->is_active) {
            return back()->withErrors(['show_on_homepage' => 'Activate this destination first.']);
        }

        $serviceDestination->update(['show_on_homepage' => (bool) $data['show_on_homepage']]);
        if ($serviceDestination->show_on_homepage) {
            $this->ensureDraftStudyPage($serviceDestination->country);
        }
        $this->activity->record($request->user(), 'service_destination.homepage', $serviceDestination, [
            'visible' => $serviceDestination->show_on_homepage,
        ]);

        return back()->with('status', 'Homepage country card setting saved.');
    }

    /**
     * The same full study-country CMS template for every country (except the
     * legacy Malaysia / Singapore pages). Never publish an empty draft.
     */
    private function ensureDraftStudyPage(Country $country): void
    {
        if (in_array($country->iso2, ['MY', 'SG'], true)
            || StudyDestination::query()->where('country_id', $country->id)->exists()) {
            return;
        }

        $base = Str::slug($country->name);
        if ($base === '' || strlen($base) > 70) {
            $base = strtolower($country->iso2);
        }
        $slug = StudyDestination::query()->where('slug', $base)->exists()
            ? substr($base, 0, 70).'-'.strtolower($country->iso2)
            : $base;

        StudyDestination::query()->firstOrCreate(
            ['country_id' => $country->id],
            ['slug' => $slug, 'status' => StudyDestination::STATUS_DRAFT]
        );
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
