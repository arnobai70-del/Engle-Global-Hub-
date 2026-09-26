@extends('layouts.site')

@section('title', 'My Bookings')
@section('body_class', 'bookings-body egho-page-body')

@section('content')

    @php
        $activeTab = request()->query('tab', 'flights');

        $tabs = [
            'flights' => 'Flights',
            'hotels' => 'Hotels',
            'tours' => 'Tours',
            'visa' => 'Visa',
            'work-visa' => 'Work Visa',
        ];

        if (! array_key_exists($activeTab, $tabs)) {
            $activeTab = 'flights';
        }
    @endphp

    <main class="egho-page">
        <div class="egho-shell">

            <header class="egho-page-head">
                <span class="egho-eyebrow">TRAVEL HISTORY</span>
                <h1>My Bookings</h1>
                <p>
                    Review stored records created from your account, including
                    order and payment status. Only records this website actually
                    holds are listed.
                </p>
            </header>

            <ul class="egho-tabs" aria-label="Booking types">
                <li @class(['egho-tab', 'is-active' => $activeTab === 'flights'])>
                    <a href="{{ route('bookings.index') }}">Flights</a>
                </li>

                @feature('hotels')
                    <li @class(['egho-tab', 'is-active' => $activeTab === 'hotels'])>
                        <a href="{{ route('bookings.index', ['tab' => 'hotels']) }}">
                            Hotels
                        </a>
                    </li>
                @endfeature

                @feature('tours')
                    <li @class(['egho-tab', 'is-active' => $activeTab === 'tours'])>
                        <a href="{{ route('bookings.index', ['tab' => 'tours']) }}">
                            Tours
                        </a>
                    </li>
                @endfeature

                @feature('visa')
                    <li @class(['egho-tab', 'is-active' => $activeTab === 'visa'])>
                        <a href="{{ route('bookings.index', ['tab' => 'visa']) }}">
                            Visa
                        </a>
                    </li>

                    <li @class(['egho-tab', 'is-active' => $activeTab === 'work-visa'])>
                        <a href="{{ route('bookings.index', ['tab' => 'work-visa']) }}">
                            Work Visa
                        </a>
                    </li>
                @endfeature
            </ul>

            @if ($activeTab === 'flights')

                @if ($bookings->isEmpty())
                    <section class="egho-notice" role="status">
                        <span class="egho-notice-status">
                            No stored records
                        </span>
                        <h2>No flight bookings yet</h2>
                        <p>
                            Your confirmed order attempts and payment status will
                            appear here after you complete the secure flight
                            booking flow.
                        </p>
                        @can('flights.search')
                            <div class="egho-actions">
                                <a
                                    href="{{ route('flights.index') }}"
                                    class="egho-btn egho-btn-primary"
                                >
                                    Start Flight Search
                                </a>
                            </div>
                        @endcan
                    </section>
                @else
                    <section
                        class="egho-bookings"
                        aria-label="Flight booking list"
                    >
                        @foreach ($bookings as $booking)
                            @php
                                $payment = $booking->paymentAttempt;

                                $orderStatus = match ($booking->status) {
                                    \App\Models\FlightOrderAttempt::STATUS_CREATED => 'Order Created',
                                    \App\Models\FlightOrderAttempt::STATUS_FAILED => 'Order Failed',
                                    default => 'Order Processing',
                                };

                                $orderTone = match ($booking->status) {
                                    \App\Models\FlightOrderAttempt::STATUS_CREATED => 'egho-status-ok',
                                    \App\Models\FlightOrderAttempt::STATUS_FAILED => 'egho-status-bad',
                                    default => 'egho-status-warn',
                                };

                                $paymentStatus = match ($payment?->status) {
                                    \App\Models\FlightOrderPaymentAttempt::STATUS_SUCCEEDED => 'Payment Succeeded',
                                    \App\Models\FlightOrderPaymentAttempt::STATUS_FAILED => 'Payment Failed',
                                    \App\Models\FlightOrderPaymentAttempt::STATUS_PROCESSING => 'Payment Processing',
                                    default => 'Payment Not Started',
                                };

                                $paymentTone = match ($payment?->status) {
                                    \App\Models\FlightOrderPaymentAttempt::STATUS_SUCCEEDED => 'egho-status-ok',
                                    \App\Models\FlightOrderPaymentAttempt::STATUS_FAILED => 'egho-status-bad',
                                    \App\Models\FlightOrderPaymentAttempt::STATUS_PROCESSING => 'egho-status-info',
                                    default => 'egho-status-muted',
                                };
                            @endphp

                            <article class="egho-booking">
                                <div class="egho-booking-ref">
                                    <strong>Booking #{{ $booking->id }}</strong>
                                    <small>Flight booking</small>
                                </div>

                                <div class="egho-booking-desc">
                                    <strong>Flight order attempt</strong>
                                    <small>
                                        @if ($payment)
                                            {{ $payment->currency }} {{ $payment->amount }}
                                            &middot; stored payment record
                                        @else
                                            No payment record stored yet
                                        @endif
                                    </small>
                                </div>

                                <div>
                                    <span @class(['egho-status', $orderTone])>
                                        {{ $orderStatus }}
                                    </span>
                                </div>

                                <div>
                                    <span @class(['egho-status', $paymentTone])>
                                        {{ $paymentStatus }}
                                    </span>
                                </div>

                                <div class="egho-booking-date">
                                    {{ $booking->created_at?->format('M j, Y g:i A') ?? 'Not available' }}
                                </div>

                                <div class="egho-booking-action">
                                    <a
                                        href="{{ route('bookings.show', $booking) }}"
                                        class="egho-btn egho-btn-ghost"
                                    >
                                        View Details
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </section>

                    @if ($bookings->hasPages())
                        <nav
                            class="booking-pagination"
                            aria-label="Booking list pagination"
                        >
                            <p>
                                Showing {{ $bookings->firstItem() }}&ndash;{{ $bookings->lastItem() }}
                                of {{ $bookings->total() }} bookings
                            </p>

                            <div>
                                @if ($bookings->onFirstPage())
                                    <span aria-disabled="true">Previous</span>
                                @else
                                    <a href="{{ $bookings->previousPageUrl() }}" rel="prev">
                                        Previous
                                    </a>
                                @endif

                                <strong aria-current="page">
                                    Page {{ $bookings->currentPage() }} of {{ $bookings->lastPage() }}
                                </strong>

                                @if ($bookings->hasMorePages())
                                    <a href="{{ $bookings->nextPageUrl() }}" rel="next">
                                        Next
                                    </a>
                                @else
                                    <span aria-disabled="true">Next</span>
                                @endif
                            </div>
                        </nav>
                    @endif
                @endif

            @elseif ($activeTab === 'hotels')
                <section class="egho-notice" role="status">
                    <span class="egho-notice-status">Not Connected</span>
                    <h2>No hotel bookings are stored yet</h2>
                    <p>
                        Hotel booking is not connected to this website, so no
                        hotel reservation records exist for your account. Hotel
                        search and the room and guest steps are shown as layout
                        previews only.
                    </p>
                    @feature('hotels')
                        <div class="egho-actions">
                            <a
                                href="{{ route('hotels.index') }}"
                                class="egho-btn egho-btn-ghost"
                            >
                                Open hotel search
                            </a>
                        </div>
                    @endfeature
                </section>

            @elseif ($activeTab === 'tours')
                <section class="egho-notice" role="status">
                    <span class="egho-notice-status">Not Connected</span>
                    <h2>No tour bookings are stored yet</h2>
                    <p>
                        Tour booking is not connected to this website, so no tour
                        reservation records exist for your account. Tour search
                        is shown as a layout preview only.
                    </p>
                    @feature('tours')
                        <div class="egho-actions">
                            <a
                                href="{{ route('tours.index') }}"
                                class="egho-btn egho-btn-ghost"
                            >
                                Open tour search
                            </a>
                        </div>
                    @endfeature
                </section>

            @elseif ($activeTab === 'visa')
                <section class="egho-notice" role="status">
                    <span class="egho-notice-status">Not Connected</span>
                    <h2>No visa applications are stored yet</h2>
                    <p>
                        Visa information is provided as a lookup only. No visa
                        application, document or fee is recorded for your
                        account, and this website cannot submit an application.
                    </p>
                    @feature('visa')
                        <div class="egho-actions">
                            <a
                                href="{{ route('visa.index') }}"
                                class="egho-btn egho-btn-ghost"
                            >
                                Check visa requirements
                            </a>
                        </div>
                    @endfeature
                </section>

            @else
                <section class="egho-notice" role="status">
                    <span class="egho-notice-status">Not Connected</span>
                    <h2>No work visa applications are stored yet</h2>
                    <p>
                        Work visa processing is not connected to this website. No
                        application, document or fee is recorded for your
                        account, and nothing can be submitted from here.
                    </p>
                    @feature('visa')
                        <div class="egho-actions">
                            <a
                                href="{{ route('work-visa.index') }}"
                                class="egho-btn egho-btn-ghost"
                            >
                                Open work visa processing
                            </a>
                        </div>
                    @endfeature
                </section>
            @endif

        </div>
    </main>

@endsection
