<?php

namespace App\Http\Controllers;

use App\Enums\BusinessService;
use App\Http\Requests\StoreServiceEnquiryRequest;
use App\Models\ServiceEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceEnquiryController extends Controller
{
    public function create(string $service): View|RedirectResponse
    {
        // Business Class reuses the existing flight enquiry, which records the cabin class.
        if ($service === 'business-class') {
            return redirect()->route('flights.enquiries.create', ['cabin_class' => 'business']);
        }

        $businessService = ServiceEnquiry::serviceFromSlug($service);

        return view('service-enquiries.create', [
            'service' => $businessService,
            'destinations' => StoreServiceEnquiryRequest::destinationsFor($businessService),
            'services' => array_map(fn (string $value): BusinessService => BusinessService::from($value), ServiceEnquiry::SERVICES),
        ]);
    }

    public function store(StoreServiceEnquiryRequest $request, string $service): RedirectResponse
    {
        $data = $request->safe()->except(['consent', 'website']);

        $enquiry = ServiceEnquiry::query()->create([
            ...$data,
            'reference' => $this->newReference(),
            'service' => ServiceEnquiry::serviceFromSlug($service),
            'user_id' => $request->user()?->getKey(),
            'consented_at' => now(),
        ]);

        return redirect()->route('service-enquiries.create', $service)->with('enquiry_reference', $enquiry->reference);
    }

    /**
     * The signed-in customer's own enquiries (tourist portal).
     */
    public function mine(Request $request): View
    {
        return view('service-enquiries.mine', [
            'enquiries' => ServiceEnquiry::query()->where('user_id', $request->user()->getKey())
                ->with('destinationCountry:id,name')->latest('id')->paginate(20),
        ]);
    }

    private function newReference(): string
    {
        do {
            $reference = 'EGH-SQ-'.Str::upper(Str::random(10));
        } while (ServiceEnquiry::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
