@extends('layouts.site')

@section('title', 'Hotel Guest Details')

@section(
    'meta_description',
    'Hotel guest details step for hotel bookings prepared with Eagle Global Hub LTD.'
)

@section('body_class', 'egho-page-body')

@php
    /*
     * Hotel screens carry their own stylesheet so a change here cannot reach
     * another page. It has no content hash, so the modification time is
     * appended to keep a cached copy from outliving an update.
     */
    $hotelCss = 'css/egh-hotels.css';
    $hotelCssVersion = @filemtime(public_path($hotelCss));
@endphp

@push('head')
    <link
        rel="stylesheet"
        href="{{ asset($hotelCss).($hotelCssVersion ? '?v='.$hotelCssVersion : '') }}"
    >
@endpush

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            <header class="egho-page-head">
                <span class="egho-eyebrow">HOTEL BOOKING</span>
                <h1>Guest details for your stay</h1>
                <p>
                    Where the room, the guest names and the contact details for
                    a stay are confirmed before payment. No guest record and no
                    booking can be created until a hotel provider is connected.
                </p>
            </header>

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
                        'is-done' => $loop->index < 2,
                        'is-active' => $loop->index === 2,
                    ])>
                        {{ $step }}
                    </li>
                @endforeach
            </ol>

            <section class="egho-notice" role="status">
                <span class="egho-notice-status">Not Configured</span>
                <h2>Hotel guest details and payment are not configured</h2>
                <p>
                    Guest details are only collected as part of a real hotel
                    booking, and a booking can only be created once an approved
                    hotel provider and a payment method are enabled.
                </p>
                <p>
                    Nothing typed on this page is stored or sent anywhere, and no
                    reservation or charge can be created from it.
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

                The guest form, the hotel summary and the totals below are
                static placeholders used to review the approved layout. The form
                is disabled, nothing is submitted, and no figure here is a real
                quote.

                Wiring point: the selected room and rate reference from the room
                selection step supply the summary, and the guest details feed the
                booking and payment steps once those are enabled.
            --}}
            @if (app()->environment('local'))
                <div class="egho-sample-note">
                    <span aria-hidden="true">&#9432;</span>
                    <span>
                        <strong>Layout preview.</strong>
                        The guest form is disabled and the summary figures are
                        samples. Nothing on this page can be submitted, booked or
                        charged.
                    </span>
                </div>

                <div class="egho-booking-layout">

                    <section class="egho-contact-card">
                        <h2>Guest details</h2>
                        <p class="egho-result-summary">
                            Primary guest for this placeholder stay. Real fields
                            are validated against the selected room and rate
                            before a booking is created.
                        </p>

                        <div class="egho-form-grid">
                            <label class="egho-field">
                                <span>First name</span>
                                <input type="text" disabled>
                            </label>

                            <label class="egho-field">
                                <span>Last name</span>
                                <input type="text" disabled>
                            </label>

                            <label class="egho-field">
                                <span>Email address</span>
                                <input type="email" disabled>
                            </label>

                            <label class="egho-field">
                                <span>Phone number</span>
                                <input type="tel" disabled>
                            </label>

                            <label class="egho-field egho-field-wide">
                                <span>Special requests (optional)</span>
                                <textarea class="egho-textarea" disabled></textarea>
                            </label>
                        </div>

                        <p class="egho-filter-note">
                            Special requests are passed to the property when a
                            booking is created and are never guaranteed.
                        </p>
                    </section>

                    <aside class="egho-summary-card">
                        <h2>Hotel summary</h2>

                        <div class="egho-summary-hotel">
                            <div class="egho-summary-thumb">
                                <img
                                    src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=400&q=85"
                                    alt=""
                                    loading="lazy"
                                    width="400"
                                    height="400"
                                >
                            </div>
                            <div>
                                <strong>Sample hotel name</strong>
                                <small>Sample city, sample country</small>
                            </div>
                        </div>

                        <div class="egho-summary-row">
                            <span>Check in</span>
                            <span>
                                {{ now()->addWeek()->format('d M Y') }}
                                &middot; 14:00
                            </span>
                        </div>

                        <div class="egho-summary-row">
                            <span>Check out</span>
                            <span>
                                {{ now()->addWeek()->addDays(3)->format('d M Y') }}
                                &middot; 12:00
                            </span>
                        </div>

                        <div class="egho-summary-row">
                            <span>Rooms &amp; guests</span>
                            <span>1 room &middot; 2 adults &middot; 3 nights</span>
                        </div>

                        <div class="egho-summary-row">
                            <span>Room rate</span>
                            <span>BDT 45,000 / night</span>
                        </div>

                        <div class="egho-summary-row">
                            <span>Taxes &amp; fees</span>
                            <span>BDT 8,200</span>
                        </div>

                        <div class="egho-summary-total">
                            <span>Total</span>
                            <strong>BDT 135,000</strong>
                        </div>

                        <div class="egho-summary-actions">
                            <button
                                type="button"
                                class="egho-btn egho-btn-primary"
                                disabled
                            >
                                Continue to payment
                            </button>
                            <small>
                                Sample figures. Payment is not connected, so no
                                amount can be charged and no booking is created.
                            </small>
                        </div>
                    </aside>

                </div>
            @endif

        </div>
    </main>

@endsection
