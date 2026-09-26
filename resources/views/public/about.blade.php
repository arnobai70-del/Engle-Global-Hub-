@extends('layouts.site')

@section('title', 'About')
@section(
    'meta_description',
    'Learn about the Eagle Global Hub LTD flight-first travel booking experience.'
)
@section('body_class', 'public-page-body')

@section('content')

    <main class="egho-doc">

        <div class="egho-shell">

            <section class="egho-doc-hero">
                <span class="egho-eyebrow">
                    ABOUT
                </span>

                <h1>
                    Eagle Global Hub LTD
                </h1>

                <p>
                    Eagle Global Hub LTD provides a flight-first travel experience
                    built around clear search, careful review, secure account
                    access and visible booking progress.
                </p>

                <div class="egho-doc-badges">
                    <span>Flight first</span>
                    <span>Secure accounts</span>
                    <span>Honest availability</span>
                </div>
            </section>

            <section class="egho-doc-grid">

                <article class="egho-doc-card">
                    <span class="egho-doc-card-num" aria-hidden="true">01</span>

                    <h2>What We Support</h2>

                    <p>
                        The current website focuses on flight search, traveler
                        review, order status, payment status and customer booking
                        confirmation pages where stored data is available.
                    </p>
                </article>

                <article class="egho-doc-card">
                    <span class="egho-doc-card-num" aria-hidden="true">02</span>

                    <h2>How We Present Travel Data</h2>

                    <p>
                        Availability and execution depend on the configured flight
                        data source. Development and fixture results are labelled
                        separately from live supplier inventory.
                    </p>
                </article>

                <article class="egho-doc-card">
                    <span class="egho-doc-card-num" aria-hidden="true">03</span>

                    <h2>Additional Services</h2>

                    <p>
                        Hotels, tours and visa discovery is shown as available only
                        when its provider integration is configured. Transactional
                        booking or application access is not implied.
                    </p>
                </article>

            </section>

        </div>

    </main>

@endsection
