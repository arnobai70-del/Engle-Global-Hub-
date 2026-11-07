@extends('layouts.site')
@section('title', 'Flight Enquiry '.$enquiry->reference)
@section('robots', 'noindex,nofollow')
@section('content')
<main class="egho-shell" style="padding:32px 16px;max-width:820px">
    <h1>Flight Enquiry Received</h1>
    @if(session('status'))<p role="status">{{ session('status') }}</p>@endif
    <p><strong>Reference:</strong> {{ $enquiry->reference }}</p>
    <p><strong>Route:</strong> {{ $enquiry->origin }} → {{ $enquiry->destination }}</p>
    <p><strong>Departure:</strong> {{ $enquiry->departure_date->toDateString() }}</p>
    <p><strong>Status:</strong> {{ str_replace('_',' ',ucwords($enquiry->status,'_')) }}</p>
    @if($enquiry->quoted_total_bdt)<p><strong>Manual quotation:</strong> BDT {{ number_format((float)$enquiry->quoted_total_bdt,2) }} — subject to final confirmation.</p>@else<p>Our team will confirm a tax-inclusive fare in BDT. No fare has been quoted yet.</p>@endif
    <p>Submitting this request does not create a booking, payment or issued ticket.</p>
    <a href="{{ route('flights.index') }}">Return to Flight Search</a>
</main>
@endsection
