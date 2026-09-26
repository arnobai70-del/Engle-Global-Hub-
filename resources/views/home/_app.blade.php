{{--
    Mobile app band and the journey call to action.

    The panel shows Google Play and App Store badges, and a newsletter sign-up.
    No mobile build is published for this website and it runs no mailing list,
    so the badges are status blocks that say so and the band carries a real call
    to action instead of an email field that would collect an address nobody
    stores.
--}}
<section class="egho-section">
    <div class="egho-shell">
        <div class="egho-app">

            <div>
                <h2>Download Our Mobile App</h2>

                <p>
                    The mobile app is not published yet. The same
                    account, search and booking steps are available in
                    the browser today.
                </p>

                <ul class="egho-app-chips">
                    @feature('flights')
                        <li>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 13.5 21 5l-3.5 8.5L21 19z"/>
                            </svg>
                            Flight Bookings
                        </li>
                    @endfeature

                    @feature('hotels')
                        <li>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 20V9m0 5h16v6M4 9l8-5 8 5"/>
                            </svg>
                            Hotel Deals
                        </li>
                    @endfeature

                    @feature('visa')
                        <li>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M6 3h9l4 4v14H6z"/>
                                <path d="M9 13h6M9 16.5h4"/>
                            </svg>
                            Visa Services
                        </li>
                    @endfeature

                    @feature('bookings')
                        <li>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9 6h10M9 12h10M9 18h10"/>
                                <path d="m4 6 1.4 1.4L8 5"/>
                            </svg>
                            Booking Status
                        </li>
                    @endfeature
                </ul>
            </div>

            <div class="egho-stores">
                <span class="egho-store">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 3.2 14 12 4 20.8zM15.4 10.6l3.3-1.9c1.2.7 1.2 2.9 0 3.6l-3.3-1.9z"/>
                    </svg>
                    <span>
                        <small>Google Play</small>
                        <strong>No published build</strong>
                    </span>
                </span>

                <span class="egho-store">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M16.4 12.7c0-2.3 1.9-3.4 2-3.4-1.1-1.6-2.8-1.8-3.4-1.8-1.5-.1-2.8.8-3.5.8-.7 0-1.8-.8-3-.8-1.6 0-3 .9-3.8 2.3-1.6 2.8-.4 7 1.2 9.3.8 1.1 1.7 2.4 2.9 2.3 1.2 0 1.6-.8 3-.8s1.8.7 3 .7c1.2 0 2-1.1 2.8-2.2.9-1.3 1.3-2.5 1.3-2.6-.1 0-2.5-1-2.5-3.8zM14.2 5.4c.6-.8 1.1-1.9 1-2.9-.9.1-2.1.6-2.7 1.4-.6.7-1.1 1.8-1 2.8 1 .1 2.1-.5 2.7-1.3z"/>
                    </svg>
                    <span>
                        <small>App Store</small>
                        <strong>No published build</strong>
                    </span>
                </span>
            </div>

        </div>
    </div>
</section>

<section
    class="egho-journey"
    style="--egho-journey-image: url('https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=2000&q=85')"
>
    <div class="egho-shell egho-journey-inner">
        <div>
            <h2>Let&rsquo;s Make Your Next Journey Amazing</h2>

            <p>
                Create an account to keep fares, travellers and booking
                status together. Every search runs against the
                configured provider only.
            </p>
        </div>

        @auth
            @feature('dashboard')
                <a href="{{ route('dashboard') }}" class="egho-journey-cta">
                    Go to your dashboard
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 12h13M13 7l5 5-5 5"/>
                    </svg>
                </a>
            @endfeature
        @else
            <a href="{{ route('register') }}" class="egho-journey-cta">
                Create your account
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 12h13M13 7l5 5-5 5"/>
                </svg>
            </a>
        @endauth
    </div>
</section>
