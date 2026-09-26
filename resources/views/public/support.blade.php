@extends('layouts.site')

@section('title', 'Contact & Support')
@section(
    'meta_description',
    'Customer support information for Eagle Global Hub LTD travel account and booking workflows.'
)
@section('body_class', 'public-page-body egho-page-body')

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            <section class="egho-contact-hero">
                <div>
                    <span class="egho-eyebrow">CONTACT &amp; SUPPORT</span>
                    <h1>Get in touch with Eagle Global Hub LTD</h1>
                    <p>
                        Support for your travel account, booking status and
                        protected customer pages. Official contact channels are
                        published here once they are configured.
                    </p>
                </div>

                <div class="egho-contact-hero-media">
                    <img
                        src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80"
                        alt=""
                        loading="lazy"
                        width="1200"
                        height="800"
                    >
                </div>
            </section>

            <div class="egho-contact-grid">

                <section class="egho-contact-card">
                    <h2>Contact information</h2>

                    <div class="egho-contact-row">
                        <span class="egho-contact-icon" aria-hidden="true">
                            &#9993;
                        </span>
                        <div>
                            <strong>Support email</strong>
                            <span>Not configured in this website yet</span>
                        </div>
                    </div>

                    <div class="egho-contact-row">
                        <span class="egho-contact-icon" aria-hidden="true">
                            &#9742;
                        </span>
                        <div>
                            <strong>Support phone</strong>
                            <span>Not configured in this website yet</span>
                        </div>
                    </div>

                    <div class="egho-contact-row">
                        <span class="egho-contact-icon" aria-hidden="true">
                            &#127968;
                        </span>
                        <div>
                            <strong>Office address</strong>
                            <span>Not configured in this website yet</span>
                        </div>
                    </div>

                    <div class="egho-contact-row">
                        <span class="egho-contact-icon" aria-hidden="true">
                            &#128337;
                        </span>
                        <div>
                            <strong>Support hours</strong>
                            <span>Not configured in this website yet</span>
                        </div>
                    </div>

                    <div class="egho-map-panel" role="img"
                         aria-label="Office location is not configured in this website yet">
                        {{--
                            Drawn city-grid canvas, not a map service. It is
                            decoration behind the notice below; the panel still
                            states that no office location is configured and no
                            coordinates are implied.
                        --}}
                        <span class="egho-map-canvas" aria-hidden="true"></span>

                        <div class="egho-map-panel-inner egho-map-panel-copy">
                            <span class="egho-map-pin" aria-hidden="true">
                                &#128205;
                            </span>
                            No office location is configured in this website
                            yet, so no map is published.
                        </div>
                    </div>
                </section>

                <section class="egho-contact-card">
                    <h2>Send us a message</h2>

                    <div class="egho-sample-note">
                        <span aria-hidden="true">&#9432;</span>
                        <span>
                            <strong>Message delivery is not enabled yet.</strong>
                            No support inbox is connected to this website, so
                            this form cannot send anything. It is shown so the
                            layout is ready for a configured support channel.
                        </span>
                    </div>

                    <div class="egho-form-grid">
                        <label class="egho-field">
                            <span>Full name</span>
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

                        <label class="egho-field">
                            <span>Subject</span>
                            <select disabled>
                                <option>Select a topic</option>
                            </select>
                        </label>

                        <label class="egho-field egho-field-wide">
                            <span>Message</span>
                            <textarea class="egho-textarea" disabled></textarea>
                        </label>
                    </div>

                    <div class="egho-form-foot">
                        <button
                            type="button"
                            class="egho-btn egho-btn-primary"
                            disabled
                        >
                            Send message
                        </button>
                        <small>
                            Use your account pages for booking status while
                            official support channels are being configured.
                        </small>
                    </div>
                </section>

            </div>

            <div class="egho-contact-grid" style="margin-top:20px">
                <article class="egho-contact-card">
                    <h2>Booking Status</h2>
                    <p class="egho-result-summary">
                        Signed-in customers can open My Bookings to review
                        stored order and payment status for their own flight
                        booking attempts.
                    </p>
                </article>

                <article class="egho-contact-card">
                    <h2>Account Access</h2>
                    <p class="egho-result-summary">
                        If you cannot access protected pages, confirm that your
                        email address is verified and that you are signed in
                        with the account used for the booking.
                    </p>
                </article>

                <article class="egho-contact-card">
                    <h2>Official Contact Channels</h2>
                    <p class="egho-result-summary">
                        No public support email, phone number or office address
                        is configured in this website yet.
                    </p>
                </article>
            </div>

        </div>
    </main>

@endsection
