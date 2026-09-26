@extends('layouts.site')

@section('title', 'Flights & Travel')

@section(
    'meta_description',
    'Search flights and manage your travel bookings with Eagle Global Hub LTD.'
)

@php
    /*
     * ---------------------------------------------------------------------
     * Flight search target (unchanged behaviour)
     * ---------------------------------------------------------------------
     * Guests are sent to sign-in, customers with the flights.search
     * permission post to the flight search endpoint, and any other signed-in
     * account is returned to its dashboard.
     */
    $flightSearchAction = route('login');
    $flightSearchMethod = 'GET';

    if (auth()->check()) {
        if (auth()->user()->can('flights.search')) {
            $flightSearchAction = route('flights.search');
            $flightSearchMethod = 'POST';
        } else {
            $flightSearchAction = route('dashboard');
        }
    }

    /*
     * ---------------------------------------------------------------------
     * Presentation data for the marketing sections
     * ---------------------------------------------------------------------
     * These are static presentation values for the approved mockup layout.
     * They are NOT booking data: no availability, price, discount or
     * inventory is implied by the airline strip or the destination cards.
     *
     * PLACEHOLDER WIRING POINT
     * ------------------------
     * Popular Destinations will later be driven by a `destinations` table
     * (name, country, image, slug) managed from the admin area. Until that
     * model/migration exists, the cards below render from this local array
     * so the layout can be reviewed. Replace this array with the
     * destinations collection when the admin CRUD lands — no other change to
     * this view is required.
     */
    /*
     * Photograph quality policy for this homepage.
     *
     * Every photograph is requested from Unsplash with `auto=format&q=85`
     * and a width that is at least twice the widest CSS box it is painted
     * into, so a high-density display still receives a pixel-dense image.
     * `auto=format` serves the smallest of WebP/AVIF/JPEG the browser
     * accepts, so the extra resolution does not cost the older formats.
     */
    /*
     * Hero photograph.
     *
     * The previous choice measured as a pale, near-greyscale frame (average
     * rgb 163,170,176 at only 0.25 saturation), so once the legibility scrim
     * sat on top the band read as a flat navy block instead of a photograph.
     * This frame measures vivid and bright (0.47 saturation, 0.67 brightness,
     * cyan-dominant), which is the open-sky look the reviewed layout uses.
     * `w=2560` covers a full-bleed hero at high density.
     */
    $heroImage = 'https://images.unsplash.com/photo-1505228395891-9a51e7e86bf6?auto=format&fit=crop&w=2560&q=85';

    $promoBanners = [
        [
            'badge' => 'Flights',
            'title' => 'FLAT 20% OFF on International Flights',
            'copy' => 'Compare fares across trusted airlines and continue to a secured booking flow.',
            'cta' => 'Search Flights',
            'feature' => 'flights',
            'image' => 'https://images.unsplash.com/photo-1540339832862-474599807836?auto=format&fit=crop&w=1400&q=85',
            'class' => '',
        ],
        [
            'badge' => 'Hotels',
            'title' => 'Up to 40% OFF on Hotel Bookings',
            'copy' => 'Stay options in one place, shown only when the hotel provider is configured.',
            'cta' => 'View Hotels',
            'feature' => 'hotels',
            'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1400&q=85',
            'class' => '',
        ],
    ];

    /*
     * Popular destinations — placeholder cards (see wiring note above).
     *
     * Six city photographs, matching the reviewed homepage strip. These are
     * destination inspiration images only: no fare, room rate or availability
     * is claimed, and no destination record is read or written.
     */
    $popularDestinations = [
        [
            'name' => 'Dubai',
            'country' => 'United Arab Emirates',
            'tag' => 'City & shopping',
            'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'name' => 'Singapore',
            'country' => 'Singapore',
            'tag' => 'Skyline',
            'image' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'name' => 'Bangkok',
            'country' => 'Thailand',
            'tag' => 'Culture',
            'image' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'name' => 'Maldives',
            'country' => 'Maldives',
            'tag' => 'Island escape',
            'image' => 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'name' => 'Istanbul',
            'country' => 'T\u00fcrkiye',
            'tag' => 'Heritage',
            'image' => 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'name' => 'Bali',
            'country' => 'Indonesia',
            'tag' => 'Beach & temples',
            'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=85',
        ],
    ];

    /*
     * Airline strip — carrier names the search layer can query through the
     * configured flight provider. Rendered as neutral monogram marks rather
     * than carrier artwork, which requires licensed brand assets.
     */
    $airlines = [
        ['name' => 'Emirates', 'code' => 'EK'],
        ['name' => 'Qatar Airways', 'code' => 'QR'],
        ['name' => 'Singapore Airlines', 'code' => 'SQ'],
        ['name' => 'Turkish Airlines', 'code' => 'TK'],
        ['name' => 'Etihad Airways', 'code' => 'EY'],
        ['name' => 'Lufthansa', 'code' => 'LH'],
    ];

    $travelServices = $travelServices ?? [];

    /*
     * Shared homepage link rule.
     *
     * This intentionally mirrors the rule the homepage already used for its
     * travel service list: a service is linked only when the registry reports
     * it as available and it has a real route. Authorisation stays where it
     * already lives — in each route's own middleware — so this view never
     * widens or narrows access, and an unconfigured service never produces a
     * link that implies availability.
     */
    $serviceLink = static function (string $key) use ($travelServices): ?string {
        $service = $travelServices[$key] ?? null;

        if (! $service || ! ($service['available'] ?? false) || empty($service['route_name'])) {
            return null;
        }

        return route($service['route_name']);
    };

    $searchTabs = [
        ['key' => 'hotels', 'label' => 'Hotels'],
        ['key' => 'tours', 'label' => 'Tours'],
        ['key' => 'visa', 'label' => 'Visa'],
    ];
@endphp

@push('head')
    <link rel="preload" as="image" href="{{ $heroImage }}">
@endpush

@section('content')

    <main class="site-home site-ota-home">

        {{-- ============================================================ HERO --}}
        <section
            class="egho-hero"
            style="--egho-hero-image: url('{{ $heroImage }}')"
        >
            <div class="egho-shell">
                <div class="egho-hero-copy">

                    @feature('flights')
                        <span class="egho-hero-eyebrow">
                            Flights, Hotels, Tours &amp; Visa
                        </span>

                        <h1>
                            Travel the World with<br>
                            Eagle Global Hub
                        </h1>

                        <p>
                            Flights, Hotels, Tours, Visa, Visa Processing &amp;
                            More — search, compare and manage every step from
                            one secure account.
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
                            Travel services<br>
                            under one secure account
                        </h1>

                        <p>
                            Use the currently available services listed below.
                        </p>
                    @endfeature

                </div>
            </div>
        </section>

        {{-- ==================================================== SEARCH PANEL --}}
        @feature('flights')
            <section class="egho-search-wrap">
                <div class="egho-shell">
                    <div class="egho-search-card">

                        {{--
                            Navigation pills, not ARIA tabs: each pill simply
                            moves to another search surface, so tab semantics
                            (aria-controls, roving focus) would be inaccurate.
                        --}}
                        <div class="egho-search-tabs" aria-label="Travel search">
                            <span class="egho-search-tab is-active" aria-current="true">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 13.5 21 5l-3.5 8.5L21 19z"/>
                                    <path d="M8.5 12.2 3 13.5"/>
                                </svg>
                                Flights
                            </span>

                            @foreach ($searchTabs as $tab)
                                @feature($tab['key'])
                                    @php $tabLink = $serviceLink($tab['key']); @endphp

                                    @if ($tabLink)
                                        <a
                                            class="egho-search-tab"
                                            href="{{ $tabLink }}"
                                        >
                                            {{ $tab['label'] }}
                                        </a>
                                    @else
                                        <span
                                            class="egho-search-tab"
                                            title="{{ $tab['label'] }} search becomes available once the provider is configured"
                                        >
                                            {{ $tab['label'] }}
                                        </span>
                                    @endif
                                @endfeature
                            @endforeach
                        </div>

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

                            <div class="egho-search-grid">

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

                                <label class="egho-field">
                                    <span>Travelers</span>
                                    <select name="adults">
                                        <option value="1">1 Traveler</option>
                                        <option value="2">2 Travelers</option>
                                        <option value="3">3 Travelers</option>
                                        <option value="4">4 Travelers</option>
                                        <option value="5">5 Travelers</option>
                                        <option value="6">6 Travelers</option>
                                    </select>
                                </label>

                                <label class="egho-field">
                                    <span>Cabin</span>
                                    <select name="cabin_class">
                                        <option value="economy">Economy</option>
                                        <option value="premium_economy">
                                            Premium Economy
                                        </option>
                                        <option value="business">Business</option>
                                        <option value="first">First Class</option>
                                    </select>
                                </label>

                                {{--
                                    Fare comparison runs automatically during
                                    offer selection and live revalidation. This
                                    chip reflects that step and stays
                                    non-interactive so it submits no field the
                                    flight search endpoint does not expect.
                                --}}
                                <span class="egho-compare">
                                    <span aria-hidden="true">&#9878;</span>
                                    Price comparison &amp; live fare revalidation
                                    run on every offer
                                </span>

                                <button
                                    type="submit"
                                    class="egho-submit"
                                    data-flight-submit
                                >
                                    Search Flights
                                </button>
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

                        <div class="egho-booking-flow">
                            <div>
                                <span>01</span>
                                <div>
                                    <strong>Search</strong>
                                    <small>Route, dates and travelers</small>
                                </div>
                            </div>

                            <div>
                                <span>02</span>
                                <div>
                                    <strong>Select</strong>
                                    <small>Compare fares and times</small>
                                </div>
                            </div>

                            <div>
                                <span>03</span>
                                <div>
                                    <strong>Travelers</strong>
                                    <small>Passenger details</small>
                                </div>
                            </div>

                            <div>
                                <span>04</span>
                                <div>
                                    <strong>Review</strong>
                                    <small>Secure fare review</small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>
        @endfeature

        {{-- ===================================================== QUICK TILES --}}
        <section class="egho-section egho-section-tight">
            <div class="egho-shell">
                <div class="egho-tiles">

                    @feature('flights')
                        @php $flightsLink = $serviceLink('flights'); @endphp

                        @if ($flightsLink)
                            <a href="{{ $flightsLink }}" class="egho-tile">
                                <span class="egho-tile-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M3 13.5 21 5l-3.5 8.5L21 19z"/>
                                        <path d="M8.5 12.2 3 13.5"/>
                                    </svg>
                                </span>
                                <span>
                                    <strong>Book a Flight</strong>
                                    <small>Search fares and seats</small>
                                </span>
                            </a>
                        @endif
                    @endfeature

                    @php
                        $serviceTiles = [
                            [
                                'key' => 'hotels',
                                'title' => 'Book a Hotel',
                                'copy' => 'Stays and room rates',
                                'icon' => '<path d="M3 20V9m0 6h18v5M3 9l9-5 9 5M9 15v-3h6v3"/>',
                            ],
                            [
                                'key' => 'tours',
                                'title' => 'Book a Tour',
                                'copy' => 'Activities and day trips',
                                'icon' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.6 2.6 3.8 5.6 3.8 9s-1.2 6.4-3.8 9c-2.6-2.6-3.8-5.6-3.8-9S9.4 5.6 12 3z"/>',
                            ],
                            [
                                'key' => 'visa',
                                'title' => 'Visa Processing',
                                'copy' => 'Travel requirement checks',
                                'icon' => '<path d="M6 3h9l4 4v14H6z"/><path d="M9 12h6M9 16h4"/>',
                            ],
                        ];
                    @endphp

                    @foreach ($serviceTiles as $tile)
                        @feature($tile['key'])
                            @php $tileLink = $serviceLink($tile['key']); @endphp

                            @if ($tileLink)
                                <a href="{{ $tileLink }}" class="egho-tile">
                                    <span class="egho-tile-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">{!! $tile['icon'] !!}</svg>
                                    </span>
                                    <span>
                                        <strong>{{ $tile['title'] }}</strong>
                                        <small>{{ $tile['copy'] }}</small>
                                    </span>
                                </a>
                            @else
                                <span class="egho-tile">
                                    <span class="egho-tile-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">{!! $tile['icon'] !!}</svg>
                                    </span>
                                    <span>
                                        <strong>{{ $tile['title'] }}</strong>
                                        <small>Available once configured</small>
                                    </span>
                                </span>
                            @endif
                        @endfeature
                    @endforeach

                    {{-- Work Visa application preparation tile. --}}
                    @feature('visa')
                        <a
                            href="{{ route('work-visa.index') }}"
                            class="egho-tile"
                        >
                            <span class="egho-tile-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="7" width="18" height="13" rx="2"/>
                                    <path d="M9 7V5h6v2M3 12h18"/>
                                </svg>
                            </span>
                            <span>
                                <strong>Work Visa</strong>
                                <small>Work permits and sponsorship</small>
                            </span>
                        </a>
                    @else
                        <span class="egho-tile">
                            <span class="egho-tile-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="7" width="18" height="13" rx="2"/>
                                    <path d="M9 7V5h6v2M3 12h18"/>
                                </svg>
                            </span>
                            <span>
                                <strong>Work Visa</strong>
                                <small>Hidden by feature control</small>
                            </span>
                        </span>
                    @endfeature

                </div>
            </div>
        </section>

        {{-- ========================================================= PROMOS --}}
        <section class="egho-section">
            <div class="egho-shell">
                <div class="egho-promos">

                    @foreach ($promoBanners as $promo)
                        @feature($promo['feature'])
                            @php $promoLink = $serviceLink($promo['feature']); @endphp

                            <div
                                class="egho-promo"
                                style="--egho-promo-image: url('{{ $promo['image'] }}')"
                            >
                                <span class="egho-promo-badge">{{ $promo['badge'] }}</span>

                                {{--
                                    Promo cards sit directly under the hero
                                    h1 with no section heading of their own,
                                    so they are h2. Using h3 here skipped a
                                    heading level before the first section h2.
                                --}}
                                <h2>{{ $promo['title'] }}</h2>

                                <p>{{ $promo['copy'] }}</p>

                                @if ($promoLink)
                                    <a href="{{ $promoLink }}" class="egho-promo-cta">
                                        {{ $promo['cta'] }}
                                    </a>
                                @else
                                    <span class="egho-promo-cta">
                                        {{ $promo['cta'] }}
                                    </span>
                                @endif
                            </div>
                        @endfeature
                    @endforeach

                    {{-- Work Visa call to action --}}
                    <div
                        id="work-visa"
                        class="egho-promo is-workvisa"
                        style="--egho-promo-image: url('https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1400&q=85')"
                    >
                        <span class="egho-promo-badge">Work Visa</span>

                        <h2>Work Visa Processing</h2>

                        <p>
                            We handle the paperwork, you chase the dream —
                            job visas, work permits and document assistance.
                        </p>

                        @feature('visa')
                            <a
                                href="{{ route('work-visa.apply') }}"
                                class="egho-promo-cta"
                            >
                                Explore Now
                            </a>
                        @else
                            {{--
                                The work visa route is hidden with the Visa
                                feature, so there is no reachable destination
                                and the call to action is shown as a status chip
                                instead of a dead link.
                            --}}
                            <span class="egho-promo-cta">
                                Coming soon
                            </span>
                        @endfeature
                    </div>

                </div>
            </div>
        </section>

        {{-- ================================================ DESTINATIONS --}}
        {{--
            PLACEHOLDER SECTION
            -------------------
            Cards render from the local `$popularDestinations` array above.
            The section is ready to be driven by a `destinations` table
            (with admin CRUD) — see the wiring note in the presentation-data
            block above. No price or availability claim is made here.

            NOTE: never place an at-directive name (for example the php
            directive tag) inside a Blade comment. Blade compiles statements
            before it strips comments, so a stray directive token inside a
            comment opens a real PHP block and silently swallows the markup
            that follows it.
        --}}
        <section class="egho-section egho-section-alt">
            <div class="egho-shell">

                <div class="egho-section-head">
                    <div>
                        <span class="egho-eyebrow">Explore</span>
                        <h2>Popular Destinations</h2>
                        <p>
                            Inspiration for your next journey. Route search
                            covers any supported airport pair.
                        </p>
                    </div>

                    @feature('flights')
                        @can('flights.search')
                            <a href="{{ route('flights.index') }}" class="egho-section-link">
                                Search flights &rarr;
                            </a>
                        @endcan
                    @endfeature
                </div>

                <div class="egho-destinations">
                    @foreach ($popularDestinations as $destination)
                        <div
                            class="egho-destination"
                            style="--egho-destination-image: url('{{ $destination['image'] }}')"
                        >
                            <span class="egho-destination-copy">
                                <span class="egho-destination-tag">
                                    {{ $destination['tag'] }}
                                </span>
                                <strong>{{ $destination['name'] }}</strong>
                                <small>{{ $destination['country'] }}</small>
                            </span>
                        </div>
                    @endforeach
                </div>

            </div>
        </section>

        {{-- =================================================== AIRLINES --}}
        <section class="egho-section">
            <div class="egho-shell">

                <div class="egho-section-head">
                    <div>
                        <span class="egho-eyebrow">Fare sources</span>
                        <h2>Airlines available in search</h2>
                        <p>
                            Carriers returned by the configured flight data
                            source. Shown as names only — no live fares or seat
                            availability are implied.
                        </p>
                    </div>
                </div>

                <div class="egho-airlines">
                    @foreach ($airlines as $airline)
                        <div class="egho-airline">
                            <span class="egho-airline-mark" aria-hidden="true">
                                {{ $airline['code'] }}
                            </span>
                            <strong>{{ $airline['name'] }}</strong>
                        </div>
                    @endforeach
                </div>

            </div>
        </section>

        {{-- ================================================== SERVICES --}}
        <section class="egho-section egho-section-alt">
            <div class="egho-shell">

                <div class="egho-section-head">
                    <div>
                        <span class="egho-eyebrow">Plan your journey</span>
                        <h2>Travel services</h2>
                        <p>
                            A service is listed as available only when it is
                            enabled and its provider configuration is complete.
                            Access is checked again when the service opens.
                        </p>
                    </div>
                </div>

                <div class="egho-services">
                    @foreach ($travelServices as $service)
                        @feature($service['key'])
                            @php $serviceUrl = $serviceLink($service['key']); @endphp

                            <article @class(['egho-service', 'is-live' => $service['available']])>
                                @if ($serviceUrl)
                                    <a href="{{ $serviceUrl }}">
                                        <strong>{{ $service['label'] }}</strong>
                                    </a>
                                @else
                                    <div>
                                        <strong>{{ $service['label'] }}</strong>
                                    </div>
                                @endif

                                <span class="egho-service-status">
                                    {{ $service['status'] }}
                                </span>
                            </article>
                        @endfeature
                    @endforeach
                </div>

            </div>
        </section>

        {{-- ===================================================== TRUST --}}
        <section class="egho-section">
            <div class="egho-shell">
                <div class="egho-services">
                    <article class="egho-service is-live">
                        <strong>Secure &amp; Reliable</strong>
                        <small>Protected account and booking steps</small>
                    </article>

                    @feature('bookings')
                        <article class="egho-service is-live">
                            <strong>Booking Updates</strong>
                            <small>Review saved order and payment status</small>
                        </article>
                    @endfeature

                    @feature('payments')
                        <article class="egho-service is-live">
                            <strong>Payment Checks</strong>
                            <small>Status is reconciled before confirmation</small>
                        </article>
                    @endfeature

                    @feature('support')
                        <article class="egho-service is-live">
                            <strong>Account Assistance</strong>
                            <small>Current options are listed on Support</small>
                        </article>
                    @endfeature
                </div>
            </div>
        </section>

    </main>

@endsection
