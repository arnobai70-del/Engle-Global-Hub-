<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Travel') | Eagle Global Hub LTD</title>

    <meta
        name="description"
        content="@yield('meta_description', 'Flight search and travel booking services from Eagle Global Hub LTD.')"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Mockup-aligned OTA theme. Scoped to `.egho-` classes only. --}}
    <link rel="stylesheet" href="{{ asset('css/egh-ota.css') }}">

    @stack('head')
</head>

@php
    /*
     * Public contact slots (top bar + footer).
     *
     * The approved mockup shows a phone number and an email address in the
     * site chrome. No such details are configured for this website yet, and
     * the project rule is that contact details are never fabricated — the
     * /support page states this explicitly and is covered by tests.
     *
     * These slots therefore render ONLY when a value is actually supplied
     * (for example by a future settings-backed view composer sharing
     * `$siteContact`). Until then the chrome stays free of invented
     * phone numbers and email addresses.
     */
    $siteContactPhone = $siteContact['phone'] ?? null;
    $siteContactEmail = $siteContact['email'] ?? null;
    $siteContactAddress = $siteContact['address'] ?? null;
    $siteContactHours = $siteContact['hours'] ?? null;

    $travelServices = $travelServices ?? [];

    /*
     * A travel service is only linked when the registry reports it as
     * available AND the signed-in account holds its permission. This mirrors
     * the existing navigation rule and keeps unconfigured or unauthorized
     * services from producing dead or forbidden links.
     */
    $serviceLinkIsAllowed = static function (array $service): bool {
        if (! ($service['available'] ?? false) || empty($service['route_name'])) {
            return false;
        }

        $permission = $service['permission'] ?? null;

        if ($permission === null) {
            return true;
        }

        return auth()->check() && auth()->user()->can($permission);
    };
@endphp

<body class="site-body @yield('body_class')">

    <a href="#main-content" class="site-skip-link">
        Skip to content
    </a>

    <header class="egho-header-shell">

        <div class="egho-topbar">
            <div class="egho-shell egho-topbar-inner">

                <div class="egho-topbar-group">
                    @if ($siteContactPhone)
                        <span class="egho-topbar-item">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1z"/>
                            </svg>
                            {{ $siteContactPhone }}
                        </span>
                    @endif

                    @if ($siteContactEmail)
                        <a
                            class="egho-topbar-item"
                            href="mailto:{{ $siteContactEmail }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <path d="m3.5 7 8.5 6 8.5-6"/>
                            </svg>
                            {{ $siteContactEmail }}
                        </a>
                    @endif

                    @if (! $siteContactPhone && ! $siteContactEmail)
                        <span class="egho-topbar-item">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 3 4.5 6.6v4.9c0 4.6 3.1 7.6 7.5 8.5 4.4-.9 7.5-3.9 7.5-8.5V6.6z"/>
                                <path d="m9 12 2.2 2.2L15.5 10"/>
                            </svg>
                            Secure travel account &amp; booking steps
                        </span>
                    @endif
                </div>

                <div class="egho-topbar-group">
                    {{--
                        Currency and language selectors from the mockup. Currency
                        switching and locale switching are not implemented yet, so
                        these are presented as non-interactive status chips rather
                        than controls that silently do nothing.
                    --}}
                    <span
                        class="egho-switch"
                        role="status"
                        title="Currency switching is not enabled yet"
                    >
                        BDT
                        <span class="egho-switch-chevron" aria-hidden="true">&#9662;</span>
                    </span>

                    <span
                        class="egho-switch"
                        role="status"
                        title="Language switching is not enabled yet"
                    >
                        {{ strtoupper(app()->getLocale()) }}
                        <span class="egho-switch-chevron" aria-hidden="true">&#9662;</span>
                    </span>

                    @auth
                        @role('super-admin')
                            <a
                                class="egho-topbar-item"
                                href="{{ route('admin.features.index') }}"
                            >
                                Feature Control
                            </a>
                        @endrole
                    @endauth
                </div>
            </div>
        </div>

        <div class="egho-header">
            <div class="egho-shell egho-header-inner">

                <a href="{{ route('home') }}" class="egho-brand">
                    <span class="egho-brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 3.6c.5-1.2-1.3-2.4-2.2-1.4L14.6 7 6.9 4.2a1 1 0 0 0-1.2.4L4 7.2a1 1 0 0 0 .5 1.5l6.2 1.9-2.9 3.3-2.6-.4a1 1 0 0 0-.9.3L3 15.4a1 1 0 0 0 .6 1.6l4.2 1.1 1.1 4.2a1 1 0 0 0 1.6.6l1.6-1.3a1 1 0 0 0 .3-.9l-.4-2.6 3.3-2.9 1.9 6.2a1 1 0 0 0 1.5.5l2.6-1.7a1 1 0 0 0 .4-1.2L18.9 13l4.8-4.2c.1-.1.2-.2.3-.3z"/>
                        </svg>
                    </span>

                    <span class="egho-brand-copy">
                        <strong>Eagle Global Hub</strong>
                        <small>Travel &amp; Visa Services</small>
                    </span>
                </a>

                <nav class="egho-nav" aria-label="Primary navigation">

                    <a
                        href="{{ route('home') }}"
                        @class(['is-active' => request()->routeIs('home')])
                    >
                        Home
                    </a>

                    @feature('flights')
                        @can('flights.search')
                            <a
                                href="{{ route('flights.index') }}"
                                @class(['is-active' => request()->routeIs('flights.*')])
                            >
                                Flights
                            </a>
                        @else
                            @feature('dashboard')
                                <a href="{{ route('dashboard') }}">
                                    Flights
                                </a>
                            @endfeature
                        @endcan
                    @endfeature

                    @foreach ($travelServices as $serviceKey => $service)
                        @feature($serviceKey)
                            @if (
                                $serviceKey !== 'flights' &&
                                $serviceLinkIsAllowed($service)
                            )
                                <a
                                    href="{{ route($service['route_name']) }}"
                                    @class(['is-active' => request()->routeIs($serviceKey.'.*')])
                                >
                                    {{ $service['label'] }}
                                </a>
                            @endif
                        @endfeature
                    @endforeach

                    @feature('visa')
                        <a
                            href="{{ route('work-visa.index') }}"
                            @class(['is-active' => request()->routeIs('work-visa.*')])
                        >
                            Work Visa
                        </a>
                    @endfeature

                    @feature('bookings')
                        @can('flights.book')
                            <a
                                href="{{ route('bookings.index') }}"
                                @class(['is-active' => request()->routeIs('bookings.*')])
                            >
                                My Bookings
                            </a>
                        @else
                            @feature('dashboard')
                                <a href="{{ route('dashboard') }}">
                                    My Bookings
                                </a>
                            @endfeature
                        @endcan
                    @endfeature

                </nav>

                <div class="egho-header-actions">

                    @auth
                        @feature('account')
                            <a
                                href="{{ route('account.overview') }}"
                                class="egho-user-chip"
                                title="Open your account"
                            >
                                <span class="egho-user-avatar" aria-hidden="true">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>

                                <span class="egho-user-copy">
                                    <strong>{{ auth()->user()->name }}</strong>
                                    <small>{{ auth()->user()->email }}</small>
                                </span>
                            </a>
                        @endfeature

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="site-button site-button-ghost"
                            >
                                Logout
                            </button>
                        </form>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="site-button site-button-ghost"
                        >
                            Sign In
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="site-button site-button-primary"
                        >
                            Create Account
                        </a>
                    @endauth

                </div>
            </div>
        </div>

    </header>

    @if (
        app()->environment(['local', 'testing']) &&
        ! request()->routeIs('home')
    )
        <div
            class="site-environment-banner"
            role="status"
        >
            <strong>Development / sandbox environment:</strong>
            flight availability may use fixture or supplier sandbox data and
            must not be treated as live bookable airline inventory.
        </div>
    @endif

    @if (request()->attributes->get('super_admin_feature_preview') === true)
        <div class="site-environment-banner" role="status">
            <strong>Super Admin Preview:</strong>
            this feature is currently disabled or hidden for other users.
        </div>
    @endif

    <div id="main-content">
        @yield('content')
    </div>

    <footer class="egho-footer">
        <div class="egho-shell">

            <div class="egho-footer-main">

                <div>
                    <a href="{{ route('home') }}" class="egho-footer-brand">
                        <span aria-hidden="true">&#9992;</span>
                        Eagle Global Hub LTD
                    </a>

                    <p>
                        A clear and secure travel journey — search, review,
                        booking status and confirmation in one account.
                    </p>
                </div>

                <div>
                    <h3>Quick Links</h3>

                    <div class="egho-footer-list">
                        <a href="{{ route('home') }}">Home</a>

                        @feature('about')
                            <a href="{{ route('about') }}">About</a>
                        @endfeature

                        @feature('support')
                            <a href="{{ route('support') }}">Support</a>
                        @endfeature

                        <a href="{{ route('terms') }}">Terms</a>

                        @auth
                            @feature('account')
                                <a href="{{ route('account.overview') }}">My Account</a>
                            @endfeature

                            @feature('dashboard')
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            @endfeature
                        @else
                            <a href="{{ route('login') }}">Login</a>
                            <a href="{{ route('register') }}">Register</a>
                        @endauth
                    </div>
                </div>

                <div>
                    <h3>Services</h3>

                    <div class="egho-footer-list">
                        @feature('flights')
                            @can('flights.search')
                                <a href="{{ route('flights.index') }}">Flights</a>
                            @endcan
                        @endfeature

                        @foreach ($travelServices as $serviceKey => $service)
                            @feature($serviceKey)
                                @if (
                                    $serviceKey !== 'flights' &&
                                    $serviceLinkIsAllowed($service)
                                )
                                    <a href="{{ route($service['route_name']) }}">
                                        {{ $service['label'] }}
                                    </a>
                                @endif
                            @endfeature
                        @endforeach

                        @feature('visa')
                            <a href="{{ route('work-visa.index') }}">
                                Work Visa
                            </a>
                        @endfeature

                        @feature('bookings')
                            @can('flights.book')
                                <a href="{{ route('bookings.index') }}">My Bookings</a>
                            @endcan
                        @endfeature
                    </div>
                </div>

                <div>
                    <h3>Contact</h3>

                    <ul class="egho-footer-contact">
                        @if ($siteContactAddress)
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/>
                                    <circle cx="12" cy="10" r="2.6"/>
                                </svg>
                                <span>{{ $siteContactAddress }}</span>
                            </li>
                        @endif

                        @if ($siteContactPhone)
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1z"/>
                                </svg>
                                <span>{{ $siteContactPhone }}</span>
                            </li>
                        @endif

                        @if ($siteContactEmail)
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                                    <path d="m3.5 7 8.5 6 8.5-6"/>
                                </svg>
                                <a href="mailto:{{ $siteContactEmail }}">{{ $siteContactEmail }}</a>
                            </li>
                        @endif

                        @if ($siteContactHours)
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="8.5"/>
                                    <path d="M12 7.5V12l3 2"/>
                                </svg>
                                <span>{{ $siteContactHours }}</span>
                            </li>
                        @endif

                        @if (
                            ! $siteContactAddress &&
                            ! $siteContactPhone &&
                            ! $siteContactEmail &&
                            ! $siteContactHours
                        )
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="8.5"/>
                                    <path d="M12 8.5v.01M12 11.5v4"/>
                                </svg>
                                <span>
                                    Official contact channels are not configured
                                    for this website yet. Booking and status
                                    questions are handled inside your account.
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>

            </div>

            <div class="egho-footer-bottom">
                <small>
                    &copy; {{ now()->year }} Eagle Global Hub LTD.
                    Live supplier inventory and execution require verified
                    production configuration.
                </small>

                <div class="egho-topbar-group">
                    <a href="{{ route('terms') }}" class="egho-topbar-item">
                        Terms
                    </a>

                    @feature('support')
                        <a href="{{ route('support') }}" class="egho-topbar-item">
                            Support
                        </a>
                    @endfeature
                </div>
            </div>

        </div>
    </footer>

    @stack('scripts')

</body>
</html>
