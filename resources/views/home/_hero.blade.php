{{--
    Hero and flight search card.

    Receives `$heroImage`, `$flightSearchAction` and `$flightSearchMethod` from
    `home.blade.php`. The search form keeps every hook the signed-in search
    script needs: one `[data-flight-search-form]`, the trip-type radios inside
    it, the two airport fields, both dates and the results container with its
    endpoint templates.
--}}
<section
    class="egho-hero"
    style="--egho-hero-image: url('{{ $heroImage }}')"
>
    <div class="egho-shell">
        <div class="egho-hero-copy">

            @feature('flights')
                <span class="egho-hero-eyebrow">
                    Explore <span class="egho-hero-mark">&middot;</span>
                    Book <span class="egho-hero-mark">&middot;</span>
                    Travel <span class="egho-hero-mark">&middot;</span>
                    Grow
                </span>

                <h1>
                    Travel the World with
                    <span class="egho-hero-accent">Eagle Global Hub</span>
                </h1>

                <p>
                    Flights, Hotels, Tours, Visa, Work Visa Processing
                    and more — search, compare and manage every step
                    from one secure account.
                </p>

                <ul class="egho-hero-points">
                    <li>
                        <span aria-hidden="true">&#10003;</span>
                        Compare fares and itineraries
                    </li>

                    <li>
                        <span aria-hidden="true">&#10003;</span>
                        Secure account &amp; booking steps
                    </li>

                    <li>
                        <span aria-hidden="true">&#10003;</span>
                        Visible order and payment status
                    </li>
                </ul>
            @else
                <span class="egho-hero-eyebrow">
                    Eagle Global Hub LTD
                </span>

                <h1>
                    Travel services
                    <span class="egho-hero-accent">under one account</span>
                </h1>

                <p>
                    Use the currently available services listed below.
                </p>
            @endfeature

        </div>
    </div>

    {{--
        Panel caption and aircraft.

        Both are decoration that repeats the approved panel. They are hidden
        from assistive technology and carry no claim, and the aircraft is a
        drawn silhouette rather than licensed artwork.
    --}}
    <div class="egho-hero-aside" aria-hidden="true">
        <span class="egho-hero-script">
            Discover<br>
            Explore<br>
            Work<br>
            Study<br>
            Travel Beyond<br>
            Boundaries
        </span>

        <svg class="egho-hero-plane" viewBox="0 0 320 130">
            <path d="M6 84c0-6 4-9 11-11l96-28 60-40c5-4 12-6 19-6 6 0 10 3 10 8s-4 9-10 13l-46 30 92 26c4 1 6 3 6 6 0 5-6 9-15 9h-58l-30 30c-3 3-6 4-10 4-5 0-8-3-8-8l2-24-96-6c-4 0-6-1-6-3z"/>
        </svg>
    </div>
</section>

@feature('flights')
    <section class="egho-search-wrap">
        <div class="egho-shell">
            <div class="egho-search-card">

                <form
                    method="{{ $flightSearchMethod }}"
                    action="{{ $flightSearchAction }}"
                    class="egho-search-form"
                    aria-label="Flight search"
                    @if ($flightSearchMethod === 'POST')
                        data-flight-search-form
                    @endif
                >
                    @if ($flightSearchMethod === 'POST')
                        @csrf
                    @endif

                    <input type="hidden" name="children" value="0">
                    <input type="hidden" name="infants" value="0">

                    <div class="egho-search-head">

                        {{--
                            Navigation pills, not ARIA tabs: each pill simply
                            moves to another search surface, so tab semantics
                            (aria-controls, roving focus) would be inaccurate.

                            The panel lists ten surfaces. Only the ones this
                            website actually has are rendered here; the rest
                            would be pills that lead nowhere.
                        --}}
                        <div class="egho-search-tabs" aria-label="Travel search">
                            <span class="egho-search-tab is-active" aria-current="true">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 13.5 21 5l-3.5 8.5L21 19z"/>
                                    <path d="M8.5 12.2 3 13.5"/>
                                </svg>
                                Flights
                            </span>

                            @php
                                $searchTabs = [
                                    [
                                        'key' => 'hotels',
                                        'label' => 'Hotels',
                                        'icon' => '<path d="M4 20V9m0 5h16v6M4 9l8-5 8 5"/><path d="M9.5 14.5v-2h5v2"/>',
                                    ],
                                    [
                                        'key' => 'tours',
                                        'label' => 'Tours & Activities',
                                        'icon' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.4 2.5 3.6 5.5 3.6 9s-1.2 6.5-3.6 9c-2.4-2.5-3.6-5.5-3.6-9S9.6 5.5 12 3z"/>',
                                    ],
                                    [
                                        'key' => 'visa',
                                        'label' => 'Visa',
                                        'icon' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14.5 3v4.5H19"/><path d="M9 13h6M9 16.5h4"/>',
                                    ],
                                ];
                            @endphp

                            @foreach ($searchTabs as $tab)
                                @feature($tab['key'])
                                    @php $tabLink = $serviceLink($tab['key']); @endphp

                                    @if ($tabLink)
                                        <a
                                            class="egho-search-tab"
                                            href="{{ $tabLink }}"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                {!! $tab['icon'] !!}
                                            </svg>
                                            {{ $tab['label'] }}
                                        </a>
                                    @else
                                        <span
                                            class="egho-search-tab"
                                            title="{{ $tab['label'] }} search becomes available once the provider is configured"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                {!! $tab['icon'] !!}
                                            </svg>
                                            {{ $tab['label'] }}
                                        </span>
                                    @endif
                                @endfeature
                            @endforeach

                            @feature('visa')
                                <a
                                    class="egho-search-tab"
                                    href="{{ route('work-visa.index') }}"
                                >
                                    Work Visa
                                </a>
                            @endfeature
                        </div>

                        {{--
                            Trip type.

                            Radios, not checkboxes: a search is either round
                            trip or one way, never both. Multi city is
                            deliberately absent — the search endpoint takes a
                            single origin and a single destination, so a third
                            option would be a control the backend cannot
                            honour.

                            These inputs stay inside the search form: app.js
                            reads them with a descendant query on that form,
                            so the trip type is submitted with the rest of the
                            search.
                        --}}
                        <fieldset class="egho-trip-type">
                            <legend>Trip type</legend>

                            <label>
                                <input
                                    type="radio"
                                    name="trip_type"
                                    value="round_trip"
                                    checked
                                >
                                <span>Round Trip</span>
                            </label>

                            <label>
                                <input
                                    type="radio"
                                    name="trip_type"
                                    value="one_way"
                                >
                                <span>One Way</span>
                            </label>
                        </fieldset>

                    </div>

                    <div class="egho-search-grid">

                        {{--
                            The two airport fields share one panel so the swap
                            control can sit on the boundary between them, as in
                            the reviewed layout. Both stay direct descendants
                            of the search form, so app.js and the form's own
                            submission see them unchanged.
                        --}}
                        <div class="egho-field-pair">

                            <label class="egho-field">
                                <span>From</span>
                                <input
                                    type="text"
                                    name="origin"
                                    maxlength="3"
                                    minlength="3"
                                    pattern="[A-Za-z]{3}"
                                    placeholder="DAC"
                                    autocomplete="off"
                                    required
                                    data-airport-code
                                >
                            </label>

                            {{--
                                Origin/destination swap.

                                Rendered `hidden` and revealed by the script at
                                the end of the homepage, so a browser without
                                JavaScript never shows a control that would do
                                nothing.
                            --}}
                            <button
                                type="button"
                                class="egho-swap"
                                data-flight-swap
                                aria-label="Swap origin and destination"
                                title="Swap origin and destination"
                                hidden
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 8h15l-3-3"/>
                                    <path d="M20 16H5l3 3"/>
                                </svg>
                            </button>

                            <label class="egho-field">
                                <span>To</span>
                                <input
                                    type="text"
                                    name="destination"
                                    maxlength="3"
                                    minlength="3"
                                    pattern="[A-Za-z]{3}"
                                    placeholder="DXB"
                                    autocomplete="off"
                                    required
                                    data-airport-code
                                >
                            </label>

                        </div>

                        <label class="egho-field">
                            <span>Departure</span>
                            <input
                                type="date"
                                name="departure_date"
                                min="{{ now()->toDateString() }}"
                                data-departure-date
                                required
                            >
                        </label>

                        <label class="egho-field">
                            <span>Return</span>
                            <input
                                type="date"
                                name="return_date"
                                min="{{ now()->addDay()->toDateString() }}"
                                data-return-date
                                required
                            >
                        </label>

                        {{--
                            Travellers and cabin class share the panel's single
                            "Travellers & Class" field. Both controls keep their
                            own field name, so the search endpoint receives
                            exactly the adults and cabin_class values it
                            validates.

                            The panel also offers a "Non Stop Flights"
                            checkbox. The flight search endpoint accepts no stop
                            preference, so a checkbox here could not change a
                            single result — the real non-stop filter lives on
                            the flight results screen, where stop counts are
                            known.
                        --}}
                        <div class="egho-field-group">
                            <span class="egho-field-group-label">
                                Travellers &amp; Class
                            </span>

                            <select name="adults" aria-label="Travellers">
                                <option value="1">1 Traveller</option>
                                <option value="2">2 Travellers</option>
                                <option value="3">3 Travellers</option>
                                <option value="4">4 Travellers</option>
                                <option value="5">5 Travellers</option>
                                <option value="6">6 Travellers</option>
                            </select>

                            <select name="cabin_class" aria-label="Cabin class">
                                <option value="economy">Economy</option>
                                <option value="premium_economy">
                                    Premium Economy
                                </option>
                                <option value="business">Business</option>
                                <option value="first">First Class</option>
                            </select>
                        </div>

                        <button
                            type="submit"
                            class="egho-submit"
                            data-flight-submit
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="11" cy="11" r="6.5"/>
                                <path d="m16 16 4 4"/>
                            </svg>
                            Search Flights
                        </button>

                        {{--
                            Fare comparison runs automatically during offer
                            selection and live revalidation. This chip reflects
                            that step and stays non-interactive so it submits no
                            field the flight search endpoint does not expect.
                        --}}
                        <span class="egho-compare">
                            <span aria-hidden="true">&#9878;</span>
                            Price comparison &amp; live fare revalidation
                            run on every offer
                        </span>
                    </div>

                    @if ($flightSearchMethod === 'POST')
                        <div
                            class="flight-status"
                            data-flight-status
                            role="status"
                            aria-live="polite"
                            hidden
                        ></div>

                        <div
                            class="flight-results"
                            data-flight-results
                            data-flight-select-url="{{ route('flights.offers.select') }}"
                            data-flight-traveler-validation-url="{{ route('flights.travelers.validate') }}"
                            data-flight-booking-draft-url="{{ route('flights.bookings.drafts.store') }}"
                            data-flight-booking-draft-review-url="{{ route('flights.bookings.drafts.review') }}"
                            data-flight-booking-confirmation-intent-url="{{ route('flights.bookings.confirmation-intents.store') }}"
                            data-flight-order-execution-url="{{ route('flights.bookings.orders.execute') }}"
                            data-flight-order-attempt-status-url-template="{{ route('flights.bookings.orders.attempts.show', ['attemptReference' => '__ATTEMPT_REFERENCE__']) }}"
                            data-flight-order-reconciliation-url-template="{{ route('flights.bookings.orders.attempts.reconcile', ['attemptReference' => '__ATTEMPT_REFERENCE__']) }}"
                            @feature('payments')
                                data-flight-payment-readiness-url-template="{{ route('flights.bookings.orders.attempts.payment-readiness.show', ['attemptReference' => '__ATTEMPT_REFERENCE__']) }}"
                                data-flight-payment-execution-url-template="{{ route('flights.bookings.orders.attempts.payments.store', ['attemptReference' => '__ATTEMPT_REFERENCE__']) }}"
                                data-flight-payment-attempt-status-url-template="{{ route('flights.bookings.orders.payments.attempts.show', ['attemptReference' => '__ATTEMPT_REFERENCE__']) }}"
                                data-flight-payment-reconciliation-url-template="{{ route('flights.bookings.orders.payments.attempts.reconcile', ['attemptReference' => '__ATTEMPT_REFERENCE__']) }}"
                            @endfeature
                            data-flight-order-confirmation-url-template="{{ route('flights.bookings.orders.attempts.confirmation.show', ['attemptReference' => '__ATTEMPT_REFERENCE__']) }}"
                            aria-live="polite"
                            hidden
                        ></div>
                    @endif
                </form>

            </div>
        </div>
    </section>
@endfeature
