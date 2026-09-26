@extends('layouts.site')

@section('title', 'Account Overview')
@section('body_class', 'account-body egho-page-body')

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            <header class="egho-page-head">
                <span class="egho-eyebrow">YOUR ACCOUNT</span>
                <h1>Account Overview</h1>
                <p>
                    Review the account information currently stored for your
                    Eagle Global Hub LTD travel account.
                </p>
            </header>

            <div class="egho-account-layout">

                {{--
                    Account sections. Entries without a page yet are shown as
                    disabled status items instead of dead links, because this
                    website does not store payment methods, travelers, visa
                    applications or notifications for an account.
                --}}
                <nav class="egho-account-nav" aria-label="Account sections">
                    <span class="egho-account-nav-title">Account</span>

                    <a href="{{ route('account.overview') }}" class="is-active">
                        Profile
                    </a>

                    @feature('bookings')
                        @can('flights.book')
                            <a href="{{ route('bookings.index') }}">
                                My Bookings
                            </a>
                        @endcan
                    @endfeature

                    @feature('support')
                        <a href="{{ route('support') }}">Support</a>
                    @endfeature

                    <span class="egho-account-nav-title">Not connected</span>

                    <span class="egho-account-nav-item">
                        Payment Methods
                        <small>Not stored</small>
                    </span>

                    <span class="egho-account-nav-item">
                        Traveler Profiles
                        <small>Not stored</small>
                    </span>

                    <span class="egho-account-nav-item">
                        Visa Applications
                        <small>Not stored</small>
                    </span>

                    <span class="egho-account-nav-item">
                        Work Visa Applications
                        <small>Not stored</small>
                    </span>

                    <span class="egho-account-nav-item">
                        Notifications
                        <small>Not stored</small>
                    </span>
                </nav>

                <div>

                    <section class="egho-contact-card" style="margin-bottom:20px">
                        <div class="egho-account-id">
                            <span class="egho-account-avatar">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>

                            <div>
                                <strong>{{ auth()->user()->name }}</strong>
                                <small>{{ auth()->user()->email }}</small>
                            </div>
                        </div>

                        <div class="egho-actions">
                            <span
                                class="egho-btn egho-btn-ghost"
                                aria-disabled="true"
                            >
                                Change photo
                            </span>
                            <small class="egho-result-summary">
                                Profile photos are not stored by this website, so
                                your initials are shown instead.
                            </small>
                        </div>
                    </section>

                    <section class="egho-contact-card" style="margin-bottom:20px">
                        <h2>Personal details</h2>

                        <form
                            method="POST"
                            action="{{ route('user-profile-information.update') }}"
                            class="egho-form-grid"
                        >
                            @csrf
                            @method('PUT')

                            @if (session('status') === \Laravel\Fortify\Fortify::PROFILE_INFORMATION_UPDATED)
                                <div
                                    class="egho-notice-status egho-notice-status-is-ready egho-field-wide"
                                    role="status"
                                >
                                    Profile information updated.
                                </div>
                            @endif

                            <label class="egho-field">
                                <span>Full Name</span>
                                <input
                                    id="account-name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name', auth()->user()->name) }}"
                                    autocomplete="name"
                                    required
                                    @error('name', 'updateProfileInformation')
                                        aria-invalid="true"
                                        aria-describedby="account-name-error"
                                    @enderror
                                >

                                @error('name', 'updateProfileInformation')
                                    <span
                                        id="account-name-error"
                                        class="egho-field-error"
                                    >
                                        {{ $message }}
                                    </span>
                                @enderror
                            </label>

                            <label class="egho-field">
                                <span>Email Address</span>
                                <input
                                    id="account-email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email', auth()->user()->email) }}"
                                    autocomplete="email"
                                    required
                                    @error('email', 'updateProfileInformation')
                                        aria-invalid="true"
                                        aria-describedby="account-email-error"
                                    @enderror
                                >

                                @error('email', 'updateProfileInformation')
                                    <span
                                        id="account-email-error"
                                        class="egho-field-error"
                                    >
                                        {{ $message }}
                                    </span>
                                @enderror
                            </label>

                            <p class="egho-filter-note egho-field-wide">
                                Changing your email address requires you to
                                verify the new address before returning to
                                verified-only pages.
                            </p>

                            <div class="egho-actions egho-field-wide">
                                <button
                                    type="submit"
                                    class="egho-btn egho-btn-primary"
                                >
                                    Save Profile
                                </button>
                            </div>
                        </form>
                    </section>

                    <section class="egho-contact-card">
                        <h2>Account status</h2>

                        <div class="egho-contact-row">
                            <span class="egho-contact-icon" aria-hidden="true">
                                &#10003;
                            </span>
                            <div>
                                <strong>Email Verification</strong>
                                <span>Verified</span>
                            </div>
                        </div>

                        <div class="egho-contact-row">
                            <span class="egho-contact-icon" aria-hidden="true">
                                &#128197;
                            </span>
                            <div>
                                <strong>Verified On</strong>
                                <span>
                                    {{
                                        auth()->user()->email_verified_at
                                            ?->format('M j, Y')
                                        ?? 'Verified'
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="egho-contact-row">
                            <span class="egho-contact-icon" aria-hidden="true">
                                &#128336;
                            </span>
                            <div>
                                <strong>Account Created</strong>
                                <span>
                                    {{
                                        auth()->user()->created_at
                                            ?->format('M j, Y')
                                        ?? '—'
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="egho-contact-row">
                            <span class="egho-contact-icon" aria-hidden="true">
                                &#9742;
                            </span>
                            <div>
                                <strong>Phone number</strong>
                                <span>Not stored in this account</span>
                            </div>
                        </div>

                        <div class="egho-contact-row">
                            <span class="egho-contact-icon" aria-hidden="true">
                                &#127968;
                            </span>
                            <div>
                                <strong>Address</strong>
                                <span>Not stored in this account</span>
                            </div>
                        </div>
                    </section>

                    <section class="egho-contact-card" style="margin-top:20px">
                        <h2>Continue planning</h2>

                        <p class="egho-result-summary">
                            Return to your dashboard or continue with a travel
                            service.
                        </p>

                        <div class="egho-actions">
                            @feature('dashboard')
                                <a
                                    href="{{ route('dashboard') }}"
                                    class="egho-btn egho-btn-ghost"
                                >
                                    Dashboard
                                </a>
                            @endfeature

                            @feature('flights')
                                @can('flights.search')
                                    <a
                                        href="{{ route('flights.index') }}"
                                        class="egho-btn egho-btn-primary"
                                    >
                                        Search Flights
                                    </a>
                                @endcan
                            @endfeature
                        </div>
                    </section>

                </div>

            </div>

        </div>
    </main>

@endsection
