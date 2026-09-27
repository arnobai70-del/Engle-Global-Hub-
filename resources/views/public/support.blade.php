@extends('layouts.site')

@section('title', 'Contact & Support')
@section(
    'meta_description',
    'Contact Eagle Global Hub LTD in Shahjadpur, Gulshan, Dhaka for travel account, booking and service support.'
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
                        Contact our Dhaka office for travel account, booking
                        status and service enquiries using the official phone,
                        email and office address below.
                    </p>
                </div>

                <div class="egho-contact-hero-media">
                    <img
                        src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1400&q=85"
                        srcset="
                            https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1000&q=85 1x,
                            https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1800&q=85 2x
                        "
                        alt="Eagle Global Hub customer support"
                        loading="lazy"
                        width="1400"
                        height="933"
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
                            <a href="mailto:{{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}">
                                {{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}
                            </a>
                        </div>
                    </div>

                    <div class="egho-contact-row">
                        <span class="egho-contact-icon" aria-hidden="true">
                            &#9742;
                        </span>
                        <div>
                            <strong>Mobile</strong>
                            <a href="tel:{{ preg_replace('/[^+0-9]/', '', data_get($siteContact, 'phone', '01953626481')) }}">
                                {{ data_get($siteContact, 'phone', '01953626481') }}
                            </a>
                        </div>
                    </div>

                    <div class="egho-contact-row">
                        <span class="egho-contact-icon" aria-hidden="true">
                            &#127968;
                        </span>
                        <div>
                            <strong>Office address</strong>
                            <span>{{ data_get($siteContact, 'address', 'Mysha Chowdhury Tower, Ga-30/B, Pragati Sharani, Shahjadpur, Gulshan, Dhaka-1212') }}</span>
                        </div>
                    </div>

                    @if(data_get($siteContact, 'hours'))
                    <div class="egho-contact-row">
                        <span class="egho-contact-icon" aria-hidden="true">
                            &#128337;
                        </span>
                        <div>
                            <strong>Support hours</strong>
                            <span>{{ data_get($siteContact, 'hours') }}</span>
                        </div>
                    </div>
                    @endif

                    <div class="egho-map-panel" role="note" aria-label="Eagle Global Hub office address">
                        <span class="egho-map-canvas" aria-hidden="true"></span>

                        <div class="egho-map-panel-inner egho-map-panel-copy">
                            <span class="egho-map-pin" aria-hidden="true">
                                &#128205;
                            </span>
                            <span>
                                <strong>Dhaka office</strong><br>
                                Mysha Chowdhury Tower<br>
                                Ga-30/B, Pragati Sharani, Shahjadpur,<br>
                                Gulshan, Dhaka-1212
                            </span>
                        </div>
                    </div>
                </section>

                <section class="egho-contact-card">
                    <h2>Send us a message</h2>

                    <div class="egho-sample-note">
                        <span aria-hidden="true">&#9432;</span>
                        <span>
                            <strong>The website message form is not connected yet.</strong>
                            Please contact us directly at
                            <a href="mailto:{{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}">
                                {{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}
                            </a>
                            or call
                            <a href="tel:{{ preg_replace('/[^+0-9]/', '', data_get($siteContact, 'phone', '01953626481')) }}">
                                {{ data_get($siteContact, 'phone', '01953626481') }}
                            </a>.
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
                        <a
                            href="mailto:{{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}"
                            class="egho-btn egho-btn-primary"
                        >
                            Email support
                        </a>
                        <small>
                            For booking status, signed-in customers can also use
                            their account and My Bookings pages.
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
                        Mobile: <a href="tel:{{ preg_replace('/[^+0-9]/', '', data_get($siteContact, 'phone', '01953626481')) }}">{{ data_get($siteContact, 'phone', '01953626481') }}</a><br>
                        Email: <a href="mailto:{{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}">{{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}</a><br>
                        {{ data_get($siteContact, 'address', 'Mysha Chowdhury Tower, Ga-30/B, Pragati Sharani, Shahjadpur, Gulshan, Dhaka-1212') }}
                    </p>
                </article>
            </div>

        </div>
    </main>

@endsection
