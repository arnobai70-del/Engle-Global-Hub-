<?php

namespace App\Http\Controllers\Flight;

use App\Http\Controllers\Controller;
use App\Http\Requests\Flight\SearchFlightRequest;
use App\Models\FlightEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class FlightEnquiryController extends Controller
{
    public function create(Request $request): View
    {
        return view('flights.enquiry', ['criteria' => $request->only([
            'origin', 'destination', 'departure_date', 'return_date', 'trip_type',
            'adults', 'children', 'infants', 'cabin_class',
        ])]);
    }

    public function store(SearchFlightRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $contact = $request->validate([
            'contact_name' => ['required', 'string', 'max:150'],
            'contact_email' => ['required', 'email', 'max:254'],
            'contact_phone' => ['required', 'string', 'min:7', 'max:32'],
            'consent' => ['accepted'],
        ]);

        $enquiry = FlightEnquiry::query()->create([
            ...$data,
            'user_id' => $request->user()->getAuthIdentifier(),
            'reference' => 'EGH-FQ-'.Str::upper(Str::random(12)),
            'contact_name' => $contact['contact_name'],
            'contact_email' => $contact['contact_email'],
            'contact_phone' => $contact['contact_phone'],
        ]);

        return redirect()->route('flights.enquiries.show', $enquiry)
            ->with('status', 'Your flight enquiry has been received. No ticket has been reserved or issued.');
    }

    public function show(Request $request, FlightEnquiry $enquiry): View
    {
        abort_unless((int) $enquiry->user_id === (int) $request->user()->getAuthIdentifier(), 403);
        return view('flights.enquiry-show', compact('enquiry'));
    }
}
