@extends('layouts.site')

@section('title', 'Hotel Room Selection')

@section(
    'meta_description',
    'Hotel room selection step for hotel bookings prepared with Eagle Global Hub LTD.'
)

@section('body_class', 'egho-page-body')

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            <ol class="egho-steps" aria-label="Hotel booking steps">
                @foreach ([
                    'Search',
                    'Room Selection',
                    'Guest Details',
                    'Review',
                    'Payment',
                    'Confirmation',
                ] as $step)
                    <li @class([
                        'is-done' => $loop->first,
                        'is-active' => $loop->index === 1,
                    ])>
                        {{ $step }}
                    </li>
                @endforeach
            </ol>

            <section class="egho-notice" role="status">
                <span class="egho-notice-status">Not Configured</span>
                <h2>Hotel room availability is not configured</h2>
                <p>
                    Room types, rates, occupancy limits and cancellation
                    policies come from an approved hotel provider. Until that
                    adapter and its server configuration are enabled, this step
                    does not offer any real room to select.
                </p>
                <p>
                    No room, rate or booking is created anywhere on this page.
                </p>
                <div class="egho-actions">
                    <a
                        href="{{ route('hotels.index') }}"
                        class="egho-btn egho-btn-ghost"
                    >
                        Back to hotel search
                    </a>
                </div>
            </section>

            {{--
                Design preview only, local development only.

                The hotel header, tabs, room cards and rates below are static
                placeholder samples used to review the approved layout. They are
                not live availability, they cannot be selected or booked, and no
                figure here comes from a supplier.

                Wiring point: a configured hotel adapter supplies room types,
                occupancy, amenities and nightly rates for the searched dates,
                and the "Select room" action carries the chosen room and rate
                reference into the guest details step.
            --}}
            @if (app()->environment('local'))
                <div class="egho-sample-note">
                    <span aria-hidden="true">&#9432;</span>
                    <span>
                        <strong>Layout preview.</strong>
                        Placeholder hotel and room content. Rates, policies and
                        availability are samples only and nothing on this page is
                        bookable.
                    </span>
                </div>

                <section class="egho-hotel-hero">
                    <div class="egho-hotel-hero-media">
                        <span class="egho-sample-badge">Sample</span>
                        {{--
                            The hero spans the full content width, so it is
                            the one placement that cannot be covered by a
                            single high-density request: 1200px is the 1x
                            variant and 2000px covers high-density displays.
                        --}}
                        <img
                            src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1600&q=85"
                            srcset="
                                https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=85 1x,
                                https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=2000&q=85 2x
                            "
                            alt=""
                            loading="lazy"
                            width="1600"
                            height="1067"
                        >
                    </div>

                    <div class="egho-hotel-hero-copy">
                        <span class="egho-eyebrow">SELECTED PROPERTY</span>
                        <h1>Sample hotel name</h1>
                        <p>Sample city, sample country</p>
                        <span class="egho-rating">
                            &#9733;&#9733;&#9733;&#9733;&#9733; 4.8
                            <em>Sample rating &middot; sample reviews</em>
                        </span>
                        <p style="margin-top:12px">
                            Placeholder property description. Real descriptions,
                            policies and photographs are supplied by the
                            configured hotel provider.
                        </p>
                    </div>
                </section>

                <ul class="egho-tabs" aria-label="Property sections">
                    @foreach ([
                        'Overview',
                        'Rooms',
                        'Amenities',
                        'Location',
                        'Reviews',
                    ] as $tab)
                        <li
                            @class(['egho-tab', 'is-active' => $tab === 'Rooms'])
                            @if ($tab !== 'Rooms') aria-disabled="true" @endif
                        >
                            {{ $tab }}
                        </li>
                    @endforeach
                </ul>

                <section class="egho-results" aria-label="Room types">
                    @foreach ([
                        [
                            'name' => 'Sample Deluxe Room',
                            'summary' => 'Placeholder room description for layout review.',
                            'image' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=1000&q=85',
                            'specs' => 'Sample king bed &middot; 2 adults &middot; city view',
                            'rate' => 'BDT 45,000',
                            'total' => 'BDT 43,650 including taxes',
                        ],
                        [
                            'name' => 'Sample Ocean View Room',
                            'summary' => 'Placeholder room description for layout review.',
                            'image' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1000&q=85',
                            'specs' => 'Sample twin beds &middot; 2 adults &middot; ocean view',
                            'rate' => 'BDT 62,000',
                            'total' => 'BDT 60,140 including taxes',
                        ],
                        [
                            'name' => 'Sample Family Suite',
                            'summary' => 'Placeholder room description for layout review.',
                            'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1000&q=85',
                            'specs' => 'Sample suite &middot; 4 adults &middot; lounge access',
                            'rate' => 'BDT 88,000',
                            'total' => 'BDT 85,360 including taxes',
                        ],
                    ] as $room)
                        <article class="egho-room-card">
                            <div class="egho-room-media">
                                <span class="egho-sample-badge">Sample</span>
                                <img
                                    src="{{ $room['image'] }}"
                                    alt=""
                                    loading="lazy"
                                    width="900"
                                    height="600"
                                >
                            </div>

                            <div class="egho-room-body">
                                <h3>{{ $room['name'] }}</h3>
                                <p>{{ $room['summary'] }}</p>
                                <p>{{ $room['specs'] }}</p>
                                <div class="egho-amenities">
                                    <span>&#10003; Free wifi</span>
                                    <span>&#10003; Breakfast included</span>
                                    <span>&#10003; Sample cancellation policy</span>
                                </div>
                            </div>

                            <div class="egho-room-price">
                                <strong>{{ $room['rate'] }}</strong>
                                <small>/ night</small>
                                <small>{{ $room['total'] }}</small>
                                <a
                                    href="{{ route('hotels.booking') }}"
                                    class="egho-btn egho-btn-primary"
                                >
                                    Continue to guest details
                                </a>
                            </div>
                        </article>
                    @endforeach
                </section>

                <p class="egho-filter-note">
                    Placeholder rates are shown per night for layout review only.
                    Real rates are quoted by the configured hotel provider for
                    the searched dates and are confirmed only after the guest
                    details and payment steps.
                </p>
            @endif

        </div>
    </main>

@endsection
