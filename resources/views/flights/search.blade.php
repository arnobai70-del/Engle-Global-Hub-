@extends('layouts.site')

@section('title', 'Search Flights')
@section('body_class', 'dashboard-body egho-page-body')

@php
    /*
     * Result-page assets.
     *
     * Both live in public/ rather than the Vite bundle so this screen can ship
     * a change without rebuilding every other page. Like the theme stylesheets
     * they carry no content hash, so the modification time is appended to the
     * URL to keep a cached copy from outliving an update.
     */
    $flightAssets = [
        'css' => 'css/egh-flight.css',
        'js' => 'js/egh-flight-results.js',
    ];

    $flightAssetVersion = @filemtime(public_path($flightAssets['css']));
    $flightScriptVersion = @filemtime(public_path($flightAssets['js']));
@endphp

@push('head')
    <link
        rel="stylesheet"
        href="{{ asset($flightAssets['css']).($flightAssetVersion ? '?v='.$flightAssetVersion : '') }}"
    >
@endpush

@push('scripts')
    <script
        src="{{ asset($flightAssets['js']).($flightScriptVersion ? '?v='.$flightScriptVersion : '') }}"
        defer
    ></script>
@endpush

@section('content')

<main class="flight-container">

        <section class="flight-hero">
            <div>
                <span class="flight-kicker">
                    FLIGHT SEARCH
                </span>

                <h1>Where would you like to go?</h1>

                <p>
                    Search domestic and international flight options with
                    secure passenger and itinerary validation.
                </p>
            </div>

            <div class="flight-hero-badge">
                <span>&#9992;</span>

                <div>
                    <strong>Eagle Global Hub LTD Flights</strong>
                    <small>Fast, simple and secure search.</small>
                </div>
            </div>
        </section>

        <section aria-label="Flight booking journey">
            <ol class="egho-steps">
                @foreach ([
                    'Search',
                    'Select',
                    'Travellers',
                    'Review',
                    'Confirmation',
                ] as $step)
                    <li @class(['is-active' => $loop->first])>{{ $step }}</li>
                @endforeach
            </ol>
        </section>
        <section class="flight-search-card">
            <div class="flight-card-heading">
                <div>
                    <span class="flight-kicker">PLAN YOUR JOURNEY</span>
                    <h2>Search Flights</h2>
                </div>

                <p>
                    Enter your route, travel dates and passenger details.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('flights.search') }}"
                class="flight-search-form"
                data-flight-search-form
            >
                @csrf

                <fieldset class="flight-trip-type">
                    <legend>Trip Type</legend>

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

                <div class="flight-route-grid">
                    <div class="flight-form-field">
                        <label for="flight-origin">From</label>

                        <input
                            id="flight-origin"
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

                        <small class="flight-field-help">
                            3-letter airport code
                        </small>

                        <small
                            class="flight-field-error"
                            data-error-for="origin"
                        ></small>
                    </div>

                    <div class="flight-route-arrow" aria-hidden="true">
                        &#8645;
                    </div>

                    <div class="flight-form-field">
                        <label for="flight-destination">To</label>

                        <input
                            id="flight-destination"
                            type="text"
                            name="destination"
                            maxlength="3"
                            minlength="3"
                            pattern="[A-Za-z]{3}"
                            placeholder="CXB"
                            autocomplete="off"
                            required
                            data-airport-code
                        >

                        <small class="flight-field-help">
                            3-letter airport code
                        </small>

                        <small
                            class="flight-field-error"
                            data-error-for="destination"
                        ></small>
                    </div>
                </div>

                <div class="flight-form-grid">
                    <div class="flight-form-field">
                        <label for="flight-departure">
                            Departure
                        </label>

                        <input
                            id="flight-departure"
                            type="date"
                            name="departure_date"
                            min="{{ now()->toDateString() }}"
                            required
                            data-departure-date
                        >

                        <small
                            class="flight-field-error"
                            data-error-for="departure_date"
                        ></small>
                    </div>

                    <div class="flight-form-field">
                        <label for="flight-return">
                            Return
                        </label>

                        <input
                            id="flight-return"
                            type="date"
                            name="return_date"
                            min="{{ now()->addDay()->toDateString() }}"
                            required
                            data-return-date
                        >

                        <small
                            class="flight-field-error"
                            data-error-for="return_date"
                        ></small>
                    </div>

                    <div class="flight-form-field">
                        <label for="flight-cabin">
                            Cabin
                        </label>

                        <select
                            id="flight-cabin"
                            name="cabin_class"
                            required
                        >
                            <option value="economy">
                                Economy
                            </option>

                            <option value="premium_economy">
                                Premium Economy
                            </option>

                            <option value="business">
                                Business
                            </option>

                            <option value="first">
                                First Class
                            </option>
                        </select>

                        <small
                            class="flight-field-error"
                            data-error-for="cabin_class"
                        ></small>
                    </div>
                </div>

                <div class="flight-passenger-section">
                    <div class="flight-passenger-heading">
                        <div>
                            <strong>Passengers</strong>
                            <span>Maximum 9 travellers per search.</span>
                        </div>
                    </div>

                    <div class="flight-passenger-grid">
                        <div class="flight-form-field">
                            <label for="flight-adults">
                                Adults
                            </label>

                            <input
                                id="flight-adults"
                                type="number"
                                name="adults"
                                min="1"
                                max="9"
                                value="1"
                                required
                            >

                            <small
                                class="flight-field-error"
                                data-error-for="adults"
                            ></small>
                        </div>

                        <div class="flight-form-field">
                            <label for="flight-children">
                                Children
                            </label>

                            <input
                                id="flight-children"
                                type="number"
                                name="children"
                                min="0"
                                max="8"
                                value="0"
                                required
                            >

                            <small
                                class="flight-field-error"
                                data-error-for="children"
                            ></small>
                        </div>

                        <div class="flight-form-field">
                            <label for="flight-infants">
                                Infants
                            </label>

                            <input
                                id="flight-infants"
                                type="number"
                                name="infants"
                                min="0"
                                max="8"
                                value="0"
                                required
                            >

                            <small
                                class="flight-field-error"
                                data-error-for="infants"
                            ></small>
                        </div>
                    </div>

                    <small
                        class="flight-field-error"
                        data-error-for="passengers"
                    ></small>
                </div>

                <div
                    class="flight-status"
                    data-flight-status
                    role="status"
                    aria-live="polite"
                    hidden
                ></div>

                {{--
                    Result chrome for the offer list.

                    The offer list itself is rendered by the flight search
                    script, so this toolbar and filter rail are static and are
                    only revealed while results are on screen (see the
                    `.flight-results-shell:has(...)` rules in the OTA theme).

                    The configured search provider returns fares in its own
                    order and exposes no stop, carrier, price or time filtering
                    to this page, so every control stays disabled and the note
                    says so. Nothing here hides or re-orders a fare.
                --}}
                <div class="flight-results-shell">

                    {{--
                        Search summary.

                        Rendered hidden and filled in by the result script from
                        the search that was actually submitted, so it can never
                        describe a search that did not run. It sits inside the
                        form, so the modify control is a plain button rather
                        than a submit control.
                    --}}
                    <div
                        class="egho-flight-summary"
                        data-flight-summary
                        hidden
                    >
                        <div class="egho-flight-summary-route">
                            <strong data-flight-summary-origin>&mdash;</strong>

                            <span aria-hidden="true">&#8594;</span>

                            <strong data-flight-summary-destination>&mdash;</strong>
                        </div>

                        <div class="egho-flight-summary-meta">
                            <span data-flight-summary-dates>&mdash;</span>
                            <span data-flight-summary-travellers>&mdash;</span>
                        </div>

                        <button
                            type="button"
                            class="egho-flight-summary-modify"
                            data-flight-summary-modify
                        >
                            Modify search
                        </button>
                    </div>

                    <div class="flight-results-toolbar">
                        <div class="flight-results-toolbar-head">
                            <strong class="flight-results-toolbar-title">
                                Compare fares
                            </strong>

                            <span
                                class="egho-flight-count"
                                data-flight-count
                                role="status"
                                aria-live="polite"
                                hidden
                            ></span>
                        </div>

                        <div class="flight-results-sort">
                            <span>Sort by</span>

                            <select
                                class="egho-sort-select"
                                data-flight-sort
                                aria-label="Sort flight results"
                                disabled
                            >
                                <option value="provider" selected>
                                    Provider order
                                </option>
                                <option value="cheapest">
                                    Cheapest fare
                                </option>
                                <option value="shortest">
                                    Shortest duration
                                </option>
                                <option value="earliest">
                                    Earliest departure
                                </option>
                            </select>
                        </div>
                    </div>

                    <aside
                        class="egho-filter-card flight-results-rail"
                        aria-label="Result filters"
                        data-flight-filters
                    >
                        {{--
                            Every control ships disabled and is enabled by the
                            result script once it has read the offers that came
                            back, so a browser without JavaScript is never shown
                            a filter that cannot do anything.

                            Filtering happens in the browser over the options
                            this search already returned. It never asks the
                            provider for a different set of fares.
                        --}}
                        <div class="egho-filter-group">
                            <h2>Stops</h2>

                            @foreach ([
                                ['value' => '0', 'label' => 'Non-stop'],
                                ['value' => '1', 'label' => '1 stop'],
                                ['value' => '2', 'label' => '2 or more stops'],
                            ] as $stopFilter)
                                <label class="egho-check">
                                    <input
                                        type="checkbox"
                                        value="{{ $stopFilter['value'] }}"
                                        data-flight-stop-filter
                                        disabled
                                    >
                                    <span>{{ $stopFilter['label'] }}</span>
                                </label>
                            @endforeach

                            <p class="egho-filter-help">
                                Applies to every leg of an itinerary.
                            </p>
                        </div>

                        <div class="egho-filter-group">
                            <h2>Airlines</h2>

                            <div
                                class="egho-check-list"
                                data-flight-airline-filters
                            >
                                <p class="egho-filter-note">
                                    Carrier names appear here once a search has
                                    returned options.
                                </p>
                            </div>
                        </div>

                        <div class="egho-filter-group">
                            <h2>Price range</h2>

                            <label class="flight-filter-field">
                                <span>Highest fare</span>

                                <input
                                    class="egho-range"
                                    type="range"
                                    value="100"
                                    data-flight-price-max
                                    aria-label="Highest fare"
                                    disabled
                                >
                            </label>

                            <div class="egho-range-row">
                                <span data-flight-price-min-label>&mdash;</span>
                                <span data-flight-price-max-label>&mdash;</span>
                            </div>

                            <label class="flight-filter-field">
                                <span>Lowest fare</span>

                                <input
                                    class="egho-range"
                                    type="range"
                                    value="0"
                                    data-flight-price-min
                                    aria-label="Lowest fare"
                                    disabled
                                >
                            </label>
                        </div>

                        <div class="egho-filter-group">
                            <h2>Departure time</h2>

                            <label class="flight-filter-field">
                                <span>Earliest</span>

                                <input
                                    class="egho-range"
                                    type="range"
                                    min="0"
                                    max="23"
                                    value="0"
                                    data-flight-departure-min
                                    aria-label="Earliest departure hour"
                                    disabled
                                >
                            </label>

                            <label class="flight-filter-field">
                                <span>Latest</span>

                                <input
                                    class="egho-range"
                                    type="range"
                                    min="0"
                                    max="23"
                                    value="23"
                                    data-flight-departure-max
                                    aria-label="Latest departure hour"
                                    disabled
                                >
                            </label>

                            <div class="egho-range-row">
                                <span data-flight-departure-window>
                                    Any time
                                </span>
                            </div>
                        </div>

                        <div class="egho-filter-group">
                            <h2>Arrival time</h2>

                            <label class="flight-filter-field">
                                <span>Earliest</span>

                                <input
                                    class="egho-range"
                                    type="range"
                                    min="0"
                                    max="23"
                                    value="0"
                                    data-flight-arrival-min
                                    aria-label="Earliest arrival hour"
                                    disabled
                                >
                            </label>

                            <label class="flight-filter-field">
                                <span>Latest</span>

                                <input
                                    class="egho-range"
                                    type="range"
                                    min="0"
                                    max="23"
                                    value="23"
                                    data-flight-arrival-max
                                    aria-label="Latest arrival hour"
                                    disabled
                                >
                            </label>

                            <div class="egho-range-row">
                                <span data-flight-arrival-window>
                                    Any time
                                </span>
                            </div>

                            <p class="egho-filter-help">
                                Departure and arrival windows apply to the first
                                leg of an itinerary.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="egho-filter-reset"
                            data-flight-filter-reset
                            hidden
                        >
                            Reset filters
                        </button>

                        {{--
                            Replaced with the active wording once the script has
                            enabled the controls, so the note always describes
                            the state the visitor is actually looking at.
                        --}}
                        <p
                            class="egho-filter-note"
                            data-flight-filter-note
                        >
                            These filters need JavaScript. The configured search
                            provider does not expose stop, carrier, price or
                            time filtering to this page, so while the controls
                            are inactive no fare is hidden or re-ordered.
                        </p>
                    </aside>

                <div
                    class="flight-results egho-flight-results"
                    data-flight-results data-flight-select-url="{{ route('flights.offers.select') }}" data-flight-traveler-validation-url="{{ route('flights.travelers.validate') }}"
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

                </div>

                <div class="flight-form-actions">
                    <a
                        href="{{ route('dashboard') }}"
                        class="flight-secondary-button"
                    >
                        Back to Dashboard
                    </a>

                    <button
                        type="submit"
                        class="flight-search-button"
                        data-flight-submit
                    >
                        Search Flights
                    </button>
                </div>
            </form>
        </section>

        {{--
            Payment method availability.

            Only the secure supplier balance payment used by the flight
            payment step is actually connected. Wallet and bank transfer
            options from the approved mockup are listed as unavailable rather
            than shown as working choices.
        --}}
        @feature('payments')
            <section
                class="egho-panel flight-payment-methods"
                aria-label="Payment methods"
            >
                <div class="egho-panel-head">
                    <span class="egho-eyebrow">PAYMENT METHODS</span>
                    <h2>How flight payment is collected</h2>
                    <p>
                        Only methods that are genuinely connected are offered.
                        Everything else stays unavailable until its integration
                        is enabled and verified.
                    </p>
                </div>

                <ul class="egho-benefits">
                    @foreach ([
                        [
                            'title' => 'Card / secure supplier balance',
                            'note' => 'Taken in the secure flight payment step once an order attempt exists. The amount is always set server-side.',
                            'available' => true,
                        ],
                        [
                            'title' => 'bKash',
                            'note' => 'Not connected. No mobile wallet payment can be taken.',
                            'available' => false,
                        ],
                        [
                            'title' => 'Nagad',
                            'note' => 'Not connected. No mobile wallet payment can be taken.',
                            'available' => false,
                        ],
                        [
                            'title' => 'Bank transfer',
                            'note' => 'Not connected. No bank transfer instruction exists yet.',
                            'available' => false,
                        ],
                    ] as $method)
                        <li class="egho-benefit">
                            <span
                                class="egho-benefit-icon"
                                aria-hidden="true"
                            >
                                {{ $method['available'] ? '\u2713' : '\u2014' }}
                            </span>

                            <span>
                                <strong>{{ $method['title'] }}</strong>
                                <small>{{ $method['note'] }}</small>

                                <span
                                    @class([
                                        'egho-status',
                                        'egho-status-ok' => $method['available'],
                                        'egho-status-muted' => ! $method['available'],
                                    ])
                                >
                                    {{ $method['available'] ? 'Connected' : 'Not connected' }}
                                </span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endfeature

        <section
            class="flight-info-grid"
            aria-label="Flight search guidance"
        >
            <article>
                <span>01</span>

                <div>
                    <strong>Clear search details</strong>

                    <p>
                        Route, travel dates, cabin and passenger counts are
                        checked before your search continues.
                    </p>
                </div>
            </article>

            <article>
                <span>02</span>

                <div>
                    <strong>Easy fare comparison</strong>

                    <p>
                        Review carrier, itinerary timing and fare information
                        before choosing a flight option.
                    </p>
                </div>
            </article>

            <article>
                <span>03</span>

                <div>
                    <strong>Secure traveler review</strong>

                    <p>
                        Traveler details and booking review remain inside your
                        authenticated account and validated server-side.
                    </p>
                </div>
            </article>
        </section>

    </main>
@endsection
