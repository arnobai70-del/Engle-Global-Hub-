@extends('layouts.site')

@section('title', 'Booking Details')
@section('body_class', 'bookings-body')

@section('content')

    @php
        $payment = $booking->paymentAttempt;

        $orderStatus = match ($booking->status) {
            \App\Models\FlightOrderAttempt::STATUS_CREATED => 'Order Created',
            \App\Models\FlightOrderAttempt::STATUS_FAILED => 'Order Failed',
            default => 'Order Processing',
        };

        $paymentStatus = match ($payment?->status) {
            \App\Models\FlightOrderPaymentAttempt::STATUS_SUCCEEDED => 'Payment Succeeded',
            \App\Models\FlightOrderPaymentAttempt::STATUS_FAILED => 'Payment Failed',
            \App\Models\FlightOrderPaymentAttempt::STATUS_PROCESSING => 'Payment Processing',
            default => 'Payment Not Started',
        };
    @endphp

    <main class="egho-page">

        <div class="egho-shell">

            <section class="egho-record-hero">
                <div>
                    <span class="egho-eyebrow">
                        BOOKING DETAILS
                    </span>

                    <h1>
                        Booking #{{ $booking->id }}
                    </h1>

                    <p>
                        Customer booking confirmation and payment summary based on
                        records stored for your Eagle Global Hub LTD account.
                    </p>
                </div>

                <div class="egho-record-actions egho-print-hide">
                    <a
                        href="{{ route('bookings.index') }}"
                        class="egho-btn egho-btn-ghost"
                    >
                        Back to Bookings
                    </a>

                    <a
                        href="{{ route('bookings.invoice', $booking) }}"
                        class="egho-btn egho-btn-ghost"
                    >
                        Invoice / Payment Record
                    </a>

                    <button
                        type="button"
                        class="egho-btn egho-btn-primary"
                        onclick="window.print()"
                    >
                        Print
                    </button>
                </div>
            </section>

            <section class="egho-record-grid">

                <article class="egho-record-card">
                    <div class="egho-panel-head">
                        <span class="egho-eyebrow">
                            STATUS
                        </span>

                        <h2>
                            Booking status
                        </h2>
                    </div>

                    <dl class="egho-data-list">
                        <div>
                            <dt>Order Status</dt>
                            <dd>
                                <span
                                    @class([
                                        'egho-status',
                                        'egho-status-ok' => $booking->status === \App\Models\FlightOrderAttempt::STATUS_CREATED,
                                        'egho-status-bad' => $booking->status === \App\Models\FlightOrderAttempt::STATUS_FAILED,
                                        'egho-status-info' => ! in_array($booking->status, [
                                            \App\Models\FlightOrderAttempt::STATUS_CREATED,
                                            \App\Models\FlightOrderAttempt::STATUS_FAILED,
                                        ], true),
                                    ])
                                >
                                    {{ $orderStatus }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt>Payment Status</dt>
                            <dd>
                                <span
                                    @class([
                                        'egho-status',
                                        'egho-status-ok' => $payment?->status === \App\Models\FlightOrderPaymentAttempt::STATUS_SUCCEEDED,
                                        'egho-status-bad' => $payment?->status === \App\Models\FlightOrderPaymentAttempt::STATUS_FAILED,
                                        'egho-status-info' => $payment?->status === \App\Models\FlightOrderPaymentAttempt::STATUS_PROCESSING,
                                        'egho-status-muted' => $payment === null,
                                    ])
                                >
                                    {{ $paymentStatus }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt>Booking Created</dt>
                            <dd>
                                {{ $booking->created_at?->format('M j, Y g:i A') ?? 'Not available' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Order Resolved</dt>
                            <dd>
                                {{ $booking->resolved_at?->format('M j, Y g:i A') ?? 'Not available' }}
                            </dd>
                        </div>
                    </dl>
                </article>

                <article class="egho-record-card">
                    <div class="egho-panel-head">
                        <span class="egho-eyebrow">
                            PAYMENT
                        </span>

                        <h2>
                            Receipt summary
                        </h2>
                    </div>

                    @if ($payment)
                        <dl class="egho-data-list">
                            <div>
                                <dt>Amount</dt>
                                <dd>{{ $payment->currency }} {{ $payment->amount }}</dd>
                            </div>

                            <div>
                                <dt>Payment Status</dt>
                                <dd>{{ $paymentStatus }}</dd>
                            </div>

                            <div>
                                <dt>Payment Created</dt>
                                <dd>
                                    {{ $payment->created_at?->format('M j, Y g:i A') ?? 'Not available' }}
                                </dd>
                            </div>

                            <div>
                                <dt>Payment Resolved</dt>
                                <dd>
                                    {{ $payment->resolved_at?->format('M j, Y g:i A') ?? 'Not available' }}
                                </dd>
                            </div>
                        </dl>
                    @else
                        <div class="egho-empty">
                            No payment attempt has been stored for this booking.
                        </div>
                    @endif
                </article>

            </section>

            <section class="egho-document">
                <div class="egho-document-head">
                    <div>
                        <span class="egho-eyebrow">
                            CUSTOMER DOCUMENT
                        </span>

                        <h2>
                            Booking confirmation
                        </h2>
                    </div>

                    <strong>
                        Eagle Global Hub LTD
                    </strong>
                </div>

                <dl class="egho-document-grid">
                    <div>
                        <dt>Customer</dt>
                        <dd>{{ auth()->user()->name }}</dd>
                    </div>

                    <div>
                        <dt>Email</dt>
                        <dd>{{ auth()->user()->email }}</dd>
                    </div>

                    <div>
                        <dt>Internal Booking</dt>
                        <dd>#{{ $booking->id }}</dd>
                    </div>

                    <div>
                        <dt>Order Status</dt>
                        <dd>{{ $orderStatus }}</dd>
                    </div>

                    <div>
                        <dt>Payment Status</dt>
                        <dd>{{ $paymentStatus }}</dd>
                    </div>

                    <div>
                        <dt>Total Paid</dt>
                        <dd>
                            @if ($payment)
                                {{ $payment->currency }} {{ $payment->amount }}
                            @else
                                Not available
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="egho-note-grid">
                    <article>
                        <h3>Itinerary</h3>

                        <p>
                            Detailed route, schedule and carrier itinerary are not
                            stored in this booking record.
                        </p>
                    </article>

                    <article>
                        <h3>Traveler Information</h3>

                        <p>
                            Traveler details are validated during booking and are
                            not displayed from this stored summary.
                        </p>
                    </article>

                    <article>
                        <h3>Airline Ticket</h3>

                        <p>
                            This is a customer booking confirmation, not an
                            airline-issued e-ticket or ticket document.
                        </p>
                    </article>
                </div>
            </section>

        </div>

    </main>

@endsection
