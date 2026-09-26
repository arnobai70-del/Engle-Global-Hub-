@extends('layouts.site')

@section('title', 'Visa Services')

@section(
    'meta_description',
    'Visa information services from Eagle Global Hub LTD.'
)

@section('body_class', 'egho-page-body')

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            <div class="egho-visa-grid">

                <section class="egho-visa-card">
                    <span class="egho-eyebrow">VISA REQUIREMENTS</span>
                    <h1>Check visa requirements for your next destination</h1>
                    <p>
                        Travel-document information comes from a configured
                        source and uses your passport nationality and actual
                        journey details.
                        Approval and entry are never guaranteed.
                    </p>

                    @if (! $service['available'])
                        <div class="egho-visa-primary-fields">
                            <label class="egho-field">
                                <span>
                                    I am a citizen of
                                    <small class="egho-field-help">Passport nationality</small>
                                </span>
                                <select disabled>
                                    <option>Selected when the service is active</option>
                                </select>
                            </label>

                            <label class="egho-field">
                                <span>
                                    I want to travel to
                                    <small class="egho-field-help">Destination country</small>
                                </span>
                                <select disabled>
                                    <option>Selected when the service is active</option>
                                </select>
                            </label>
                        </div>

                        <div class="egho-notice" role="status" style="margin-top:18px">
                            <span class="egho-notice-status">Not Configured</span>
                            <h2>Visa information service is not configured</h2>
                            <p>
                                Visa requirements will be available only after
                                an approved information provider and its
                                required server configuration are enabled.
                            </p>
                            <div class="egho-actions">
                                <a
                                    href="{{ route('home') }}"
                                    class="egho-btn egho-btn-ghost"
                                >
                                    Back to travel services
                                </a>
                            </div>
                        </div>
                    @elseif ($countries->isEmpty())
                        <div class="egho-notice" role="status">
                            <span class="egho-notice-status">Unavailable</span>
                            <h2>Country information is unavailable</h2>
                            <p>
                                Visa lookup cannot continue until the active
                                country catalogue is available.
                            </p>
                        </div>
                    @else
                        <form
                            method="POST"
                            action="{{ route('visa.requirements') }}"
                        >
                            @csrf

                            <div class="egho-visa-primary-fields">
                                <label class="egho-field">
                                    <span>
                                        I am a citizen of
                                        <small class="egho-field-help">Passport nationality</small>
                                    </span>
                                    <select name="nationality" required>
                                        <option value="">Select country</option>
                                        @foreach ($countries as $country)
                                            <option
                                                value="{{ $country->iso3 }}"
                                                @selected(
                                                    old('nationality') === $country->iso3
                                                )
                                            >
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="egho-field">
                                    <span>
                                        I want to travel to
                                        <small class="egho-field-help">Destination country</small>
                                    </span>
                                    <select
                                        name="destination_country"
                                        required
                                    >
                                        <option value="">Select country</option>
                                        @foreach ($countries as $country)
                                            <option
                                                value="{{ $country->iso3 }}"
                                                @selected(
                                                    old('destination_country') === $country->iso3
                                                )
                                            >
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>

                            <div
                                class="egho-visa-primary-fields"
                                style="margin-top:14px"
                            >
                                <label class="egho-field">
                                    <span>Origin country</span>
                                    <select name="origin_country" required>
                                        <option value="">Select country</option>
                                        @foreach ($countries as $country)
                                            <option
                                                value="{{ $country->iso3 }}"
                                                @selected(
                                                    old('origin_country') === $country->iso3
                                                )
                                            >
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="egho-field">
                                    <span>Departure date</span>
                                    <input
                                        type="date"
                                        name="departure_date"
                                        value="{{ old('departure_date') }}"
                                        min="{{ now()->toDateString() }}"
                                        required
                                    >
                                </label>

                                <label class="egho-field">
                                    <span>Departure time</span>
                                    <input
                                        type="time"
                                        name="departure_time"
                                        value="{{ old('departure_time') }}"
                                        required
                                    >
                                </label>

                                <label class="egho-field">
                                    <span>Arrival date</span>
                                    <input
                                        type="date"
                                        name="arrival_date"
                                        value="{{ old('arrival_date') }}"
                                        min="{{ now()->toDateString() }}"
                                        required
                                    >
                                </label>

                                <label class="egho-field">
                                    <span>Arrival time</span>
                                    <input
                                        type="time"
                                        name="arrival_time"
                                        value="{{ old('arrival_time') }}"
                                        required
                                    >
                                </label>
                            </div>

                            <div class="egho-actions" style="margin-top:18px">
                                <button
                                    type="submit"
                                    class="egho-btn egho-btn-primary"
                                >
                                    Check Requirements
                                </button>
                            </div>
                        </form>

                        <p class="egho-filter-note">
                            This service provides travel-requirement
                            information only. It does not guarantee visa
                            approval or admission at the border.
                        </p>
                    @endif
                </section>

                <div class="egho-visa-media">
                    <img
                        src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=1400&q=85"
                        alt=""
                        loading="lazy"
                        width="1400"
                        height="1750"
                    >
                </div>

            </div>

            <section
                class="egho-visa-types"
                aria-label="Visa categories"
            >
                @foreach ([
                    [
                        'icon' => '&#127968;',
                        'label' => 'Tourist Visa',
                        'note' => 'Short visits, holidays and family trips.',
                    ],
                    [
                        'icon' => '&#128188;',
                        'label' => 'Business Visa',
                        'note' => 'Meetings, conferences and trade visits.',
                    ],
                    [
                        'icon' => '&#9992;',
                        'label' => 'Transit Visa',
                        'note' => 'Short layovers through a third country.',
                    ],
                    [
                        'icon' => '&#127891;',
                        'label' => 'Student Visa',
                        'note' => 'Study and academic exchange programmes.',
                    ],
                    [
                        'icon' => '&#128084;',
                        'label' => 'Work Visa',
                        'note' => 'Employment and employer-sponsored permits.',
                    ],
                ] as $category)
                    <article class="egho-visa-type">
                        <span aria-hidden="true">{{ $category['icon'] }}</span>
                        <strong>{{ $category['label'] }}</strong>
                        <small>{{ $category['note'] }}</small>
                    </article>
                @endforeach
            </section>

            <p class="egho-filter-note">
                Requirements can change. This information does not guarantee
                approval, boarding, or admission.
            </p>

        </div>
    </main>

@endsection
