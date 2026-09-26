@extends('layouts.site')

@section('title', 'Search Flights')
@section('body_class', 'dashboard-body egho-page-body')

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

                    <div class="flight-results-toolbar">
                        <span class="flight-results-toolbar-title">
                            Compare fares
                        </span>

                        <div class="flight-results-sort">
                            <span>Sort by</span>

                            <select
                                class="egho-sort-select"
                                aria-label="Sort flight results"
                                disabled
                            >
                                <option>Cheapest (provider order)</option>
                            </select>
                        </div>
                    </div>

                    <aside
                        class="egho-filter-card flight-results-rail"
                        aria-label="Result filters"
                    >
                        <div class="egho-filter-group">
                            <h2>Price range</h2>

                            <input
                                class="egho-range"
                                type="range"
                                min="0"
                                max="100"
                                value="60"
                                aria-label="Price range"
                                disabled
                            >

                            <div class="egho-range-row">
                                <span>Lowest fare</span>
                                <span>Highest fare</span>
                            </div>
                        </div>

                        <div class="egho-filter-group">
                            <h2>Stops</h2>

                            @foreach ([
                                'Direct only',
                                'Up to 1 stop',
                                'Up to 2 stops',
                            ] as $stopOption)
                                <label class="egho-check">
                                    <input type="checkbox" disabled>
                                    <span>{{ $stopOption }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="egho-filter-group">
                            <h2>Airlines</h2>

                            <p class="egho-filter-note">
                                Carrier names shown in the results are the ones
                                the provider actually returned.
                            </p>
                        </div>

                        <div class="egho-filter-group">
                            <h2>Departure &amp; arrival time</h2>

                            @foreach (['Departure time', 'Arrival time'] as $timeLabel)
                                <label class="flight-filter-field">
                                    <span>{{ $timeLabel }}</span>

                                    <select
                                        aria-label="{{ $timeLabel }}"
                                        disabled
                                    >
                                        <option>Any time</option>
                                    </select>
                                </label>
                            @endforeach
                        </div>

                        <p class="egho-filter-note">
                            The configured search provider returns fares in its
                            own order and does not expose stop, carrier, price
                            or time filtering, so these controls stay disabled.
                            No fare is hidden or re-ordered by this page.
                        </p>
                    </aside>

                <div
                    class="flight-results"
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
