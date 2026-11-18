{{--
    "Plan my trip" enquiry for foreign visitors (saved as Foreigner support › Travel assistance).
    @var \Illuminate\Support\Collection<int, \App\Models\Destination> $tripPlaces  published Bangladesh destinations
    @var \Illuminate\Support\Collection<int, \App\Models\Country> $countries
    @var list<int> $selected  destination ids to pre-select
    @var string $title
--}}
@php
    $chosenPlaces = array_map('intval', (array) old('destination_ids', $selected));
    $chosenNeeds = (array) old('needs', []);
@endphp
<div class="v2-form-card v2-form v2-explore-form">
    <form method="POST" action="{{ route('explore-bangladesh.enquire') }}" novalidate>
        @csrf
        <h2 class="v2-h3">{{ $title }}</h2>
        <p class="v2-tool-note">{{ __('Tell us your plans. Our travel team replies by email or WhatsApp with options for hotels, transport and guides.') }}</p>
        <x-site.lead-success />
        @if($errors->any())<ul class="v2-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif

        <div class="v2-row-2">
            <label class="v2-field" for="trip-name">{{ __('Full name') }}<input id="trip-name" name="full_name" autocomplete="name" required maxlength="150" value="{{ old('full_name') }}"></label>
            <label class="v2-field" for="trip-email">{{ __('Email') }}<input id="trip-email" name="email" type="email" autocomplete="email" required maxlength="254" value="{{ old('email') }}"></label>
        </div>
        <div class="v2-row-2">
            <label class="v2-field" for="trip-phone">{{ __('Phone / WhatsApp (with country code)') }}<input id="trip-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required maxlength="32" value="{{ old('phone') }}" placeholder="+44 7700 900123"></label>
            <label class="v2-field" for="trip-country">{{ __('Country of residence') }}
                <select id="trip-country" name="residence_country_id" autocomplete="country"><option value="">{{ __('Choose one') }}</option>@foreach($countries as $country)<option value="{{ $country->id }}" @selected((string) old('residence_country_id') === (string) $country->id)>{{ __($country->name) }}</option>@endforeach</select>
            </label>
        </div>
        <div class="v2-row-3">
            <label class="v2-field" for="trip-from">{{ __('Arrival date') }}<input id="trip-from" name="travel_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('travel_date') }}"></label>
            <label class="v2-field" for="trip-to">{{ __('Departure date') }}<input id="trip-to" name="return_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('return_date') }}"></label>
            <label class="v2-field" for="trip-travellers">{{ __('Travellers') }}<input id="trip-travellers" name="travellers" type="number" min="1" max="20" required value="{{ old('travellers', 1) }}"></label>
        </div>
        @if($tripPlaces->isNotEmpty())
        <fieldset class="v2-explore-checks">
            <legend>{{ __('Places you would like to visit') }}</legend>
            @foreach($tripPlaces as $place)
                <label class="v2-check"><input type="checkbox" name="destination_ids[]" value="{{ $place->id }}" @checked(in_array($place->id, $chosenPlaces, true))><span>{{ $place->localized('name') }}</span></label>
            @endforeach
        </fieldset>
        @endif
        <fieldset class="v2-explore-checks">
            <legend>{{ __('What should we arrange?') }}</legend>
            @foreach(\App\Http\Requests\StoreTripEnquiryRequest::NEEDS as $value => $label)
                <label class="v2-check"><input type="checkbox" name="needs[]" value="{{ $value }}" @checked(in_array($value, $chosenNeeds, true))><span>{{ __($label) }}</span></label>
            @endforeach
        </fieldset>
        <label class="v2-field" for="trip-message">{{ __('Anything else? (optional)') }}<textarea id="trip-message" name="message" rows="3" maxlength="2000">{{ old('message') }}</textarea></label>
        <label class="v2-hp" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
        <label class="v2-check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))><span>{{ __('I agree to be contacted about my enquiry.') }} <a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a></span></label>
        <button type="submit" class="v2-btn v2-btn-red v2-btn-block">{{ __('Send my trip request') }}</button>
    </form>
</div>
