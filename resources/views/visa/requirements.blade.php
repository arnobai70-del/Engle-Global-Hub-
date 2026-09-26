@extends('layouts.site')

@section('title', 'Visa Requirements')

@section('body_class', 'egho-page-body')

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            <header class="egho-page-head">
                <span class="egho-eyebrow">VISA INFORMATION</span>
                <h1>{{ $criteria['destination_country'] }}</h1>
                <p>
                    Passport: {{ $criteria['nationality'] }}
                    &middot;
                    {{ $criteria['origin_country'] }}
                    to
                    {{ $criteria['destination_country'] }}
                </p>
                <p>
                    Departure:
                    {{ $criteria['departure_date'] }}
                    {{ $criteria['departure_time'] }}
                    &middot;
                    Arrival:
                    {{ $criteria['arrival_date'] }}
                    {{ $criteria['arrival_time'] }}
                </p>
            </header>

            @if (
                $information['summary'] === '' &&
                $information['requirements'] === [] &&
                $information['documents'] === []
            )
                <section class="egho-notice" role="status">
                    <span class="egho-notice-status">
                        No information returned
                    </span>
                    <h2>No visa information was returned</h2>
                    <p>
                        The configured source returned no visa requirements for
                        this trip. Eligibility, documents and approval have not
                        been assumed. Do not submit an application based on
                        missing information.
                    </p>
                    <div class="egho-actions">
                        <a
                            href="{{ route('visa.index') }}"
                            class="egho-btn egho-btn-ghost"
                        >
                            Check another trip
                        </a>
                    </div>
                </section>
            @else
                @if ($information['summary'] !== '')
                    <section class="egho-notice" role="status">
                        <span class="egho-notice-status egho-notice-status-is-ready">
                            Trip summary
                        </span>
                        <h2>Trip summary</h2>
                        <p>{{ $information['summary'] }}</p>
                    </section>
                @endif

                <div class="egho-contact-grid" style="margin-top:18px">
                    <article class="egho-contact-card">
                        <h2>Requirements</h2>

                        @if ($information['requirements'] === [])
                            <p class="egho-result-summary">
                                No requirement details were returned.
                            </p>
                        @else
                            <ul class="egho-visa-list">
                                @foreach (
                                    $information['requirements']
                                    as $requirement
                                )
                                    <li>{{ $requirement }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </article>

                    <article class="egho-contact-card">
                        <h2>Documents</h2>

                        @if ($information['documents'] === [])
                            <p class="egho-result-summary">
                                No document types were returned.
                            </p>
                        @else
                            <ul class="egho-visa-list">
                                @foreach (
                                    $information['documents']
                                    as $document
                                )
                                    <li>{{ $document }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                </div>

                <p class="egho-filter-note">
                    Requirements can change. This information does not guarantee
                    approval, boarding, or admission.
                </p>
            @endif

        </div>
    </main>

@endsection
