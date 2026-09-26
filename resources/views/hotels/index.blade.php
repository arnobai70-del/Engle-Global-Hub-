@extends('layouts.site')

@section('title', 'Hotels')

@section(
    'meta_description',
    'Hotel search services from Eagle Global Hub LTD.'
)

@section('body_class', 'egho-page-body')

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            <header class="egho-page-head">
                <span class="egho-eyebrow">HOTELS</span>
                <h1>Find a stay for your journey</h1>
                <p>
                    Search configured hotel inventory and review room and rate
                    details before continuing to guest information.
                </p>
            </header>

            <ol class="egho-steps" aria-label="Hotel booking steps">
                @foreach ([
                    'Search',
                    'Results',
                    'Hotel details',
                    'Room & rate',
                    'Guest details',
                    'Review',
                    'Booking',
                    'Payment',
                    'Confirmation',
                ] as $step)
                    <li @class(['is-active' => $loop->first])>{{ $step }}</li>
                @endforeach
            </ol>

            @if (! $service['available'])
                <section class="egho-notice" role="status">
                    <span class="egho-notice-status">Not Configured</span>
                    <h2>Hotel service is not configured</h2>
                    <p>
                        Hotel search will be available after an approved
                        provider adapter and its required server configuration
                        are enabled.
                    </p>
                    <div class="egho-actions">
                        <a
                            href="{{ route('home') }}"
                            class="egho-btn egho-btn-ghost"
                        >
                            Back to travel services
                        </a>
                    </div>
                </section>

                {{--
                    Design preview only, local development only.

                    The search bar, the filter rail and the cards below are
                    static samples that show the result layout. They are not
                    live availability, they cannot be booked, and no price here
                    comes from a supplier.

                    Wiring point: once an approved hotel adapter is configured,
                    the branch above renders the real search form and the
                    `hotels.results` page renders real inventory inside the same
                    card and filter structure.
                --}}
                @if (app()->environment('local'))
                    <div class="egho-sample-note">
                        <span aria-hidden="true">&#9432;</span>
                        <span>
                            <strong>Layout preview.</strong>
                            Controls stay disabled and the cards are samples,
                            because no hotel provider is configured in this
                            environment. Nothing here is bookable.
                        </span>
                    </div>

                    <div
                        class="egho-searchbar"
                        role="group"
                        aria-label="Layout preview search controls (disabled)"
                    >
                        <label class="egho-field">
                            <span>Destination</span>
                            <input type="text" value="Sample destination" disabled>
                        </label>

                        <label class="egho-field">
                            <span>Check in</span>
                            <input
                                type="text"
                                value="{{ now()->addWeek()->format('d M') }}"
                                disabled
                            >
                        </label>

                        <label class="egho-field">
                            <span>Check out</span>
                            <input
                                type="text"
                                value="{{ now()->addWeek()->addDays(3)->format('d M') }}"
                                disabled
                            >
                        </label>

                        <label class="egho-field">
                            <span>Guests</span>
                            <input type="text" value="2 Guests" disabled>
                        </label>

                        <label class="egho-field">
                            <span>Rooms</span>
                            <input type="text" value="1 Room" disabled>
                        </label>

                        <div class="egho-actions">
                            <span
                                class="egho-btn egho-btn-primary"
                                aria-disabled="true"
                            >
                                Search
                            </span>
                        </div>
                    </div>

                    <div class="egho-results-layout">
                        @include('hotels._filters')

                        <section
                            class="egho-results"
                            aria-label="Hotel result layout preview"
                        >
                            @foreach ([
                                [
                                    'name' => 'Sample property one',
                                    'location' => 'Sample city, sample country',
                                    'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1000&q=85',
                                    'rating' => '4.6',
                                    'nightly' => 'BDT 45,000',
                                ],
                                [
                                    'name' => 'Sample property two',
                                    'location' => 'Sample city, sample country',
                                    'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1000&q=85',
                                    'rating' => '4.8',
                                    'nightly' => 'BDT 120,000',
                                ],
                                [
                                    'name' => 'Sample property three',
                                    'location' => 'Sample city, sample country',
                                    'image' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=1000&q=85',
                                    'rating' => '4.4',
                                    'nightly' => 'BDT 38,500',
                                ],
                            ] as $sample)
                                <article class="egho-result-card">
                                    <div class="egho-result-media">
                                        <span class="egho-sample-badge">
                                            Sample
                                        </span>
                                        <img
                                            src="{{ $sample['image'] }}"
                                            alt=""
                                            loading="lazy"
                                            width="900"
                                            height="600"
                                        >
                                    </div>

                                    <div class="egho-result-body">
                                        <h3>{{ $sample['name'] }}</h3>
                                        <p class="egho-result-location">
                                            {{ $sample['location'] }}
                                        </p>
                                        <span class="egho-rating">
                                            &#9733; {{ $sample['rating'] }}
                                            <em>Sample rating</em>
                                        </span>
                                        <div class="egho-tags">
                                            <span class="egho-tag egho-tag-muted">
                                                Sample layout
                                            </span>
                                        </div>
                                    </div>

                                    <div class="egho-result-price">
                                        <strong>{{ $sample['nightly'] }}</strong>
                                        <small>Preview only</small>
                                    </div>
                                </article>
                            @endforeach
                        </section>
                    </div>

                    <div class="egho-actions" style="margin-top:16px">
                        <a
                            href="{{ route('hotels.rooms') }}"
                            class="egho-btn egho-btn-ghost"
                        >
                            Preview room selection
                        </a>
                        <a
                            href="{{ route('hotels.booking') }}"
                            class="egho-btn egho-btn-ghost"
                        >
                            Preview guest details
                        </a>
                    </div>
                @endif
            @else
                <section class="egho-panel">
                    <div class="egho-panel-head">
                        <span class="egho-eyebrow">HOTEL SEARCH</span>
                        <h2>Search available stays</h2>
                        <p>
                            Availability, room types and rates come from the
                            configured provider. Nothing is assumed.
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('hotels.search') }}"
                        class="egho-search-form-grid"
                    >
                        @csrf

                        <label class="egho-field egho-field-wide">
                            <span>Destination</span>
                            <input
                                type="text"
                                name="destination"
                                value="{{ old('destination') }}"
                                maxlength="120"
                                autocomplete="off"
                                required
                            >
                        </label>

                        <label class="egho-field">
                            <span>Check in</span>
                            <input
                                type="date"
                                name="check_in"
                                value="{{ old('check_in') }}"
                                min="{{ now()->toDateString() }}"
                                required
                            >
                        </label>

                        <label class="egho-field">
                            <span>Check out</span>
                            <input
                                type="date"
                                name="check_out"
                                value="{{ old('check_out') }}"
                                min="{{ now()->addDay()->toDateString() }}"
                                required
                            >
                        </label>

                        <label class="egho-field">
                            <span>Adults</span>
                            <select name="adults" required>
                                @for ($adults = 1; $adults <= 9; $adults++)
                                    <option value="{{ $adults }}">
                                        {{ $adults }}
                                    </option>
                                @endfor
                            </select>
                        </label>

                        <label class="egho-field">
                            <span>Rooms</span>
                            <select name="rooms" required>
                                @for ($rooms = 1; $rooms <= 5; $rooms++)
                                    <option value="{{ $rooms }}">
                                        {{ $rooms }}
                                    </option>
                                @endfor
                            </select>
                        </label>

                        <button
                            type="submit"
                            class="egho-btn egho-btn-primary"
                        >
                            Search
                        </button>
                    </form>
                </section>
            @endif

        </div>
    </main>

@endsection
