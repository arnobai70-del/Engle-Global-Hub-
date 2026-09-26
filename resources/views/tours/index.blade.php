@extends('layouts.site')

@section('title', 'Tours')

@section(
    'meta_description',
    'Tour search services from Eagle Global Hub LTD.'
)

@section('body_class', 'egho-page-body')

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            <header class="egho-page-head">
                <span class="egho-eyebrow">TOURS &amp; ACTIVITIES</span>
                <h1>Explore tours for your destination</h1>
                <p>
                    Search a configured tour provider and review genuine
                    availability before entering traveler details.
                </p>
            </header>

            <ol class="egho-steps" aria-label="Tour booking steps">
                @foreach ([
                    'Search',
                    'Tour details',
                    'Availability',
                    'Traveler details',
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
                    <h2>Tour service is not configured</h2>
                    <p>
                        Tour search will be available after an approved provider
                        adapter and its required server configuration are
                        enabled.
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

                    Static sample activities that show the result layout. They
                    are not live availability and cannot be booked.

                    Wiring point: `tours.results` renders real provider
                    inventory inside the same card, search bar and filter
                    structure once an approved adapter is configured.
                --}}
                @if (app()->environment('local'))
                    <div class="egho-sample-note">
                        <span aria-hidden="true">&#9432;</span>
                        <span>
                            <strong>Layout preview.</strong>
                            Controls stay disabled and the activities are
                            samples, because no tour provider is configured in
                            this environment. Nothing here is bookable.
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
                            <span>Preferred date</span>
                            <input
                                type="text"
                                value="{{ now()->addWeek()->format('d M') }}"
                                disabled
                            >
                        </label>

                        <label class="egho-field">
                            <span>Travelers</span>
                            <input type="text" value="2 Travelers" disabled>
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
                        @include('tours._filters')

                        <section
                            class="egho-results"
                            aria-label="Tour result layout preview"
                        >
                            @foreach ([
                                [
                                    'title' => 'Sample desert safari activity',
                                    'summary' => 'Sample itinerary copy for layout review only.',
                                    'duration' => 'Sample duration',
                                    'image' => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?auto=format&fit=crop&w=900&q=80',
                                    'price' => 'BDT 6,500',
                                ],
                                [
                                    'title' => 'Sample city landmark activity',
                                    'summary' => 'Sample itinerary copy for layout review only.',
                                    'duration' => 'Sample duration',
                                    'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=900&q=80',
                                    'price' => 'BDT 6,200',
                                ],
                                [
                                    'title' => 'Sample city tour activity',
                                    'summary' => 'Sample itinerary copy for layout review only.',
                                    'duration' => 'Sample duration',
                                    'image' => 'https://images.unsplash.com/photo-1518684079-3c830dcef090?auto=format&fit=crop&w=900&q=80',
                                    'price' => 'BDT 5,800',
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
                                        <h3>{{ $sample['title'] }}</h3>
                                        <p class="egho-result-summary">
                                            {{ $sample['summary'] }}
                                        </p>
                                        <div class="egho-tags">
                                            <span class="egho-tag">
                                                {{ $sample['duration'] }}
                                            </span>
                                            <span class="egho-tag egho-tag-muted">
                                                Sample layout
                                            </span>
                                        </div>
                                    </div>

                                    <div class="egho-result-price">
                                        <strong>{{ $sample['price'] }}</strong>
                                        <small>Preview only</small>
                                    </div>
                                </article>
                            @endforeach
                        </section>
                    </div>
                @endif
            @else
                <section class="egho-panel">
                    <div class="egho-panel-head">
                        <span class="egho-eyebrow">TOUR SEARCH</span>
                        <h2>Search available tours</h2>
                        <p>
                            Availability and pricing come from the configured
                            provider. Nothing is assumed.
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('tours.search') }}"
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
                            <span>Preferred date</span>
                            <input
                                type="date"
                                name="travel_date"
                                value="{{ old('travel_date') }}"
                                min="{{ now()->toDateString() }}"
                            >
                        </label>

                        <label class="egho-field">
                            <span>Travelers</span>
                            <select name="travelers" required>
                                @for ($travelers = 1; $travelers <= 12; $travelers++)
                                    <option value="{{ $travelers }}">
                                        {{ $travelers }}
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
