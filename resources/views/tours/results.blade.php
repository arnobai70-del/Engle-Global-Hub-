@extends('layouts.site')

@section('title', 'Tour Results')

@section('body_class', 'egho-page-body')

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            <form
                method="POST"
                action="{{ route('tours.search') }}"
                class="egho-searchbar"
                aria-label="Tour search"
            >
                @csrf

                <label class="egho-field">
                    <span>Destination</span>
                    <input
                        type="text"
                        name="destination"
                        value="{{ $criteria['destination'] }}"
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
                        value="{{ $criteria['travel_date'] ?? '' }}"
                    >
                </label>

                <label class="egho-field">
                    <span>Travelers</span>
                    <select name="travelers" required>
                        @for ($travelers = 1; $travelers <= 12; $travelers++)
                            <option
                                value="{{ $travelers }}"
                                @selected(
                                    (int) $criteria['travelers'] === $travelers
                                )
                            >
                                {{ $travelers }}
                                {{ $travelers === 1 ? 'Traveler' : 'Travelers' }}
                            </option>
                        @endfor
                    </select>
                </label>

                <div class="egho-actions">
                    <button type="submit" class="egho-btn egho-btn-primary">
                        Search
                    </button>
                </div>
            </form>

            <div class="egho-results-head">
                <h2>Tours in {{ $criteria['destination'] }}</h2>
                <small>
                    {{ $criteria['travelers'] }} traveler(s)
                    @if ($criteria['travel_date'] ?? null)
                        &middot; {{ $criteria['travel_date'] }}
                    @endif
                </small>
            </div>

            <div class="egho-results-layout">

                @include('tours._filters')

                <section class="egho-results" aria-label="Tour results">
                    @if ($tours === [])
                        <section class="egho-notice" role="status">
                            <span class="egho-notice-status">
                                No inventory returned
                            </span>
                            <h2>No tours were returned</h2>
                            <p>
                                The configured provider returned no matching
                                tours. No availability, price or booking has
                                been assumed.
                            </p>
                            <div class="egho-actions">
                                <a
                                    href="{{ route('tours.index') }}"
                                    class="egho-btn egho-btn-ghost"
                                >
                                    Change search
                                </a>
                            </div>
                        </section>
                    @else
                        @foreach ($tours as $tour)
                            <article class="egho-result-card">
                                <div class="egho-result-media">
                                    <span
                                        class="egho-result-media-glyph"
                                        aria-hidden="true"
                                    >
                                        &#127796;
                                    </span>
                                </div>

                                <div class="egho-result-body">
                                    <h3>{{ $tour['title'] }}</h3>
                                    <p class="egho-result-location">
                                        {{ $tour['location'] }}
                                    </p>
                                    @if ($tour['summary'] !== '')
                                        <p class="egho-result-summary">
                                            {{ $tour['summary'] }}
                                        </p>
                                    @endif
                                    <div class="egho-tags">
                                        <span class="egho-tag egho-tag-muted">
                                            Availability requires provider
                                            integration
                                        </span>
                                    </div>
                                </div>

                                <div class="egho-result-price">
                                    <small>Price</small>
                                    <strong>Provider quote</strong>
                                    <small>Confirmed at booking</small>
                                </div>
                            </article>
                        @endforeach
                    @endif
                </section>

            </div>

        </div>
    </main>

@endsection
