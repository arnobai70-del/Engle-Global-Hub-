@extends('layouts.site')

@section('title', data_get($homeContent, 'seo.title', 'Flights & Travel'))
@section('meta_description', data_get($homeContent, 'seo.description'))
@section('canonical', data_get($homeContent, 'seo.canonical') ?: url()->current())
@section('robots', data_get($homeContent, 'seo.robots', 'index,follow'))
@section('og_title', data_get($homeContent, 'seo.og_title'))
@section('og_description', data_get($homeContent, 'seo.og_description'))
@section('og_image', data_get($homeContent, 'seo.og_image'))
@section('twitter_card', data_get($homeContent, 'seo.twitter_card', 'summary_large_image'))

@php
    $settings = data_get($homeContent, 'settings', []);
    $heroImage = data_get($settings, 'hero_image');
    $assurances = data_get($homeContent, 'assurances', []);
    $promoBanners = data_get($homeContent, 'promotions', []);
    $serviceTiles = data_get($homeContent, 'services', []);
    $popularDestinations = data_get($homeContent, 'destinations', []);
    $servicePanels = data_get($homeContent, 'service_panels', []);
    $whyChoose = data_get($homeContent, 'benefits', []);
    $testimonials = data_get($homeContent, 'testimonials', []);
    $appFeatures = data_get($homeContent, 'app_features', []);

    $flightSearchAction = route('login');
    $flightSearchMethod = 'GET';
    if (auth()->check()) {
        if (auth()->user()->can('flights.search')) {
            $flightSearchAction = route('flights.search');
            $flightSearchMethod = 'POST';
        } else {
            $flightSearchAction = route('dashboard');
        }
    }

    $travelServices = $travelServices ?? [];
    $serviceLink = static function (string $key) use ($travelServices): ?string {
        $service = $travelServices[$key] ?? null;
        if (! $service) return null;

        $routeName = $service['page_route_name'] ?? $service['route_name'] ?? null;
        if (! is_string($routeName) || ! \Illuminate\Support\Facades\Route::has($routeName)) return null;

        return route($routeName);
    };
    $serviceStatus = static function (string $key) use ($travelServices): ?array {
        $service = $travelServices[$key] ?? null;
        if (! $service) return null;
        return [
            'label' => $service['display_status'] ?? $service['status'] ?? 'Demo Preview',
            'live' => (bool) ($service['available'] ?? false),
        ];
    };
    $pageAssetVersion = static function (string $file): string {
        $modifiedAt = @filemtime(public_path($file));
        return $modifiedAt ? '?v='.$modifiedAt : '';
    };
    $organizationSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'TravelAgency',
        'name' => $siteName ?? 'Eagle Global Hub LTD',
        'url' => route('home'),
        'logo' => $siteLogo ?? null,
        'email' => data_get($siteContact ?? [], 'email'),
        'telephone' => data_get($siteContact ?? [], 'phone'),
        'address' => data_get($siteContact ?? [], 'address') ? ['@type' => 'PostalAddress', 'streetAddress' => data_get($siteContact, 'address')] : null,
    ]);
@endphp

@push('head')
    @if ($heroImage)<link rel="preload" as="image" href="{{ $heroImage }}">@endif
    <link rel="stylesheet" href="{{ asset('css/egh-home.css').$pageAssetVersion('css/egh-home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/egh-home-dynamic.css').$pageAssetVersion('css/egh-home-dynamic.css') }}">
    <meta name="robots" content="{{ data_get($homeContent, 'seo.robots', 'index,follow') }}">
    <link rel="canonical" href="{{ data_get($homeContent, 'seo.canonical') ?: url()->current() }}">
    <meta property="og:title" content="{{ data_get($homeContent, 'seo.og_title') ?: data_get($homeContent, 'seo.title') }}">
    <meta property="og:description" content="{{ data_get($homeContent, 'seo.og_description') ?: data_get($homeContent, 'seo.description') }}">
    <meta property="og:url" content="{{ data_get($homeContent, 'seo.canonical') ?: url()->current() }}">
    @if(data_get($homeContent, 'seo.og_image'))<meta property="og:image" content="{{ data_get($homeContent, 'seo.og_image') }}">@endif
    <meta name="twitter:card" content="{{ data_get($homeContent, 'seo.twitter_card', 'summary_large_image') }}">
    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<main class="site-home site-ota-home">
    @include('home._hero')
    @include('home._highlights')
    @include('home._services')
    @include('home._panels')
    @include('home._reasons')
    @include('home._app')
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/egh-home.js').$pageAssetVersion('js/egh-home.js') }}" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var swap = document.querySelector('[data-flight-swap]');
    var form = swap ? swap.form : null;
    if (swap && form) {
        var airports = form.querySelectorAll('[data-airport-code]');
        if (airports.length >= 2) {
            swap.hidden = false;
            swap.addEventListener('click', function () {
                var previous = airports[0].value;
                airports[0].value = airports[1].value;
                airports[1].value = previous;
                airports[0].focus();
            });
        }
    }
    document.querySelectorAll('[data-egho-rail]').forEach(function (rail) {
        var scope = rail.closest('section') || rail.parentElement;
        var controls = scope ? scope.querySelector('[data-egho-rail-nav]') : null;
        if (controls) controls.hidden = false;
    });
});
</script>
@endpush
