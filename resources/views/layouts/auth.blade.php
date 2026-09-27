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

    @php
        $officialLogo = asset('images/eagle-global-hub-logo.png');
    @endphp

    <link rel="icon" type="image/png" href="{{ $officialLogo }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ Vite::fonts() }}

    @php
        $themeCss = static function (string $file): string {
            $modifiedAt = @filemtime(public_path($file));
            return asset($file).($modifiedAt ? '?v='.$modifiedAt : '');
        };
    @endphp

    <link rel="stylesheet" href="{{ $themeCss('css/egh-ota.css') }}">
    <link rel="stylesheet" href="{{ $themeCss('css/egh-final-polish.css') }}">
    <style>
        .egho-auth-brand{display:inline-flex!important;align-items:center!important;gap:10px!important;text-decoration:none!important;min-width:0;max-width:100%}
        .egho-auth-brand-logo{display:block!important;width:112px!important;height:68px!important;object-fit:cover!important;object-position:center 44%!important;flex:0 0 112px!important}
        .egho-auth-brand-copy{display:grid!important;gap:3px!important;line-height:1.1!important;min-width:0!important}
        .egho-auth-brand-copy strong{color:#fff!important;font-size:18px!important;font-weight:800!important;letter-spacing:-.2px!important;white-space:nowrap!important}
        .egho-auth-brand-copy small{color:rgba(255,255,255,.72)!important;font-size:10px!important;font-weight:700!important;letter-spacing:.07em!important;text-transform:uppercase!important;white-space:nowrap!important}
        @media(max-width:720px){
            .egho-auth-visual-top{align-items:center!important;gap:12px!important;flex-wrap:wrap!important}
            .egho-auth-brand-logo{width:96px!important;height:60px!important;flex-basis:96px!important}
            .egho-auth-brand-copy strong{font-size:15px!important}
            .egho-auth-brand-copy small{font-size:8.5px!important}
            .egho-auth-back{margin-left:auto!important}
        }
        @media(max-width:420px){
            .egho-auth-brand{gap:7px!important}
            .egho-auth-brand-logo{width:84px!important;height:54px!important;flex-basis:84px!important}
            .egho-auth-brand-copy strong{font-size:13px!important}
            .egho-auth-brand-copy small{font-size:7.5px!important;letter-spacing:.04em!important}
        }
    </style>
</head>

<body class="auth-body">

    <main class="egho-auth">

        <section class="egho-auth-visual">

            <div class="egho-auth-visual-top">

                <a href="{{ route('home') }}" class="egho-auth-brand" aria-label="Eagle Global Hub LTD home">
                    <img
                        src="{{ $officialLogo }}"
                        alt="Eagle Global Hub LTD logo"
                        class="egho-auth-brand-logo"
                    >
                    <span class="egho-auth-brand-copy">
                        <strong>Eagle Global Hub LTD</strong>
                        <small>Travel &amp; Visa Services</small>
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
                            <small>Protected areas require authenticated and verified access.</small>
                        </span>
                    </li>

                    <li>
                        <span class="egho-auth-tick" aria-hidden="true">&#10003;</span>
                        <span>
                            <strong>Clear booking flow</strong>
                            <small>Review important itinerary and booking states as you continue.</small>
                        </span>
                    </li>

                    <li>
                        <span class="egho-auth-tick" aria-hidden="true">&#10003;</span>
                        <span>
                            <strong>Server-authoritative actions</strong>
                            <small>Sensitive booking and payment decisions remain server controlled.</small>
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
                    <div class="auth-alert auth-alert-error" role="alert">
                        <strong>Please check the form.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status') && session('status') !== 'verification-link-sent')
                    <div class="auth-alert auth-alert-success" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')

                <div class="egho-auth-meta">
                    <span>Secure account access</span>
                    <a href="{{ route('home') }}">Eagle Global Hub LTD</a>
                </div>

            </div>

        </section>

    </main>

</body>
</html>
