@extends('layouts.site')

@section('title', 'Hotel Results')
@section('meta_description', $demoMode ? 'Demo hotel search results. Sample content only; live availability appears when the hotel provider is connected.' : 'Live hotel search results from the configured provider.')
@section('canonical', route('hotels.index'))
@section('robots', 'noindex,follow')
@section('body_class', 'egho-page-body')

@push('head')
<link rel="stylesheet" href="{{ asset('css/egh-hotels.css') }}">
<link rel="stylesheet" href="{{ asset('css/egh-travel-pages.css') }}">
@endpush

@section('content')
<main class="egh-travel-page"><div class="egho-shell" style="padding-top:28px">
    @if($demoMode)<x-travel.demo-notice />@else<span class="egh-live-indicator">Live provider results</span>@endif

    <form method="POST" action="{{ route('hotels.search') }}" class="egh-search-panel egh-search-grid" aria-label="Hotel search">
        @csrf
        <label class="egho-field"><span>Destination</span><input name="destination" value="{{ $criteria['destination'] }}" maxlength="120" required></label>
        <label class="egho-field"><span>Check-in</span><input type="date" name="check_in" value="{{ $criteria['check_in'] }}" required></label>
        <label class="egho-field"><span>Check-out</span><input type="date" name="check_out" value="{{ $criteria['check_out'] }}" required></label>
        <label class="egho-field"><span>Guests</span><select name="adults">@for($i=1;$i<=9;$i++)<option value="{{ $i }}" @selected((int)$criteria['adults']===$i)>{{ $i }}</option>@endfor</select></label>
        <label class="egho-field"><span>Rooms</span><select name="rooms">@for($i=1;$i<=5;$i++)<option value="{{ $i }}" @selected((int)$criteria['rooms']===$i)>{{ $i }}</option>@endfor</select></label>
        <button type="submit" class="egho-btn egho-btn-primary">Search again</button>
    </form>

    <section class="egh-section"><div class="egh-section-head"><div><h1 style="font-size:28px;margin:0;color:#08284d">Stays in {{ $criteria['destination'] }}</h1><p>{{ $criteria['check_in'] }} to {{ $criteria['check_out'] }} · {{ $criteria['adults'] }} guest(s) · {{ $criteria['rooms'] }} room(s)</p></div></div>
        @if($hotels === [])
            <section class="egho-notice" role="status"><span class="egho-notice-status">No inventory returned</span><h2>No hotel stays were returned</h2><p>The configured provider returned no matching inventory. No availability or price has been assumed.</p></section>
        @else
            <div class="egh-card-grid">
            @foreach($hotels as $hotel)
                <article class="egh-travel-card {{ $demoMode ? 'egh-results-demo-card' : '' }}">
                    @if($demoMode)<span class="egh-demo-badge">Demo Preview</span>@endif
                    @if($demoMode)<img src="{{ $hotel['image'] }}" alt="Sample hotel preview in {{ $hotel['location'] }}" loading="lazy">@else<div class="egh-result-media-glyph" style="height:170px;display:grid;place-items:center;background:#eef5ff;font-size:50px" aria-hidden="true">🏨</div>@endif
                    <div class="egh-card-body">
                        <h3>{{ $hotel['name'] }}</h3><p>{{ $hotel['location'] }}</p>
                        @if(!empty($hotel['summary']))<p>{{ $hotel['summary'] }}</p>@endif
                        @if($demoMode)
                            <span class="egh-sample-rating">★ {{ $hotel['sample_rating'] }} · Sample rating</span>
                            <div class="egh-chip-row">@foreach($hotel['amenities'] as $amenity)<span class="egh-chip">{{ $amenity }}</span>@endforeach</div>
                            <div class="egh-card-price"><div><small>{{ $hotel['sample_price_note'] }}</small><strong>{{ $hotel['sample_price'] }}</strong></div><span class="egh-muted-button" aria-disabled="true">Sample details</span></div>
                        @else
                            <div class="egh-card-price"><div><small>Live provider inventory</small><strong>See room & rate details</strong></div><a href="{{ route('hotels.rooms') }}" class="egho-btn egho-btn-primary">View rooms</a></div>
                        @endif
                    </div>
                </article>
            @endforeach
            </div>
        @endif
    </section>
</div></main>
@endsection