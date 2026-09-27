@extends('layouts.site')
@section('title', 'Tour Results')
@section('meta_description', $demoMode ? 'Demo tour results with sample content only.' : 'Live tour results from the configured provider.')
@section('canonical', route('tours.index'))
@section('robots', 'noindex,follow')
@section('body_class', 'egho-page-body')
@push('head')<link rel="stylesheet" href="{{ asset('css/egh-travel-pages.css') }}">@endpush
@section('content')
<main class="egh-travel-page"><div class="egho-shell" style="padding-top:28px">
@if($demoMode)<x-travel.demo-notice />@else<span class="egh-live-indicator">Live provider results</span>@endif
<form method="POST" action="{{ route('tours.search') }}" class="egh-search-panel egh-search-grid egh-search-grid-tours">@csrf
<label class="egho-field"><span>Destination</span><input name="destination" value="{{ $criteria['destination'] }}" required></label>
<label class="egho-field"><span>Preferred date</span><input type="date" name="travel_date" value="{{ $criteria['travel_date'] ?? '' }}"></label>
<label class="egho-field"><span>Travelers</span><select name="travelers">@for($i=1;$i<=12;$i++)<option value="{{ $i }}" @selected((int)$criteria['travelers']===$i)>{{ $i }}</option>@endfor</select></label>
<button class="egho-btn egho-btn-primary">Search again</button></form>
<section class="egh-section"><div class="egh-section-head"><div><h1 style="font-size:28px;margin:0;color:#08284d">Tours in {{ $criteria['destination'] }}</h1><p>{{ $criteria['travelers'] }} traveler(s) @if($criteria['travel_date']??null) · {{ $criteria['travel_date'] }} @endif</p></div></div>
@if($tours===[])<section class="egho-notice"><span class="egho-notice-status">No inventory returned</span><h2>No tours were returned</h2><p>The configured provider returned no matching tours. No availability, price or booking has been assumed.</p></section>
@else<div class="egh-card-grid">@foreach($tours as $tour)<article class="egh-travel-card {{ $demoMode?'egh-results-demo-card':'' }}">@if($demoMode)<span class="egh-demo-badge">Demo Preview</span><img src="{{ $tour['image'] }}" alt="Sample activity in {{ $tour['location'] }}" loading="lazy">@else<div style="height:170px;display:grid;place-items:center;background:#eef5ff;font-size:50px">🌴</div>@endif<div class="egh-card-body"><h3>{{ $tour['title'] }}</h3><p>{{ $tour['location'] ?? '' }}</p>@if(!empty($tour['summary']))<p>{{ $tour['summary'] }}</p>@endif @if($demoMode)<div class="egh-chip-row"><span class="egh-chip">{{ $tour['category'] }}</span><span class="egh-chip">{{ $tour['duration'] }}</span></div><div class="egh-card-price"><div><small>Sample from price</small><strong>{{ $tour['sample_price'] }}</strong></div><span class="egh-muted-button" aria-disabled="true">Sample details</span></div>@else<div class="egh-card-price"><div><small>Live provider result</small><strong>Provider quote</strong></div></div>@endif</div></article>@endforeach</div>@endif
</section></div></main>
@endsection