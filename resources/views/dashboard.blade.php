@php
    $isAdminDashboard = auth()->user()->hasAnyRole(['admin', 'super-admin']);
@endphp

@extends($isAdminDashboard ? 'layouts.admin' : 'layouts.site')

@section('title', 'Dashboard')
@section('body_class', 'dashboard-body')
@section('page-class', 'admin-page')

@section('content')

    @if ($isAdminDashboard)
        <x-admin.page-header
            title="Dashboard"
            description="Open the operational areas available to your assigned administration role."
            icon="D"
            eyebrow="Administration overview"
        >
            <a class="egh-button secondary" href="{{ route('home') }}">View website</a>
        </x-admin.page-header>

        <section class="egh-card">
            <div class="admin-card-heading">
                <div>
                    <span class="admin-page-eyebrow">Workspace</span>
                    <h2>Administration areas</h2>
                    <p>Every link below follows the same permission checks as the admin navigation.</p>
                </div>
            </div>

            <div class="admin-dashboard-grid">
                @can('users.view')
                    <a class="admin-dashboard-card" href="{{ route('admin.users.index') }}">
                        <strong>Users</strong>
                        <span>Accounts, verification, and role assignments</span>
                    </a>
                @endcan

                @can('roles.view')
                    <a class="admin-dashboard-card" href="{{ route('admin.roles.index') }}">
                        <strong>Roles &amp; Permissions</strong>
                        <span>Authorization roles and permission coverage</span>
                    </a>
                @endcan

                @role('super-admin')
                    <a class="admin-dashboard-card" href="{{ route('admin.features.index') }}">
                        <strong>Feature Control</strong>
                        <span>Visibility controls within established safety boundaries</span>
                    </a>
                @endrole

                @can('settings.view')
                    <a class="admin-dashboard-card" href="{{ route('admin.settings.manage') }}">
                        <strong>Settings</strong>
                        <span>Application configuration available to your role</span>
                    </a>
                @endcan

                @can('master-data.view')
                    <a class="admin-dashboard-card" href="{{ route('admin.master-data.manage') }}">
                        <strong>Master Data</strong>
                        <span>Countries, cities, categories, currencies, and languages</span>
                    </a>
                @endcan

                <a class="admin-dashboard-card" href="{{ route('admin.bookings.index') }}">
                    <strong>Bookings</strong>
                    <span>Read-only persisted flight order attempts</span>
                </a>

                @can('reports.view')
                    <a class="admin-dashboard-card" href="{{ route('admin.reports.index') }}">
                        <strong>Reports</strong>
                        <span>Read-only operational reporting</span>
                    </a>
                @endcan

                @can('system-logs.view')
                    <a class="admin-dashboard-card" href="{{ route('admin.system-logs.index') }}">
                        <strong>System Logs</strong>
                        <span>Redacted, metadata-only application events</span>
                    </a>
                @endcan
            </div>
        </section>

        <section class="egh-card">
            <div class="admin-card-heading">
                <div>
                    <span class="admin-page-eyebrow">Reporting</span>
                    <h2>Live figures are not connected</h2>
                    <p>
                        Booking counts, active users, revenue and pending work
                        need reporting queries over stored records. Until those
                        queries are enabled this dashboard shows no figures, so
                        nothing on this page is presented as a real total.
                    </p>
                </div>
            </div>

            <p class="egha-chart-note">
                Real totals are read from the read-only pages this dashboard
                links to: bookings, reports and system logs.
            </p>

            <div class="egha-actions">
                <a class="egh-button secondary" href="{{ route('admin.bookings.index') }}">
                    Open bookings
                </a>

                @can('reports.view')
                    <a class="egh-button secondary" href="{{ route('admin.reports.index') }}">
                        Open reports
                    </a>
                @endcan

                @can('system-logs.view')
                    <a class="egh-button secondary" href="{{ route('admin.system-logs.index') }}">
                        Open system logs
                    </a>
                @endcan
            </div>
        </section>

        {{--
            Layout preview only, local development only.

            The KPI cards, the charts and the recent bookings rows are static
            samples used to review the approved admin dashboard layout. They are
            not measured figures and no admin decision should be based on them.

            Wiring point: reporting queries supply the totals and series, and the
            recent bookings table reads the same stored flight order attempts as
            the bookings page.
        --}}
        @if (app()->environment('local'))
            <div class="egha-note">
                <span aria-hidden="true">&#9432;</span>
                <span>
                    <strong>Layout preview.</strong>
                    Sample figures and sample rows that preview the dashboard
                    layout. They are not real totals.
                </span>
            </div>

            <div class="egha-kpis">
                @foreach ([
                    [
                        'label' => 'Bookings',
                        'value' => '1,245',
                        'note' => 'Sample figure',
                        'tone' => 'blue',
                        'icon' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
                    ],
                    [
                        'label' => 'Flights',
                        'value' => '350',
                        'note' => 'Sample figure',
                        'tone' => 'green',
                        'icon' => '<path d="M3 13.5 21 5l-3.5 8.5L21 19z"/><path d="M8.5 12.2 3 13.5"/>',
                    ],
                    [
                        'label' => 'Hotels',
                        'value' => '85',
                        'note' => 'Sample figure',
                        'tone' => 'amber',
                        'icon' => '<path d="M3 20V9m0 6h18v5M3 9l9-5 9 5M9 15v-3h6v3"/>',
                    ],
                    [
                        'label' => 'Searches',
                        'value' => '2,180',
                        'note' => 'Sample figure',
                        'tone' => 'violet',
                        'icon' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/>',
                    ],
                ] as $kpi)
                    <article class="egha-kpi">
                        <span
                            @class([
                                'egha-kpi-icon',
                                'egha-kpi-icon-'.$kpi['tone'],
                            ])
                            aria-hidden="true"
                        >
                            <svg viewBox="0 0 24 24">{!! $kpi['icon'] !!}</svg>
                        </span>

                        <div class="egha-kpi-body">
                            <span>{{ $kpi['label'] }}</span>
                            <strong>{{ $kpi['value'] }}</strong>
                            <small>{{ $kpi['note'] }}</small>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="egha-chart-grid">
                <section class="egha-chart">
                    <h3>Booking Overview</h3>

                    <div class="egha-bars" aria-hidden="true">
                        @foreach ([46, 68, 52, 81, 64, 90, 58, 74] as $height)
                            <span
                                class="egha-bar"
                                style="height: {{ $height }}%"
                            ></span>
                        @endforeach
                    </div>

                    <div class="egha-bar-labels" aria-hidden="true">
                        @foreach (['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'] as $month)
                            <span>{{ $month }}</span>
                        @endforeach
                    </div>

                    <p class="egha-chart-note">
                        Sample series. No real booking volume is measured here.
                    </p>
                </section>

                <section class="egha-chart">
                    <h3>Revenue Overview</h3>

                    <svg
                        viewBox="0 0 320 150"
                        preserveAspectRatio="none"
                        role="img"
                        aria-label="Sample revenue trend line"
                    >
                        <polyline
                            points="0,120 40,96 80,104 120,72 160,80 200,52 240,60 280,32 320,40"
                            fill="none"
                            stroke="#1256a0"
                            stroke-width="3"
                            stroke-linejoin="round"
                            stroke-linecap="round"
                        />
                        <polyline
                            points="0,140 40,132 80,136 120,120 160,124 200,112 240,116 280,104 320,108"
                            fill="none"
                            stroke="#bcd6f3"
                            stroke-width="2"
                            stroke-dasharray="5 5"
                        />
                    </svg>

                    <p class="egha-chart-note">
                        Sample series. Payment totals are not connected yet.
                    </p>
                </section>
            </div>

            <div class="egh-card">
                <div class="admin-card-heading">
                    <div>
                        <span class="admin-page-eyebrow">Preview rows</span>
                        <h2>Recent Bookings</h2>
                        <p>
                            Sample rows that preview the table layout. Real
                            persisted flight order attempts live on the bookings
                            page.
                        </p>
                    </div>
                </div>

                <div class="egha-table-wrap">
                    <table class="egha-table">
                        <thead>
                            <tr>
                                <th scope="col">Booking</th>
                                <th scope="col">Customer</th>
                                <th scope="col">Type</th>
                                <th scope="col">Destination</th>
                                <th scope="col">Dates</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ([
                                ['ref' => 'EGH-SAMPLE-0001', 'type' => 'Flight', 'destination' => 'Sample route', 'dates' => 'Sample dates', 'status' => 'Order Created', 'tone' => 'egha-status-on'],
                                ['ref' => 'EGH-SAMPLE-0002', 'type' => 'Flight', 'destination' => 'Sample route', 'dates' => 'Sample dates', 'status' => 'Order Processing', 'tone' => 'egha-status-off'],
                                ['ref' => 'EGH-SAMPLE-0003', 'type' => 'Hotel', 'destination' => 'Sample city', 'dates' => 'Sample dates', 'status' => 'Not connected', 'tone' => 'egha-status-off'],
                            ] as $row)
                                <tr>
                                    <td>{{ $row['ref'] }}</td>
                                    <td class="egha-muted">Sample customer</td>
                                    <td>{{ $row['type'] }}</td>
                                    <td class="egha-muted">{{ $row['destination'] }}</td>
                                    <td class="egha-muted">{{ $row['dates'] }}</td>
                                    <td>
                                        <span class="egha-status {{ $row['tone'] }}">
                                            {{ $row['status'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <a
                                            class="egha-muted"
                                            href="{{ route('admin.bookings.index') }}"
                                        >
                                            View bookings
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @else

    <main class="dashboard-container">

        <section class="dashboard-welcome">

            <div>
                <span class="dashboard-kicker">
                    WELCOME BACK
                </span>

                <h1>
                    Hello, {{ auth()->user()->name }}
                </h1>

                <p>
                    Search flights, review your account details and continue
                    your travel journey from one secure dashboard.
                </p>

                <div class="dashboard-welcome-actions">

                    @feature('flights')
                        @can('flights.search')
                            <a
                                href="{{ route('flights.index') }}"
                                class="site-button site-button-primary"
                            >
                                Search Flights
                            </a>
                        @endcan
                    @endfeature

                    @feature('account')
                        <a
                            href="{{ route('account.overview') }}"
                            class="site-button site-button-secondary"
                        >
                            View Account
                        </a>
                    @endfeature

                </div>
            </div>

            <div class="dashboard-verified">
                <span aria-hidden="true">&#10003;</span>

                <div>
                    <strong>Email Verified</strong>

                    <small>
                        Your verified account can access protected travel pages.
                    </small>
                </div>
            </div>

        </section>

        <section class="dashboard-section">

            <div class="dashboard-section-heading">
                <div>
                    <span class="dashboard-kicker">
                        QUICK ACCESS
                    </span>

                    <h2>
                        Continue your journey
                    </h2>
                </div>
            </div>

            <div class="dashboard-quick-grid">

                @feature('flights')
                    @can('flights.search')
                        <a
                            href="{{ route('flights.index') }}"
                            class="dashboard-quick-card"
                        >
                        <span class="dashboard-quick-icon" aria-hidden="true">
                            &#9992;
                        </span>

                        <div>
                            <strong>Flight Search</strong>

                            <small>
                                Search available flight options by route,
                                date, cabin and travelers.
                            </small>
                        </div>

                        <b aria-hidden="true">&rarr;</b>
                        </a>
                    @endcan
                @endfeature

                @feature('account')
                    <a
                        href="{{ route('account.overview') }}"
                        class="dashboard-quick-card"
                    >
                    <span class="dashboard-quick-icon" aria-hidden="true">
                        A
                    </span>

                    <div>
                        <strong>Account Overview</strong>

                        <small>
                            Review your name, email, verification and
                            account timeline.
                        </small>
                    </div>

                    <b aria-hidden="true">&rarr;</b>
                    </a>
                @endfeature

                @feature('bookings')
                    @can('flights.book')
                        <a
                            href="{{ route('bookings.index') }}"
                            class="dashboard-quick-card"
                        >
                        <span class="dashboard-quick-icon" aria-hidden="true">
                            B
                        </span>

                        <div>
                            <strong>My Bookings</strong>

                            <small>
                                Review your flight booking attempts, order
                                status and payment status.
                            </small>
                        </div>

                        <b aria-hidden="true">&rarr;</b>
                        </a>
                    @endcan
                @endfeature

            </div>

        </section>

        <section class="dashboard-section">

            <div class="dashboard-section-heading">
                <div>
                    <span class="dashboard-kicker">
                        TRAVEL SERVICES
                    </span>

                    <h2>
                        Services
                    </h2>
                </div>
            </div>

            <div class="dashboard-service-grid">

                @feature('flights')
                    @can('flights.search')
                        <a
                            href="{{ route('flights.index') }}"
                            class="dashboard-service-card dashboard-service-card-link"
                        >
                        <div class="service-icon" aria-hidden="true">
                            &#9992;
                        </div>

                        <h3>Flights</h3>

                        <p>
                            Search domestic and international flight options.
                        </p>

                        <span class="service-status service-status-live">
                            Available
                        </span>
                        </a>
                    @else
                        <article class="dashboard-service-card">
                        <div class="service-icon" aria-hidden="true">
                            &#9992;
                        </div>

                        <h3>Flights</h3>

                        <p>
                            Flight access is not enabled for this account.
                        </p>

                        <span class="service-status">
                            Unavailable
                        </span>
                        </article>
                    @endcan
                @endfeature

                @foreach ([
                    'hotels' => [
                        'icon' => 'H',
                        'description' => 'Search configured hotel availability and stay options.',
                    ],
                    'tours' => [
                        'icon' => 'T',
                        'description' => 'Search configured tours and destination activities.',
                    ],
                    'visa' => [
                        'icon' => 'V',
                        'description' => 'Review configured visa and entry requirement information.',
                    ],
                ] as $serviceKey => $presentation)
                    @feature($serviceKey)
                        @php
                            $service = $travelServices[$serviceKey] ?? null;
                            $hasPermission = $service
                                && (
                                    $service['permission'] === null
                                    || auth()->user()->can($service['permission'])
                                );
                            $canAccess = $service
                                && $service['available']
                                && $service['route_name']
                                && $hasPermission;
                        @endphp

                        @if ($canAccess)
                            <a
                                href="{{ route($service['route_name']) }}"
                                class="dashboard-service-card dashboard-service-card-link"
                            >
                                <div class="service-icon" aria-hidden="true">
                                    {{ $presentation['icon'] }}
                                </div>

                                <h3>{{ $service['label'] }}</h3>

                                <p>{{ $presentation['description'] }}</p>

                                <span class="service-status service-status-live">
                                    {{ $service['status'] }}
                                </span>
                            </a>
                        @else
                            <article class="dashboard-service-card">
                                <div class="service-icon" aria-hidden="true">
                                    {{ $presentation['icon'] }}
                                </div>

                                <h3>{{ $service['label'] ?? ucfirst($serviceKey) }}</h3>

                                <p>
                                    @if ($service && $service['available'])
                                        This service is not enabled for your account.
                                    @else
                                        This service is not configured for customer use.
                                    @endif
                                </p>

                                <span class="service-status">
                                    {{ $service && $service['available']
                                        ? 'Unavailable'
                                        : ($service['status'] ?? 'Not Configured') }}
                                </span>
                            </article>
                        @endif
                    @endfeature
                @endforeach

            </div>

        </section>

        <section class="dashboard-account-card">

            <div class="dashboard-account-heading">
                <div>
                    <span class="dashboard-kicker">
                        YOUR ACCOUNT
                    </span>

                    <h2>
                        Account snapshot
                    </h2>
                </div>

                @feature('account')
                    <a
                        href="{{ route('account.overview') }}"
                        class="dashboard-account-link"
                    >
                        Open Account
                    </a>
                @endfeature
            </div>

            <div class="dashboard-account-grid">

                <div>
                    <span>Full Name</span>
                    <strong>{{ auth()->user()->name }}</strong>
                </div>

                <div>
                    <span>Email Address</span>
                    <strong>{{ auth()->user()->email }}</strong>
                </div>

                <div>
                    <span>Email Status</span>
                    <strong class="verified-text">
                        Verified
                    </strong>
                </div>

                <div>
                    <span>Member Since</span>

                    <strong>
                        {{ auth()->user()->created_at?->format('M Y') ?? 'Not available' }}
                    </strong>
                </div>

            </div>

        </section>

    </main>

    @endif

@endsection
