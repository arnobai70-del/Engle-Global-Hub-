@php
    $dashboardCssModifiedAt = @filemtime(public_path('css/egh-admin-dashboard-v2.css'));
    $dashboardCssHref = asset('css/egh-admin-dashboard-v2.css').($dashboardCssModifiedAt ? '?v='.$dashboardCssModifiedAt : '');
    $currentAdmin = auth()->user();
    $currentRole = $currentAdmin?->getRoleNames()->first();
    $currentRoleLabel = $currentRole ? ucwords(str_replace('-', ' ', $currentRole)) : 'Admin';
@endphp

<link rel="stylesheet" href="{{ $dashboardCssHref }}">

<div class="eghad-dashboard">
    <section class="eghad-hero" aria-labelledby="eghad-dashboard-title">
        <div class="eghad-hero-copy">
            <span class="eghad-kicker">Eagle Global Hub Control Center</span>
            <h1 id="eghad-dashboard-title">Welcome back, {{ $currentAdmin?->name ?? 'Admin' }}</h1>
            <p>
                Manage website content, travel operations, access control and business data
                from one permission-aware workspace.
            </p>

            <div class="eghad-hero-meta">
                <span class="eghad-role-pill">{{ $currentRoleLabel }}</span>
                <span class="eghad-security-pill">
                    <span aria-hidden="true">✓</span>
                    Permission protected
                </span>
            </div>
        </div>

        <div class="eghad-hero-actions">
            @can('settings.view')
                <a class="eghad-button eghad-button-primary" href="{{ route('admin.homepage.index') }}">
                    <span>Homepage Content</span>
                    <span aria-hidden="true">↗</span>
                </a>
            @endcan

            <a class="eghad-button eghad-button-light" href="{{ route('home') }}">
                <span>View Website</span>
                <span aria-hidden="true">↗</span>
            </a>
        </div>
    </section>

    <section class="eghad-section" aria-labelledby="eghad-priority-heading">
        <div class="eghad-section-heading">
            <div>
                <span class="eghad-kicker">Priority tools</span>
                <h2 id="eghad-priority-heading">Website &amp; system controls</h2>
                <p>The controls used most often, kept together for faster administration.</p>
            </div>
        </div>

        <div class="eghad-grid eghad-grid-featured">
            @can('settings.view')
                <a class="eghad-card eghad-card-featured" href="{{ route('admin.homepage.index') }}">
                    <span class="eghad-card-icon eghad-icon-amber" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 5h18v14H3z"/><path d="M3 9h18M8 9v10"/></svg>
                    </span>
                    <span class="eghad-card-copy">
                        <strong>Homepage Content</strong>
                        <small>Hero, news, destinations, sections, images and homepage SEO content.</small>
                    </span>
                    <span class="eghad-card-arrow" aria-hidden="true">→</span>
                </a>

                <a class="eghad-card eghad-card-featured" href="{{ route('admin.settings.manage') }}">
                    <span class="eghad-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l-2.8 2.8a1.7 1.7 0 0 0-1.9-.3A1.7 1.7 0 0 0 14 21h-4a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-2.8-2.8a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14v-4a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l2.8-2.8a1.7 1.7 0 0 0 1.9.3A1.7 1.7 0 0 0 10 3h4a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l2.8 2.8a1.7 1.7 0 0 0-.3 1.9A1.7 1.7 0 0 0 21 10v4a1.7 1.7 0 0 0-1.6 1z"/></svg>
                    </span>
                    <span class="eghad-card-copy">
                        <strong>Settings</strong>
                        <small>Brand, contact, SEO, application and provider configuration available to your role.</small>
                    </span>
                    <span class="eghad-card-arrow" aria-hidden="true">→</span>
                </a>
            @endcan

            @role('super-admin')
                <a class="eghad-card eghad-card-featured" href="{{ route('admin.features.index') }}">
                    <span class="eghad-card-icon eghad-icon-green" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/><circle cx="9" cy="7" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="8" cy="17" r="2"/></svg>
                    </span>
                    <span class="eghad-card-copy">
                        <strong>Feature Control</strong>
                        <small>Manage public feature visibility while preserving existing safety boundaries.</small>
                    </span>
                    <span class="eghad-card-arrow" aria-hidden="true">→</span>
                </a>
            @endrole

            @can('master-data.view')
                <a class="eghad-card eghad-card-featured" href="{{ route('admin.master-data.manage') }}">
                    <span class="eghad-card-icon eghad-icon-cyan" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.8 2.7 4 5.7 4 9s-1.2 6.3-4 9c-2.8-2.7-4-5.7-4-9s1.2-6.3 4-9z"/></svg>
                    </span>
                    <span class="eghad-card-copy">
                        <strong>Master Data</strong>
                        <small>Countries, cities and the structured data used throughout the platform.</small>
                    </span>
                    <span class="eghad-card-arrow" aria-hidden="true">→</span>
                </a>
            @endcan
        </div>
    </section>

    @can('master-data.view')
        <section class="eghad-section" aria-labelledby="eghad-data-heading">
            <div class="eghad-section-heading eghad-section-heading-row">
                <div>
                    <span class="eghad-kicker">Content &amp; reference data</span>
                    <h2 id="eghad-data-heading">Manage structured website data</h2>
                </div>
            </div>

            <div class="eghad-grid eghad-grid-compact">
                <a class="eghad-mini-card" href="{{ route('admin.categories.manage') }}"><span>Categories</span><b aria-hidden="true">→</b></a>
                <a class="eghad-mini-card" href="{{ route('admin.currencies.manage') }}"><span>Currencies</span><b aria-hidden="true">→</b></a>
                <a class="eghad-mini-card" href="{{ route('admin.master-data.manage') }}"><span>Countries &amp; Cities</span><b aria-hidden="true">→</b></a>
                <a class="eghad-mini-card" href="{{ route('admin.languages.manage') }}"><span>Languages</span><b aria-hidden="true">→</b></a>
                <a class="eghad-mini-card" href="{{ route('admin.destinations.index') }}"><span>Destinations</span><b aria-hidden="true">→</b></a>
            </div>
        </section>
    @endcan

    <section class="eghad-section" aria-labelledby="eghad-operations-heading">
        <div class="eghad-section-heading">
            <div>
                <span class="eghad-kicker">Operations</span>
                <h2 id="eghad-operations-heading">Administration workspace</h2>
                <p>Only tools allowed by the signed-in administrator's permissions are shown.</p>
            </div>
        </div>

        <div class="eghad-grid eghad-grid-standard">
            <a class="eghad-card" href="{{ route('admin.bookings.index') }}">
                <span class="eghad-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg></span>
                <span class="eghad-card-copy"><strong>Bookings</strong><small>Persisted flight order attempts and operational booking records.</small></span>
                <span class="eghad-card-arrow" aria-hidden="true">→</span>
            </a>

            @can('users.view')
                <a class="eghad-card" href="{{ route('admin.users.index') }}">
                    <span class="eghad-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M2.5 20c.8-4 3-6 6.5-6s5.7 2 6.5 6M16 7a3 3 0 1 1 0 6M17 14c2.6.4 4.2 2.2 4.7 5"/></svg></span>
                    <span class="eghad-card-copy"><strong>Users</strong><small>Accounts, verification states and role assignments.</small></span>
                    <span class="eghad-card-arrow" aria-hidden="true">→</span>
                </a>
            @endcan

            @can('roles.view')
                <a class="eghad-card" href="{{ route('admin.roles.index') }}">
                    <span class="eghad-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 4 7v5c0 5 3.4 8 8 9 4.6-1 8-4 8-9V7z"/><path d="m8.5 12 2.2 2.2 4.8-5"/></svg></span>
                    <span class="eghad-card-copy"><strong>Roles &amp; Permissions</strong><small>Authorization roles and permission coverage.</small></span>
                    <span class="eghad-card-arrow" aria-hidden="true">→</span>
                </a>
            @endcan

            @can('reports.view')
                <a class="eghad-card" href="{{ route('admin.reports.index') }}">
                    <span class="eghad-card-icon eghad-icon-green" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg></span>
                    <span class="eghad-card-copy"><strong>Reports</strong><small>Read-only operational reporting and review tools.</small></span>
                    <span class="eghad-card-arrow" aria-hidden="true">→</span>
                </a>
            @endcan

            @can('system-logs.view')
                <a class="eghad-card" href="{{ route('admin.system-logs.index') }}">
                    <span class="eghad-card-icon eghad-icon-slate" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span>
                    <span class="eghad-card-copy"><strong>System Logs</strong><small>Redacted metadata-only application events.</small></span>
                    <span class="eghad-card-arrow" aria-hidden="true">→</span>
                </a>
            @endcan
        </div>
    </section>

    @if ($currentAdmin?->canAny(['agents.view', 'affiliates.view', 'students.view', 'institutions.view']))
        <section class="eghad-section" aria-labelledby="eghad-network-heading">
            <div class="eghad-section-heading">
                <div>
                    <span class="eghad-kicker">Business network</span>
                    <h2 id="eghad-network-heading">People &amp; partner management</h2>
                </div>
            </div>

            <div class="eghad-grid eghad-grid-compact">
                @can('agents.view')
                    <a class="eghad-mini-card" href="{{ route('admin.agents.index') }}"><span>Agents</span><b aria-hidden="true">→</b></a>
                @endcan
                @can('affiliates.view')
                    <a class="eghad-mini-card" href="{{ route('admin.affiliates.index') }}"><span>Affiliates</span><b aria-hidden="true">→</b></a>
                @endcan
                @can('students.view')
                    <a class="eghad-mini-card" href="{{ route('admin.students.index') }}"><span>Students</span><b aria-hidden="true">→</b></a>
                @endcan
                @can('institutions.view')
                    <a class="eghad-mini-card" href="{{ route('admin.institutions.index') }}"><span>Institutions</span><b aria-hidden="true">→</b></a>
                @endcan
            </div>
        </section>
    @endif

    <section class="eghad-status-panel" aria-labelledby="eghad-reporting-heading">
        <div class="eghad-status-icon" aria-hidden="true">i</div>
        <div class="eghad-status-copy">
            <span class="eghad-kicker">Reporting status</span>
            <h2 id="eghad-reporting-heading">Live dashboard totals are intentionally not shown yet</h2>
            <p>
                This dashboard does not invent booking, revenue or activity figures. Use the
                connected read-only operational pages for real stored records until dedicated
                reporting queries are enabled.
            </p>
        </div>
        <div class="eghad-status-actions">
            <a class="eghad-button eghad-button-light" href="{{ route('admin.bookings.index') }}">Bookings</a>
            @can('reports.view')
                <a class="eghad-button eghad-button-light" href="{{ route('admin.reports.index') }}">Reports</a>
            @endcan
            @can('system-logs.view')
                <a class="eghad-button eghad-button-light" href="{{ route('admin.system-logs.index') }}">Logs</a>
            @endcan
        </div>
    </section>
</div>
