<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Eagle Global Hub LTD')</title>

    <meta
        name="description"
        content="Secure account access for Eagle Global Hub LTD flight and travel services."
    >

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

    {{-- Mockup-aligned OTA theme. Scoped to `.egho-` classes only. --}}
    <link rel="stylesheet" href="{{ $themeCss('css/egh-ota.css') }}">
</head>

<body class="auth-body">

    <main class="egho-auth">

        <section class="egho-auth-visual">

            <div class="egho-auth-visual-top">

                <a href="{{ route('home') }}" class="egho-auth-brand">
                    <span class="egho-auth-brand-mark" aria-hidden="true">
                        &#9992;
                    </span>

                    <span class="egho-auth-brand-copy">
                        <strong>Eagle Global Hub LTD</strong>
                        <small>Flights &amp; Travel</small>
                    </span>
                </a>

                <a href="{{ route('home') }}" class="egho-auth-back">
                    Back to website
                </a>

            </div>

            <div class="egho-auth-visual-body">

                <span class="egho-eyebrow">
                    SECURE TRAVEL ACCOUNT
                </span>

                <h1>
                    @yield(
                        'hero-title',
                        'Your journey starts with secure account access.'
                    )
                </h1>

                <p>
                    @yield(
                        'hero-description',
                        'Search, review and manage your flight journey from one verified account.'
                    )
                </p>

                <ul class="egho-auth-points">

                    <li>
                        <span class="egho-auth-tick" aria-hidden="true">&#10003;</span>

                        <span>
                            <strong>Verified account access</strong>
                            <small>
                                Protected areas require authenticated and verified access.
                            </small>
                        </span>
                    </li>

                    <li>
                        <span class="egho-auth-tick" aria-hidden="true">&#10003;</span>

                        <span>
                            <strong>Clear booking flow</strong>
                            <small>
                                Review important itinerary and booking states as you continue.
                            </small>
                        </span>
                    </li>

                    <li>
                        <span class="egho-auth-tick" aria-hidden="true">&#10003;</span>

                        <span>
                            <strong>Server-authoritative actions</strong>
                            <small>
                                Sensitive booking and payment decisions remain server controlled.
                            </small>
                        </span>
                    </li>

                </ul>

            </div>

            <small class="egho-auth-visual-foot">
                Eagle Global Hub LTD
            </small>

        </section>

        <section class="egho-auth-panel">

            <div class="egho-auth-card">

                @if ($errors->any())
                    <div
                        class="auth-alert auth-alert-error"
                        role="alert"
                    >
                        <strong>
                            Please check the form.
                        </strong>

                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (
                    session('status')
                    && session('status') !== 'verification-link-sent'
                )
                    <div
                        class="auth-alert auth-alert-success"
                        role="status"
                    >
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')

                <div class="egho-auth-meta">
                    <span>Secure account access</span>

                    <a href="{{ route('home') }}">
                        Eagle Global Hub LTD
                    </a>
                </div>

            </div>

        </section>

    </main>

</body>
</html>
