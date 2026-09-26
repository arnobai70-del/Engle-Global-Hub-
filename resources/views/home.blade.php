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
     * Presentation data for the mockup sections
     * ---------------------------------------------------------------------
     * These are static presentation values for the approved mockup layout.
     * They are NOT booking data: no availability, price, discount or
     * inventory is implied by a promotion card, a service tile, a destination
     * photograph or a customer comment.
     *
     * PLACEHOLDER WIRING POINT
     * ------------------------
     * Popular Destinations will later be driven by a `destinations` table
     * (name, country, image, slug) managed from the admin area. Until that
     * model/migration exists, the cards below render from this local array
     * so the layout can be reviewed. Replace this array with the destinations
     * collection when the admin CRUD lands — no other change to this view is
     * required.
     *
     * The page itself is assembled from the partials in `resources/views/home/`,
     * each of which receives these values from this parent scope.
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
     * A pale, near-greyscale frame reads as a flat navy block once the
     * legibility scrim sits on top. This frame measures vivid and bright,
     * which is the open-sky look the reviewed layout uses. `w=2560` covers a
     * full-bleed hero at high density.
     */
    $heroImage = 'https://images.unsplash.com/photo-1505228395891-9a51e7e86bf6?auto=format&fit=crop&w=2560&q=85';

    /*
     * Promotion cards — the panel's first row under the assurance strip.
     *
     * The headline of each card is the panel's own marketing line. The copy
     * underneath states what the website actually does, because no discount
     * is applied automatically here: a fare or rate is whatever the
     * configured provider returns.
     */
    $promoBanners = [
        [
            'badge' => 'Flights',
            'title' => 'FLAT 20% OFF on International Flights',
            'copy' => 'Every fare the configured flight provider returns, compared in one place. Any discount depends on the fare rules shown on the offer.',
            'cta' => 'Book Now',
            'feature' => 'flights',
            'image' => 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1400&q=85',
        ],
        [
            'badge' => 'Hotels',
            'title' => '40% OFF on Hotel Bookings',
            'copy' => 'Stay options appear only when the hotel provider is configured and enabled for this website.',
            'cta' => 'Book Now',
            'feature' => 'hotels',
            'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1400&q=85',
        ],
    ];

    /*
     * Popular destinations — placeholder cards (see wiring note above).
     *
     * Eight city photographs, matching the approved panel strip. These are
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
            'name' => 'Bali',
            'country' => 'Indonesia',
            'tag' => 'Beach & temples',
            'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'name' => 'Bangkok',
            'country' => 'Thailand',
            'tag' => 'Culture',
            'image' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'name' => 'Singapore',
            'country' => 'Singapore',
            'tag' => 'Skyline',
            'image' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'name' => 'Maldives',
            'country' => 'Maldives',
            'tag' => 'Island escape',
            'image' => 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'name' => 'London',
            'country' => 'United Kingdom',
            'tag' => 'City break',
            'image' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'name' => 'Istanbul',
            'country' => 'Türkiye',
            'tag' => 'Heritage',
            'image' => 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'name' => 'Paris',
            'country' => 'France',
            'tag' => 'Romance',
            'image' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1000&q=85',
        ],
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

    /*
     * One status chip per service tile.
     *
     * A tile backed by the travel service registry reports that service's own
     * status, which is what the customer-facing availability test reads. A
     * tile for something this application does not offer is labelled as
     * not available instead of being given an invented status.
     */
    $serviceStatus = static function (string $key) use ($travelServices): ?array {
        $service = $travelServices[$key] ?? null;

        if (! $service) {
            return null;
        }

        return [
            'label' => $service['status'] ?? 'Not Configured',
            'live' => (bool) ($service['available'] ?? false),
        ];
    };

    /*
     * Our Services tiles — the panel's ten tiles, five across two rows.
     *
     * `service` names a travel service registry entry when the tile maps to
     * one, so the tile can carry that service's real status. A tile with no
     * registry entry describes a product this website does not sell yet.
     */
    $serviceTiles = [
        [
            'key' => 'flights',
            'title' => 'Flights',
            'copy' => 'Domestic & international',
            'service' => 'flights',
            'icon' => '<path d="M3 13.5 21 5l-3.5 8.5L21 19z"/><path d="M8.5 12.2 3 13.5"/>',
        ],
        [
            'key' => 'hotels',
            'title' => 'Hotels',
            'copy' => 'Stays worldwide',
            'service' => 'hotels',
            'icon' => '<path d="M4 20V9m0 5h16v6M4 9l8-5 8 5"/><path d="M9.5 14.5v-2h5v2"/>',
        ],
        [
            'key' => 'tours',
            'title' => 'Tours & Activities',
            'copy' => 'Explore experiences',
            'service' => 'tours',
            'icon' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.4 2.5 3.6 5.5 3.6 9s-1.2 6.5-3.6 9c-2.4-2.5-3.6-5.5-3.6-9S9.6 5.5 12 3z"/>',
        ],
        [
            'key' => 'visa',
            'title' => 'Visa Assistance',
            'copy' => 'Tourist & business visa',
            'service' => 'visa',
            'icon' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14.5 3v4.5H19"/><path d="M9 13h6M9 16.5h4"/>',
        ],
        [
            'key' => 'work-visa',
            'title' => 'Work Visa Processing',
            'copy' => 'Jobs & work permits',
            'service' => null,
            'icon' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5h6v2M3 12h18"/>',
        ],
        [
            'key' => 'holiday-packages',
            'title' => 'Holiday Packages',
            'copy' => 'Curated itineraries',
            'service' => null,
            'icon' => '<path d="M3 9h18v11H3z"/><path d="M3 9l2.4-5h5.1l-1.6 5M13 4h5.2L21 9"/><path d="M10 13v3h4v-3"/>',
        ],
        [
            'key' => 'trains',
            'title' => 'Trains',
            'copy' => 'Easy train booking',
            'service' => null,
            'icon' => '<rect x="5" y="3" width="14" height="14" rx="3"/><path d="M5 10h14M9 21l-1.5-3M15 21l1.5-3M9.5 13.5h.01M14.5 13.5h.01"/>',
        ],
        [
            'key' => 'buses',
            'title' => 'Buses',
            'copy' => 'Comfortable travel',
            'service' => null,
            'icon' => '<rect x="4" y="4" width="16" height="13" rx="2.5"/><path d="M4 11h16M8 20v-3M16 20v-3M7.5 14h.01M16.5 14h.01"/>',
        ],
        [
            'key' => 'cabs',
            'title' => 'Cabs',
            'copy' => 'Airport & local rides',
            'service' => null,
            'icon' => '<path d="M5 16.5h14M6.5 16.5V12l1.8-3.6A1.6 1.6 0 0 1 9.7 7.4h4.6a1.6 1.6 0 0 1 1.4 1l1.8 3.6v4.5"/><path d="M5 16.5v2.2h2.5v-2.2M16.5 16.5v2.2H19v-2.2M8.5 12.5h7"/>',
        ],
        [
            'key' => 'insurance',
            'title' => 'Travel Insurance',
            'copy' => 'Safe & secure journeys',
            'service' => null,
            'icon' => '<path d="M12 3 4.5 6.6v4.9c0 4.6 3.1 7.6 7.5 8.5 4.4-.9 7.5-3.9 7.5-8.5V6.6z"/><path d="m9 12 2.2 2.2L15.5 10"/>',
        ],
    ];

    /*
     * Service panels — the panel's second row.
     *
     * Each panel carries a checklist and exactly one action. Where the action
     * leads to a page this website really has, it is a link; where the product
     * does not exist yet, it is a status chip instead of a dead link.
     */
    $servicePanels = [
        [
            'key' => 'visa-services',
            'class' => 'is-visa',
            'title' => 'Visa Services',
            'copy' => 'Visa assistance for 200+ destinations, with the requirements check on every route.',
            'items' => [
                'Tourist Visa',
                'Business Visa',
                'Transit Visa',
                'Visa Requirements Check',
            ],
            'cta' => 'Apply for Visa',
            'service' => 'visa',
            'image' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=900&q=85',
        ],
        [
            'key' => 'work-visa',
            'class' => 'is-workvisa',
            'title' => 'Work Visa Processing',
            'copy' => 'Start your global career — job visas, work permits and document assistance.',
            'items' => [
                'Job Visa & Work Permits',
                'Skilled Worker Visa',
                'Employer Sponsored Visa',
                'Document Assistance',
            ],
            'cta' => 'Apply Now',
            'service' => null,
            'work_visa' => true,
            'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=85',
        ],
        [
            'key' => 'holiday-packages',
            'class' => 'is-holiday',
            'title' => 'Holiday Packages',
            'copy' => 'Package holidays are not sold on this website yet. Tours that are configured are searchable from the tours surface.',
            'items' => [
                'Family Packages',
                'Honeymoon Packages',
                'Adventure Tours',
                'Customized Itineraries',
            ],
            'cta' => null,
            'service' => null,
            'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=85',
        ],
    ];

    /*
     * Why choose — six statements about how this website behaves.
     *
     * The panel's tiles make trust claims (thousand-strong customer bases,
     * country counts, round-the-clock desks). This application holds no data
     * behind any of those, so each tile instead states something the code
     * actually does and that the test suite can hold to.
     */
    $whyChoose = [
        [
            'title' => 'Fares compared in one place',
            'copy' => 'Every offer the configured provider returns is listed and compared side by side.',
            'icon' => '<path d="M4 7h16M4 12h16M4 17h10"/>',
        ],
        [
            'title' => 'Live fare revalidation',
            'copy' => 'A selected fare is revalidated on the server before traveller details are collected.',
            'icon' => '<path d="M12 3a9 9 0 1 0 9 9"/><path d="M12 7v5l3.5 2"/>',
        ],
        [
            'title' => 'Secure account steps',
            'copy' => 'Search, travellers, review and booking status all stay inside one signed-in account.',
            'icon' => '<path d="M12 3 4.5 6.6v4.9c0 4.6 3.1 7.6 7.5 8.5 4.4-.9 7.5-3.9 7.5-8.5V6.6z"/><path d="m9 12 2.2 2.2L15.5 10"/>',
        ],
        [
            'title' => 'Explicit availability',
            'copy' => 'A service is listed as available only when its provider is configured and enabled.',
            'icon' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.2 2.3 2.3 4.7-5"/>',
        ],
        [
            'title' => 'Visible booking status',
            'copy' => 'Each booking carries its own order and payment status, readable at any time.',
            'icon' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14.5 3v4.5H19"/><path d="M9 12.5h6M9 16h4"/>',
        ],
        [
            'title' => 'Credentials stay server-side',
            'copy' => 'Provider credentials are never rendered into a page or sent to a browser.',
            'icon' => '<rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7.5a4 4 0 0 1 8 0V10"/>',
        ],
    ];

    /*
     * Page assets.
     *
     * The sheet and the rail script live in public/ with no content hash, so
     * the modification time is appended to keep a cached copy from outliving
     * an update. The lookup is error-suppressed because the production
     * readiness check runs the application against a relocated public
     * directory that does not hold these files.
     */
    $pageAssetVersion = static function (string $file): string {
        $modifiedAt = @filemtime(public_path($file));

        return $modifiedAt ? '?v='.$modifiedAt : '';
    };
@endphp

@push('head')
    <link rel="preload" as="image" href="{{ $heroImage }}">

    {{--
        Homepage sections. Page-scoped like the flight and hotel sheets, so a
        change here can never reach another screen.
    --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/egh-home.css').$pageAssetVersion('css/egh-home.css') }}"
    >
@endpush

@section('content')

    <main class="site-home site-ota-home">
        @include('home._hero')
        @include('home._highlights')
        @include('home._services')
        @include('home._panels')
        @include('home._reasons')
        @include('home._app')
    </main>

@endsection

@push('scripts')
    <script
        src="{{ asset('js/egh-home.js').$pageAssetVersion('js/egh-home.js') }}"
        defer
    ></script>

    <script>
        /*
         * Origin/destination swap on the homepage search card.
         *
         * Progressive enhancement: the control is marked `hidden` in the
         * markup and is only revealed once this handler is attached, so no
         * browser is ever shown a button that does nothing. Values are moved
         * in place, and focus returns to the origin field, which is where a
         * keyboard user continues from.
         *
         * The button's own form is used as the scope, so this works for both
         * the signed-in search form and the signed-out form that leads to
         * sign-in. Nothing here submits or validates: the form's existing
         * behaviour is untouched.
         */
        (function () {
            var init = function () {
                var swap = document.querySelector('[data-flight-swap]');
                var form = swap ? swap.form : null;

                if (!swap || !form) {
                    return;
                }

                var airports = form.querySelectorAll('[data-airport-code]');

                if (airports.length < 2) {
                    return;
                }

                swap.hidden = false;

                swap.addEventListener('click', function () {
                    var origin = airports[0];
                    var destination = airports[1];
                    var previousOrigin = origin.value;

                    origin.value = destination.value;
                    destination.value = previousOrigin;
                    origin.focus();
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();

        /*
         * Destination rail controls.
         *
         * The buttons only page the strip that is already in the document, so
         * they are revealed once JavaScript is running rather than being left
         * visible for a browser that could not act on them. `egh-home.js` owns
         * the scrolling itself and disables a button at each end of the rail.
         */
        (function () {
            var init = function () {
                document.querySelectorAll('[data-egho-rail]').forEach(function (rail) {
                    var scope = rail.closest('section') || rail.parentElement;
                    var controls = scope
                        ? scope.querySelector('[data-egho-rail-nav]')
                        : null;

                    if (controls) {
                        controls.hidden = false;
                    }
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
@endpush
