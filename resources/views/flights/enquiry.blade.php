@extends('layouts.site')
@section('title', 'Request a Flight Fare')
@section('robots', 'noindex,follow')
@section('content')
<main class="egho-shell" style="padding:32px 16px;max-width:850px">
    <h1>Request a Flight Fare</h1>
    <p>We will check availability and send a final quotation. No seat is reserved or ticket issued when you submit this form. Prices will be quoted in BDT, including applicable airline taxes. Agency or payment charges, if any, will be shown separately.</p>
    @if($errors->any())<div role="alert" style="color:#b91c1c"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('flights.enquiries.store') }}" style="display:grid;gap:16px;max-width:640px">
        @csrf
        <label>Trip type <select name="trip_type" required><option value="one_way" @selected(old('trip_type',$criteria['trip_type']??'one_way')==='one_way')>One way</option><option value="round_trip" @selected(old('trip_type',$criteria['trip_type']??'')==='round_trip')>Round trip</option></select></label>
        <label>From (IATA code) <input name="origin" maxlength="3" pattern="[A-Za-z]{3}" required placeholder="DAC" value="{{ old('origin',$criteria['origin']??'') }}"></label>
        <label>To (IATA code) <input name="destination" maxlength="3" pattern="[A-Za-z]{3}" required placeholder="KOL" value="{{ old('destination',$criteria['destination']??'') }}"></label>
        <label>Departure <input type="date" name="departure_date" min="{{ now()->toDateString() }}" required value="{{ old('departure_date',$criteria['departure_date']??'') }}"></label>
        <label>Return (round trip only) <input type="date" name="return_date" value="{{ old('return_date',$criteria['return_date']??'') }}"></label>
        <label>Adults <input type="number" name="adults" min="1" max="9" required value="{{ old('adults',$criteria['adults']??1) }}"></label>
        <label>Children <input type="number" name="children" min="0" max="8" required value="{{ old('children',$criteria['children']??0) }}"></label>
        <label>Infants <input type="number" name="infants" min="0" max="8" required value="{{ old('infants',$criteria['infants']??0) }}"></label>
        <label>Cabin <select name="cabin_class" required>@foreach(['economy'=>'Economy','premium_economy'=>'Premium Economy','business'=>'Business','first'=>'First Class'] as $value=>$label)<option value="{{ $value }}" @selected(old('cabin_class',$criteria['cabin_class']??'economy')===$value)>{{ $label }}</option>@endforeach</select></label>
        <label>Contact name <input name="contact_name" maxlength="150" required value="{{ old('contact_name',auth()->user()->name) }}"></label>
        <label>Contact email <input type="email" name="contact_email" required value="{{ old('contact_email',auth()->user()->email) }}"></label>
        <label>Phone number <input type="tel" name="contact_phone" maxlength="32" required value="{{ old('contact_phone') }}"></label>
        <label><input type="checkbox" name="consent" value="1" required @checked(old('consent'))> I agree to be contacted about my flight enquiry.</label>
        <button type="submit" class="site-button site-button-primary">Submit Flight Enquiry</button>
    </form>
</main>
@endsection
