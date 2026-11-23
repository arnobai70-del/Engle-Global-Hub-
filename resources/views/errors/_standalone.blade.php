@php $errorCompanyName = rescue(fn (): string => app(\App\Services\SiteFacts::class)->companyName(), 'Eagle Global Hub LTD', false); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,follow">
    <title>{{ $title }} | {{ $errorCompanyName }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/eagle-global-hub-logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/egh-sg.css') }}">
</head>
<body class="sg-body">
    <header class="sg-site-header"><div class="sg-header"><div class="sg-shell sg-header-inner"><a href="{{ url('/') }}" class="sg-brand"><img class="sg-brand-logo" src="{{ asset('images/eagle-global-hub-logo.png') }}" alt="{{ $errorCompanyName }} logo" width="240" height="241"><span class="sg-brand-copy"><strong>{{ $errorCompanyName }}</strong></span></a></div></div></header>
    <main class="egho-page sg-error-page"><div class="sg-shell">
        <section class="sg-card sg-error-card" role="alert" aria-labelledby="error-title">
            <span class="sg-error-code">{{ $code }}</span>
            <h1 id="error-title">{{ $title }}</h1>
            <p>{{ $message }}</p>
            <div class="sg-cta-actions"><a href="{{ url('/') }}" class="sg-btn sg-btn-red">{{ __('Home') }}</a></div>
        </section>
    </div></main>
</body>
</html>
