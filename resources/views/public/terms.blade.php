@extends('layouts.site')

@section('title', 'Terms & Policies')
@section(
    'meta_description',
    'Terms and policy notes for using the Eagle Global Hub LTD travel website.'
)
@section('body_class', 'public-page-body terms-page-body')

@section('content')

    <main class="egho-doc">

        <div class="egho-shell">

            <section class="egho-doc-hero">
                <span class="egho-eyebrow">
                    TERMS &amp; POLICIES
                </span>

                <h1>
                    Website terms and booking notices
                </h1>

                <p>
                    These customer-facing notices explain how the website presents
                    flight search, booking status and supplier-dependent execution
                    without replacing formal supplier or airline terms.
                </p>
            </section>

            <section class="egho-doc-stack">

                <article class="egho-doc-card">
                    <h2>Flight Availability</h2>

                    <p>
                        Flight availability, fares and payment readiness are
                        dependent on the configured data source. Fixture or sandbox
                        data must not be treated as live airline inventory.
                    </p>
                </article>

                <article class="egho-doc-card">
                    <h2>Booking Confirmation</h2>

                    <p>
                        Customer booking confirmations show stored order and payment
                        status where available. They are not airline-issued tickets
                        unless a supplier-issued ticket document is explicitly
                        provided.
                    </p>
                </article>

                <article class="egho-doc-card">
                    <h2>Privacy Notice</h2>

                    <p>
                        Protected booking pages require account access and are
                        scoped to the signed-in customer. The public website does
                        not display supplier credentials or unsafe supplier
                        identifiers.
                    </p>
                </article>

            </section>

            <p class="egho-doc-note">
                These notices describe how this website presents travel data. They
                do not replace the terms, fare rules or ticket conditions issued by
                the airline or supplier that operates your journey.
            </p>

        </div>

    </main>

@endsection
