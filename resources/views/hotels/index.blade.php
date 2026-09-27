@extends('layouts.site')

@section('title', $pageContent['meta_title'])
@section('meta_description', $pageContent['meta_description'])
@section('canonical', route('hotels.index'))
@section('og_title', $pageContent['meta_title'])
@section('og_description', $pageContent['meta_description'])
@section('twitter_card', 'summary_large_image')
@section('body_class', 'egho-page-body')

@push('head')
<link rel="stylesheet" href="{{ asset('css/egh-hotels.css') }}">
<link rel="stylesheet" href="{{ asset('css/egh-travel-pages.css') }}">
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'WebPage','name'=>$pageContent['meta_title'],'description'=>$pageContent['meta_description'],'url'=>route('hotels.index')], JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<main class="egh-travel-page">
    <section class="egh-travel-hero">
        <div class="egho-shell">
            <span class="egho-eyebrow">{{ $pageContent['hero_eyebrow'] }}</span>
            <h1>{{ $pageContent['hero_title'] }}</h1>
            <p>{{ $pageContent['hero_subtitle'] }}</p>
        </div>
    </section>

    <div class="egho-shell">
        <section class="egh-search-panel" aria-label="Hotel search">
            @if($demoMode)
                <x-travel.demo-notice />
            @else
                <span class="egh-live-indicator">Live provider connected</span>
            @endif

            <form method="{{ $demoMode ? 'GET' : 'POST' }}" action="{{ $demoMode ? route('hotels.index').'#hotel-demo-results' : route('hotels.search') }}" class="egh-search-grid">
                @unless($demoMode) @csrf @endunless
                <label class="egho-field"><span>Destination</span><input name="destination" value="{{ old('destination') }}" placeholder="City or destination" maxlength="120" required></label>
                <label class="egho-field"><span>Check-in</span><input type="date" name="check_in" value="{{ old('check_in') }}" min="{{ now()->toDateString() }}" required></label>
                <label class="egho-field"><span>Check-out</span><input type="date" name="check_out" value="{{ old('check_out') }}" min="{{ now()->addDay()->toDateString() }}" required></label>
                <label class="egho-field"><span>Guests</span><select name="adults">@for($i=1;$i<=9;$i++)<option value="{{ $i }}">{{ $i }} {{ $i===1?'Guest':'Guests' }}</option>@endfor</select></label>
                <label class="egho-field"><span>Rooms</span><select name="rooms">@for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }} {{ $i===1?'Room':'Rooms' }}</option>@endfor</select></label>
                <button class="egho-btn egho-btn-primary" type="submit">{{ $demoMode ? 'View Demo Results' : 'Search Hotels' }}</button>
            </form>
        </section>

        <section class="egh-section" id="hotel-demo-results">
            <div class="egh-section-head"><div><h2>{{ $pageContent['featured_title'] }}</h2><p>{{ $demoMode ? 'Sample stays demonstrate the live-result layout.' : 'Search above for current supplier availability.' }}</p></div></div>
            @if($demoMode)
            <div class="egh-card-grid">
                @foreach($demoHotels as $hotel)
                <article class="egh-travel-card egh-results-demo-card">
                    <span class="egh-demo-badge">Demo Preview</span>
                    <img src="{{ $hotel['image'] }}" alt="Sample hotel preview in {{ $hotel['location'] }}" loading="lazy" width="600" height="400">
                    <div class="egh-card-body">
                        <h3>{{ $hotel['name'] }}</h3><p>{{ $hotel['location'] }}</p>
                        <span class="egh-sample-rating">★ {{ $hotel['sample_rating'] }} · Sample rating</span>
                        <div class="egh-chip-row">@foreach($hotel['amenities'] as $amenity)<span class="egh-chip">{{ $amenity }}</span>@endforeach</div>
                        <div class="egh-card-price"><div><small>{{ $hotel['sample_price_note'] }}</small><strong>{{ $hotel['sample_price'] }}</strong></div><span class="egh-muted-button" aria-disabled="true">Demo details</span></div>
                    </div>
                </article>
                @endforeach
            </div>
            @endif
        </section>

        <section class="egh-section"><div class="egh-section-head"><div><h2>{{ $pageContent['destinations_title'] }}</h2><p>Popular travel ideas for your next hotel search.</p></div></div><div class="egh-info-grid">
            @foreach([['Dubai','City breaks & premium stays'],['Bali','Resorts & family escapes'],['London','Central stays & business trips']] as $destination)
            <article class="egh-info-card"><h3>{{ $destination[0] }}</h3><p>{{ $destination[1] }}. Search live inventory when the provider is connected.</p></article>
            @endforeach
        </div></section>

        <section class="egh-section"><div class="egh-section-head"><div><h2>Why book hotels with Eagle Global Hub?</h2></div></div><div class="egh-info-grid">
            <article class="egh-info-card"><h3>Transparent availability</h3><p>Live rates are shown only when returned by the configured supplier.</p></article>
            <article class="egh-info-card"><h3>Secure account flow</h3><p>Hotel search remains behind the existing verified-account and permission controls.</p></article>
            <article class="egh-info-card"><h3>One travel partner</h3><p>Coordinate flights, stays, tours and visa preparation from the same travel hub.</p></article>
        </div></section>

        <section class="egh-section egh-faq"><div class="egh-section-head"><div><h2>Hotel booking FAQ</h2></div></div>
            <details><summary>Are Demo Preview prices live?</summary><p>No. Demo prices and ratings are samples only. Supplier-confirmed availability and rates appear only when a hotel provider is connected.</p></details>
            <details><summary>What happens when a provider is connected?</summary><p>The same search flow sends validated criteria to the configured provider and the results page renders the provider response instead of demo cards.</p></details>
        </section>
    </div>
</main>
@endsection