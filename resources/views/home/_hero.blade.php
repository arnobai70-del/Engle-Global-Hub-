<section class="egho-hero" style="--egho-hero-image: url('{{ $heroImage }}')">
    <div class="egho-shell">
        <div class="egho-hero-copy">
            <span class="egho-hero-eyebrow">{{ data_get($settings, 'hero_eyebrow') }}</span>
            <h1>{{ data_get($settings, 'hero_title', 'Travel the World with') }} <span class="egho-hero-accent">{{ data_get($settings, 'hero_accent', 'Eagle Global Hub') }}</span></h1>
            <p>{{ data_get($settings, 'hero_subtitle') }}</p>
        </div>
    </div>
    @if (data_get($settings, 'hero_side_text'))
        <div class="egho-hero-aside" aria-hidden="true"><span class="egho-hero-script">{!! nl2br(e(data_get($settings, 'hero_side_text'))) !!}</span></div>
    @endif
</section>

@feature('flights')
<section class="egho-search-wrap" aria-label="Flight search">
    <div class="egho-shell"><div class="egho-search-card">
        <form method="{{ $flightSearchMethod }}" action="{{ $flightSearchAction }}" class="egho-search-form" @if ($flightSearchMethod === 'POST') data-flight-search-form @endif>
            @if ($flightSearchMethod === 'POST') @csrf @endif
            <input type="hidden" name="children" value="0"><input type="hidden" name="infants" value="0">
            <div class="egho-search-head">
                <div class="egho-search-tabs" aria-label="Travel search services">
                    <span class="egho-search-tab is-active" aria-current="page"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 13.5 21 5l-3.5 8.5L21 19z"/><path d="M8.5 12.2 3 13.5"/></svg>Flights</span>
                    @foreach ([['key'=>'hotels','label'=>'Hotels'],['key'=>'tours','label'=>'Tours & Activities'],['key'=>'visa','label'=>'Visa']] as $tab)
                        @feature($tab['key'])
                            @php $tabLink = $serviceLink($tab['key']); @endphp
                            @if ($tabLink)<a class="egho-search-tab" href="{{ $tabLink }}">{{ $tab['label'] }}</a>@else<span class="egho-search-tab" title="Available when the provider is configured">{{ $tab['label'] }}</span>@endif
                        @endfeature
                    @endforeach
                    @feature('visa')<a class="egho-search-tab" href="{{ route('work-visa.index') }}">Work Visa</a>@endfeature
                </div>
                <fieldset class="egho-trip-type"><legend>Trip type</legend><label><input type="radio" name="trip_type" value="round_trip" checked><span>Round Trip</span></label><label><input type="radio" name="trip_type" value="one_way"><span>One Way</span></label></fieldset>
            </div>
            <div class="egho-search-grid">
                <div class="egho-field-pair">
                    <label class="egho-field"><span>From</span><input type="text" name="origin" maxlength="3" minlength="3" pattern="[A-Za-z]{3}" placeholder="DAC" autocomplete="off" required data-airport-code aria-label="Departure airport code"></label>
                    <button type="button" class="egho-swap" data-flight-swap aria-label="Swap origin and destination" title="Swap origin and destination" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h15l-3-3"/><path d="M20 16H5l3 3"/></svg></button>
                    <label class="egho-field"><span>To</span><input type="text" name="destination" maxlength="3" minlength="3" pattern="[A-Za-z]{3}" placeholder="DXB" autocomplete="off" required data-airport-code aria-label="Destination airport code"></label>
                </div>
                <label class="egho-field"><span>Departure</span><input type="date" name="departure_date" min="{{ now()->toDateString() }}" data-departure-date required></label>
                <label class="egho-field"><span>Return</span><input type="date" name="return_date" min="{{ now()->addDay()->toDateString() }}" data-return-date required></label>
                <div class="egho-field-group"><span class="egho-field-group-label">Travellers &amp; Class</span><select name="adults" aria-label="Travellers">@for ($i=1;$i<=6;$i++)<option value="{{ $i }}">{{ $i }} {{ $i===1 ? 'Traveller' : 'Travellers' }}</option>@endfor</select><select name="cabin_class" aria-label="Cabin class"><option value="economy">Economy</option><option value="premium_economy">Premium Economy</option><option value="business">Business</option><option value="first">First Class</option></select></div>
                <button type="submit" class="egho-submit" data-flight-submit><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>Search Flights</button>
            </div>
            @if ($flightSearchMethod === 'POST')
                <div class="flight-status" data-flight-status role="status" aria-live="polite" hidden></div>
                <div class="flight-results" data-flight-results
                    data-flight-select-url="{{ route('flights.offers.select') }}"
                    data-flight-traveler-validation-url="{{ route('flights.travelers.validate') }}"
                    data-flight-booking-draft-url="{{ route('flights.bookings.drafts.store') }}"
                    data-flight-booking-draft-review-url="{{ route('flights.bookings.drafts.review') }}"
                    data-flight-booking-confirmation-intent-url="{{ route('flights.bookings.confirmation-intents.store') }}"
                    data-flight-order-execution-url="{{ route('flights.bookings.orders.execute') }}"
                    data-flight-order-attempt-status-url-template="{{ route('flights.bookings.orders.attempts.show', ['attemptReference'=>'__ATTEMPT_REFERENCE__']) }}"
                    data-flight-order-reconciliation-url-template="{{ route('flights.bookings.orders.attempts.reconcile', ['attemptReference'=>'__ATTEMPT_REFERENCE__']) }}"
                    @feature('payments')
                    data-flight-payment-readiness-url-template="{{ route('flights.bookings.orders.attempts.payment-readiness.show', ['attemptReference'=>'__ATTEMPT_REFERENCE__']) }}"
                    data-flight-payment-execution-url-template="{{ route('flights.bookings.orders.attempts.payments.store', ['attemptReference'=>'__ATTEMPT_REFERENCE__']) }}"
                    data-flight-payment-attempt-status-url-template="{{ route('flights.bookings.orders.payments.attempts.show', ['attemptReference'=>'__ATTEMPT_REFERENCE__']) }}"
                    data-flight-payment-reconciliation-url-template="{{ route('flights.bookings.orders.payments.attempts.reconcile', ['attemptReference'=>'__ATTEMPT_REFERENCE__']) }}"
                    @endfeature
                    data-flight-order-confirmation-url-template="{{ route('flights.bookings.orders.attempts.confirmation.show', ['attemptReference'=>'__ATTEMPT_REFERENCE__']) }}" aria-live="polite" hidden></div>
            @endif
        </form>
    </div></div>
</section>
@endfeature
