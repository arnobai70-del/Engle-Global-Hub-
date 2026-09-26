@extends('layouts.site')

@section('title', 'Hotel Results')

@section('body_class', 'egho-page-body')

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            @php
                $nights = (int) \Illuminate\Support\Carbon::parse(
                    $criteria['check_in']
                )->diffInDays(
                    \Illuminate\Support\Carbon::parse($criteria['check_out'])
                );
            @endphp

            <form
                method="POST"
                action="{{ route('hotels.search') }}"
                class="egho-searchbar"
                aria-label="Hotel search"
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
                    <span>Check in</span>
                    <input
                        type="date"
                        name="check_in"
                        value="{{ $criteria['check_in'] }}"
                        required
                    >
                </label>

                <label class="egho-field">
                    <span>Check out</span>
                    <input
                        type="date"
                        name="check_out"
                        value="{{ $criteria['check_out'] }}"
                        required
                    >
                </label>

                <label class="egho-field">
                    <span>Guests</span>
                    <select name="adults" required>
                        @for ($adults = 1; $adults <= 9; $adults++)
                            <option
                                value="{{ $adults }}"
                                @selected((int) $criteria['adults'] === $adults)
                            >
                                {{ $adults }}
                                {{ $adults === 1 ? 'Guest' : 'Guests' }}
                            </option>
                        @endfor
                    </select>
                </label>

                <label class="egho-field">
                    <span>Rooms</span>
                    <select name="rooms" required>
                        @for ($rooms = 1; $rooms <= 5; $rooms++)
                            <option
                                value="{{ $rooms }}"
                                @selected((int) $criteria['rooms'] === $rooms)
                            >
                                {{ $rooms }}
                                {{ $rooms === 1 ? 'Room' : 'Rooms' }}
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
                <h2>Stays in {{ $criteria['destination'] }}</h2>
                <small>
                    {{ $criteria['check_in'] }} to {{ $criteria['check_out'] }}
                    &middot; {{ $criteria['adults'] }} guest(s)
                    &middot; {{ $criteria['rooms'] }} room(s)
                    @if ($nights > 0)
                        &middot; {{ $nights }} night(s)
                    @endif
                </small>
            </div>

            <div class="egho-results-layout">

                @include('hotels._filters')

                <section class="egho-results" aria-label="Hotel results">
                    @if ($hotels === [])
                        <section class="egho-notice" role="status">
                            <span class="egho-notice-status">
                                No inventory returned
                            </span>
                            <h2>No hotel stays were returned</h2>
                            <p>
                                The configured provider returned no matching
                                inventory for this search.
                                No availability or price has been assumed.
                            </p>
                            <div class="egho-actions">
                                <a
                                    href="{{ route('hotels.index') }}"
                                    class="egho-btn egho-btn-ghost"
                                >
                                    Change search
                                </a>
                            </div>
                        </section>
                    @else
                        @foreach ($hotels as $hotel)
                            <article class="egho-result-card">
                                <div class="egho-result-media">
                                    <span
                                        class="egho-result-media-glyph"
                                        aria-hidden="true"
                                    >
                                        &#127976;
                                    </span>
                                </div>

                                <div class="egho-result-body">
                                    <h3>{{ $hotel['name'] }}</h3>
                                    <p class="egho-result-location">
                                        {{ $hotel['location'] }}
                                    </p>
                                    @if ($hotel['summary'] !== '')
                                        <p class="egho-result-summary">
                                            {{ $hotel['summary'] }}
                                        </p>
                                    @endif
                                    <div class="egho-tags">
                                        <span class="egho-tag egho-tag-muted">
                                            Rates require room selection
                                        </span>
                                    </div>
                                </div>

                                <div class="egho-result-price">
                                    <small>Rate</small>
                                    <strong>Provider quote</strong>
                                    <small>Released at room selection</small>
                                </div>
                            </article>
                        @endforeach
                    @endif
                </section>

            </div>

        </div>
    </main>

@endsection
