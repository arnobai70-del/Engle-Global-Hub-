<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') | Eagle Global Hub LTD</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Instrument Sans, built from the Vite font manifest. --}}
    {{ Vite::fonts() }}

    @php
        /*
         * Cache-busted like the site theme: this file lives in public/ rather
         * than the Vite manifest, so the modification time stands in for the
         * content hash a build would otherwise provide.
         */
        $themeCss = static function (string $file): string {
            $modifiedAt = @filemtime(public_path($file));

            return asset($file).($modifiedAt ? '?v='.$modifiedAt : '');
        };
    @endphp

    {{-- Additive mockup styling for the admin shell; scoped to `.egha-`. --}}
    <link rel="stylesheet" href="{{ $themeCss('css/egh-admin.css') }}">

    <style>
        /*
        |--------------------------------------------------------------------------
        | Compatibility utilities for existing Admin pages
        |--------------------------------------------------------------------------
        */

        .egh-content {
            min-width: 0;
            width: 100%;
            box-sizing: border-box;
            padding: 28px;
        }

        .egh-content > * {
            min-width: 0;
            max-width: 100%;
            box-sizing: border-box;
        }

        .egh-card {
            min-width: 0;
            max-width: 100%;
            box-sizing: border-box;
            overflow-x: auto;
            padding: 20px;
            border: 1px solid #e1e7f0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 6px 24px rgba(21,45,82,.04);
        }

        .egh-button {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            padding: 9px 14px;
            border: 0;
            border-radius: 9px;
            background: #315ff4;
            color: #ffffff;
            cursor: pointer;
            font: inherit;
            font-size: 13px;
            font-weight: 750;
            text-decoration: none;
        }

        .egh-button.secondary {
            border: 1px solid #dbe3ef;
            background: #eef2f7;
            color: #273852;
        }

        .egh-alert {
            margin: 0 28px 18px;
            padding: 12px 14px;
            border: 1px solid #ccebd8;
            border-radius: 10px;
            background: #eefaf2;
            color: #22643b;
        }

        .egh-content input:not([type="checkbox"]):not([type="radio"]),
        .egh-content select,
        .egh-content textarea {
            max-width: 100%;
            box-sizing: border-box;
        }

        .egh-content form.egh-card > p > label {
            display: block;
            max-width: 640px;
            color: #263d58;
            font-weight: 700;
        }

        .egh-content form.egh-card > p input:not([type="checkbox"]):not([type="radio"]),
        .egh-content form.egh-card > p select,
        .egh-content form.egh-card > p textarea {
            width: 100%;
            min-height: 42px;
            margin-top: 7px;
            padding: 9px 11px;
            border: 1px solid #cfd9e7;
            border-radius: 9px;
            background: #ffffff;
            color: #172944;
            font: inherit;
        }

        .egh-content table {
            max-width: 100%;
        }

        .egh-content td,
        .egh-content th {
            overflow-wrap: anywhere;
        }

        .admin-footer {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 28px;
            border-top: 1px solid #e5eaf1;
            background: #ffffff;
            color: #7a8798;
            font-size: 12px;
        }

        .egh-skip-link {
            position: fixed;
            z-index: 2000;
            top: 8px;
            left: 8px;
            padding: 9px 12px;
            border-radius: 8px;
            background: #ffffff;
            color: #163657;
            font-weight: 700;
            text-decoration: none;
            transform: translateY(-150%);
        }

        .egh-skip-link:focus {
            transform: translateY(0);
        }

        @media (max-width: 900px) {
            .egh-content {
                padding: 16px;
            }

            .egh-card {
                padding: 16px;
            }

            .egh-content table {
                min-width: 560px;
            }

            .admin-footer {
                padding: 16px;
                flex-direction: column;
            }
        }
    </style>

    @stack('head')
    <link rel="stylesheet" href="{{ $themeCss('css/egh-sg-admin.css') }}">
</head>

@php
    $adminUser = auth()->user();
    $adminRole = $adminUser?->getRoleNames()->first();
    $adminRoleLabel = $adminRole
        ? ucwords(str_replace('-', ' ', $adminRole))
        : 'Admin';
@endphp

<body class="admin-body">

<a href="#admin-main" class="egh-skip-link">
    Skip to admin content
</a>

<div class="admin-shell">

    <aside
        class="admin-sidebar"
        id="adminSidebar"
        aria-label="Administration"
    >
        <div class="admin-brand">
            <div class="admin-brand-mark" aria-hidden="true">
                <img src="{{ rescue(fn () => app(\App\Services\HomepageContentService::class)->mediaUrl(app(\App\Services\SettingService::class)->get('general', 'logo_path'), asset('images/eagle-global-hub-logo.png')), asset('images/eagle-global-hub-logo.png'), false) }}" alt="" width="240" height="241">
            </div>

            <div class="admin-brand-copy">
                <strong>Eagle Global Hub</strong>
                <span>Admin Panel</span>
            </div>
        </div>

        <nav class="admin-nav">

            <div class="admin-nav-section">
                <span class="admin-nav-title">MAIN</span>

                @feature('dashboard')
                    <a
                        href="{{ route('dashboard') }}"
                        class="admin-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 10.8 12 3l9 7.8V21h-6v-6H9v6H3z"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                @endfeature

                @can('users.view')
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="9" cy="8" r="4"/>
                            <path d="M2.5 20c.8-4 3-6 6.5-6s5.7 2 6.5 6M16 7a3 3 0 1 1 0 6M17 14c2.6.4 4.2 2.2 4.7 5"/>
                        </svg>
                        <span>Users</span>
                    </a>
                @endcan

                @can('roles.view')
                    <a
                        href="{{ route('admin.roles.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 3 4 7v5c0 5 3.4 8 8 9 4.6-1 8-4 8-9V7z"/>
                            <path d="m8.5 12 2.2 2.2 4.8-5"/>
                        </svg>
                        <span>Roles &amp; Permissions</span>
                    </a>
                @endcan
            </div>

            <div class="admin-nav-section">
                <span class="admin-nav-title">SYSTEM</span>

                @role('super-admin')
                    <a
                        href="{{ route('admin.features.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.features.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 7h16M4 12h16M4 17h16"/>
                            <circle cx="9" cy="7" r="2"/>
                            <circle cx="15" cy="12" r="2"/>
                            <circle cx="8" cy="17" r="2"/>
                        </svg>
                        <span>Feature Control</span>
                    </a>
                @endrole

                @can('settings.view')
                    <a
                        href="{{ route('admin.settings.manage') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21H10v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H3v-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.6V3h4v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.1v4H21a1.7 1.7 0 0 0-1.6 1z"/>
                        </svg>
                        <span>Settings</span>
                    </a>
                @endcan

                @can('master-data.view')
                    <a
                        href="{{ route('admin.categories.manage') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="16" rx="2"/>
                            <path d="M8 4v16M3 10h18"/>
                        </svg>
                        <span>Categories</span>
                    </a>

                    <a
                        href="{{ route('admin.currencies.manage') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.currencies.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M15.5 8.5c-.8-.7-1.9-1-3.2-1-1.8 0-3.2.8-3.2 2.1 0 3.4 6.8 1.7 6.8 5.2 0 1.3-1.4 2.2-3.4 2.2-1.5 0-2.8-.4-3.8-1.3M12 5v14"/>
                        </svg>
                        <span>Currencies</span>
                    </a>

                    <a
                        href="{{ route('admin.master-data.manage') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.master-data.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M3 12h18M12 3c2.8 2.7 4 5.7 4 9s-1.2 6.3-4 9c-2.8-2.7-4-5.7-4-9s1.2-6.3 4-9z"/>
                        </svg>
                        <span>Countries</span>
                    </a>

                    <a
                        href="{{ route('admin.languages.manage') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 5h16v12H8l-4 4z"/>
                            <path d="M8 9h8M8 13h5"/>
                        </svg>
                        <span>Languages</span>
                    </a>

                    <a
                        href="{{ route('admin.destinations.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.destinations.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11z"/>
                            <circle cx="12" cy="10" r="2.6"/>
                        </svg>
                        <span>Destinations</span>
                    </a>

                    <a
                        href="{{ route('admin.service-destinations.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.service-destinations.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6 3h9l4 4v14H6z"/>
                            <path d="M9 13h6M9 16.5h4"/>
                        </svg>
                        <span>Services &amp; Destinations</span>
                    </a>
                @endcan

                @can('content.view')
                    <a
                        href="{{ route('admin.team-members.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.team-members.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M2.5 21c.7-4.3 2.9-6.5 6.5-6.5s5.8 2.2 6.5 6.5"/>
                        </svg>
                        <span>Team</span>
                    </a>

                    <a
                        href="{{ route('admin.study-destinations.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.study-destinations.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2 9l10-5 10 5-10 5z"/>
                            <path d="M6 11v5c3 2 9 2 12 0v-5"/>
                        </svg>
                        <span>Study destinations</span>
                    </a>

                    <a
                        href="{{ route('admin.news-ticker.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.news-ticker.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 6h16v12H4z"/>
                            <path d="M8 10h8M8 14h5"/>
                        </svg>
                        <span>News ticker</span>
                    </a>

                    <a
                        href="{{ route('admin.offers.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.offers.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20 12 12 20 4 12V4h8z"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                        </svg>
                        <span>Offers</span>
                    </a>

                    <a
                        href="{{ route('admin.reviews.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>
                        </svg>
                        <span>Reviews</span>
                    </a>

                    @can('content.view')
                        <a
                            href="{{ route('admin.media.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 17-5-5-9 8"/></svg>
                            <span>Media Library</span>
                        </a>
                    @endcan

                    @can('content.view')
                        <a
                            href="{{ route('admin.content.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.content.*') && ! request()->routeIs('admin.content.approvals*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h10l4 4v14H5z"/><path d="M8 11h8M8 15h8M8 7h4"/></svg>
                            <span>Website Content</span>
                        </a>
                    @endcan

                    @can('content.approve')
                        <a
                            href="{{ route('admin.content.approvals') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.content.approvals*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 12l2 2 4-4"/><path d="M12 3l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6z"/></svg>
                            <span>Content Approval</span>
                        </a>
                    @endcan

                    @can('settings.manage')
                        <a
                            href="{{ route('admin.facts.edit') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.facts.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16M7 16V9M12 16V5M17 16v-4"/></svg>
                            <span>Business Facts</span>
                        </a>
                    @endcan

                    @can('menus.manage')
                        <a
                            href="{{ route('admin.menus.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                            <span>Menus &amp; Branding</span>
                        </a>
                    @endcan

                    @can('content.manage')
                        <a
                            href="{{ route('admin.contact-directory.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.contact-directory.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>
                            <span>Contact Directory</span>
                        </a>
                    @endcan

                    @can('forums.moderate')
                        <a
                            href="{{ route('admin.forums.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.forums.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h12v9H8l-4 3z"/><path d="M8 17h8l4 3V9h-4"/></svg>
                            <span>Forums</span>
                        </a>
                    @endcan

                    @can('rewards.manage')
                        <a
                            href="{{ route('admin.rewards.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.rewards.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l2.5 5 5.5.8-4 3.9.9 5.5L12 15.6 7.1 18.2l.9-5.5-4-3.9 5.5-.8z"/></svg>
                            <span>Rewards</span>
                        </a>
                    @endcan

                    @can('content.view')
                        <a
                            href="{{ route('admin.translations.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.translations.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h9M8 3v2M6 5c0 4 3 7 6 8M11 5c-1 4-4 7-7 8"/><path d="m13 21 4-10 4 10M14.5 17h5"/></svg>
                            <span>Translations</span>
                        </a>
                    @endcan

                    @can('seo.manage')
                        <a
                            href="{{ route('admin.seo.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.seo.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                            <span>SEO &amp; Redirects</span>
                        </a>
                    @endcan
                @endcan
            </div>

            @canany(['student-enquiries.view', 'student-applications.view', 'courses.view', 'appointments.view', 'payments.view'])
                <div class="admin-nav-section">
                    <span class="admin-nav-title">SINGAPORE CONSULTANCY</span>

                    @can('student-enquiries.view')
                        <a
                            href="{{ route('admin.student-enquiries.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.student-enquiries.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 5h16v12H9l-5 4V5Z"/>
                                <path d="M8 9h8M8 13h5"/>
                            </svg>
                            <span>Student Enquiries</span>
                        </a>
                    @endcan

                    @can('student-applications.view')
                        <a
                            href="{{ route('admin.student-applications.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.student-applications.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m3 9 9-5 9 5-9 5z"/>
                                <path d="M6 11v5c3.5 3 8.5 3 12 0v-5"/>
                            </svg>
                            <span>Student Applications</span>
                        </a>
                    @endcan

                    @can('courses.view')
                        <a
                            href="{{ route('admin.courses.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
                            <span>Courses &amp; Intakes</span>
                        </a>
                        <a
                            href="{{ route('admin.eligibility-rules.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.eligibility-rules.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9"/></svg>
                            <span>Eligibility rules</span>
                        </a>
                    @endcan

                    @can('student-applications.view')
                        <a
                            href="{{ route('admin.success-pipeline.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.success-pipeline.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20 10 14l4 4 6-8"/><path d="M14 10h6v6"/></svg>
                            <span>Upcoming Success</span>
                        </a>
                    @endcan

                    @can('appointments.view')
                        <a
                            href="{{ route('admin.appointments.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
                            <span>Appointments</span>
                        </a>
                    @endcan

                    @can('payments.view')
                        <a
                            href="{{ route('admin.payments.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="6" width="20" height="13" rx="2"/><path d="M2 10h20"/></svg>
                            <span>Payments</span>
                        </a>
                    @endcan
                </div>
            @endcanany

            <div class="admin-nav-section">
                <span class="admin-nav-title">BUSINESS</span>

                @role('admin|super-admin')
                <a href="{{ route('admin.flight-enquiries.index') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.flight-enquiries.*') ? 'active' : '' }}">
                    <span aria-hidden="true">✈</span>
                    <span>Flight Enquiries</span>
                </a>
                @endrole

                @can('service-enquiries.view')
                    <a
                        href="{{ route('admin.service-enquiries.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.service-enquiries.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6 3h9l4 4v14H6z"/>
                            <path d="M9 13h6M9 16.5h4"/>
                        </svg>
                        <span>Service Enquiries</span>
                    </a>
                @endcan

                @role('admin|super-admin')
                <a
                    href="{{ route('admin.bookings.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M8 3v4M16 3v4M3 10h18"/>
                    </svg>
                    <span>Bookings</span>
                </a>
                @endrole

                @can('agents.view')
                    <a
                        href="{{ route('admin.agents.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="7" r="4"/>
                            <path d="M4 21c.7-4.7 3.4-7 8-7s7.3 2.3 8 7"/>
                        </svg>
                        <span>Agents</span>
                    </a>
                @endcan

                @can('affiliates.view')
                    <a
                        href="{{ route('admin.affiliates.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.affiliates.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="6" cy="12" r="3"/>
                            <circle cx="18" cy="6" r="3"/>
                            <circle cx="18" cy="18" r="3"/>
                            <path d="m8.8 10.6 6.4-3.2M8.8 13.4l6.4 3.2"/>
                        </svg>
                        <span>Affiliates</span>
                    </a>
                @endcan

                @can('students.view')
                    <a
                        href="{{ route('admin.students.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m3 9 9-5 9 5-9 5z"/>
                            <path d="M6 11v5c3.5 3 8.5 3 12 0v-5"/>
                        </svg>
                        <span>Students</span>
                    </a>
                @endcan

                @can('institutions.view')
                    <a
                        href="{{ route('admin.institutions.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.institutions.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 21h18M5 21V9l7-5 7 5v12"/>
                            <path d="M9 21v-6h6v6"/>
                        </svg>
                        <span>Institutions</span>
                    </a>
                @endcan

                @can('reports.view')
                    <a
                        href="{{ route('admin.reports.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>
                        </svg>
                        <span>Reports</span>
                    </a>
                @endcan
            </div>

            @can('system-logs.view')
                <div class="admin-nav-section">
                    <span class="admin-nav-title">SYSTEM INFO</span>

                    <a
                        href="{{ route('admin.system-logs.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.system-logs.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="4" y="3" width="16" height="18" rx="2"/>
                            <path d="M8 8h8M8 12h8M8 16h5"/>
                        </svg>
                        <span>System Logs</span>
                    </a>
                </div>
            @endcan

            @can('activity-logs.view')
                <div class="admin-nav-section">
                    <span class="admin-nav-title">USERS &amp; SECURITY</span>

                    <a
                        href="{{ route('admin.activity-logs.index') }}"
                        class="admin-nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 8v4l3 2"/>
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                        <span>Activity Logs</span>
                    </a>

                    @if(auth()->user()?->hasAnyRole(['admin', 'super-admin']))
                        <a
                            href="{{ route('admin.launch-checklist') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.launch-checklist') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9 11l3 3 8-8"/>
                                <path d="M20 12v7H4V5h11"/>
                            </svg>
                            <span>Launch checklist</span>
                        </a>
                    @endif

                    @can('integrations.view')
                        <a
                            href="{{ route('admin.integrations.index') }}"
                            class="admin-nav-link {{ request()->routeIs('admin.integrations.*') ? 'active' : '' }}"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9 7V3M15 7V3M7 7h10v4a5 5 0 0 1-10 0z"/>
                                <path d="M12 16v5"/>
                            </svg>
                            <span>API &amp; Integrations</span>
                        </a>
                    @endcan
                </div>
            @endcan
        </nav>
    </aside>

    <button
        type="button"
        class="admin-sidebar-backdrop"
        id="adminSidebarBackdrop"
        aria-label="Close navigation"
        hidden
    ></button>

    <div class="admin-main">

        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button
                    type="button"
                    class="admin-menu-button"
                    id="adminMenuButton"
                    aria-controls="adminSidebar"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>

            <div class="admin-topbar-actions">
                <div class="admin-user">
                    <div class="admin-user-avatar">
                        {{ strtoupper(substr($adminUser?->name ?? 'A', 0, 1)) }}
                    </div>

                    <div class="admin-user-copy">
                        <strong>{{ $adminUser?->name ?? 'Admin User' }}</strong>
                        <span>{{ $adminRoleLabel }}</span>
                    </div>
                </div>

                <a class="admin-topbar-link admin-topbar-site-link" href="{{ route('home') }}">View site</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="admin-topbar-link" type="submit">Sign out</button>
                </form>
            </div>
        </header>

        <main
            id="admin-main"
            class="@yield('page-class', 'admin-page')"
        >
            @if (
                session('status')
                && ! request()->routeIs('admin.settings.*')
                && ! request()->routeIs('admin.features.*')
            )
                <div class="egh-alert" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="admin-footer">
            <span>
                &copy; {{ date('Y') }} Eagle Global Hub LTD. All rights reserved.
            </span>

            <span>
                Version 1.0.0
            </span>
        </footer>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const menuButton = document.getElementById('adminMenuButton');
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('adminSidebarBackdrop');

    if (!menuButton || !sidebar || !backdrop) {
        return;
    }

    const setOpen = function (open) {
        const mobileOpen = Boolean(open) && window.innerWidth <= 1024;

        sidebar.classList.toggle('is-open', mobileOpen);
        document.body.classList.toggle('admin-nav-open', mobileOpen);
        backdrop.hidden = !mobileOpen;

        menuButton.setAttribute(
            'aria-expanded',
            mobileOpen ? 'true' : 'false'
        );
    };

    menuButton.addEventListener('click', function () {
        setOpen(!sidebar.classList.contains('is-open'));
    });

    backdrop.addEventListener('click', function () {
        setOpen(false);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 1024) {
            setOpen(false);
        }
    });

    sidebar
        .querySelectorAll('.admin-nav-link')
        .forEach(function (link) {
            link.addEventListener('click', function () {
                setOpen(false);
            });
        });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
});
</script>

@yield('scripts')

</body>
</html>
