@extends('layouts.site')
@section('title', $pageContent['meta_title'])
@section('meta_description', $pageContent['meta_description'])
@section('canonical', route('tours.index'))
@section('og_title', $pageContent['meta_title'])
@section('og_description', $pageContent['meta_description'])
@section('body_class', 'egho-page-body')
@push('head')
<link rel="stylesheet" href="{{ asset('css/egh-travel-pages.css') }}">
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'WebPage','name'=>$pageContent['meta_title'],'description'=>$pageContent['meta_description'],'url'=>route('tours.index')], JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
@section('content')
<main class="egh-travel-page">
<section class="egh-travel-hero"><div class="egho-shell"><span class="egho-eyebrow">{{ $pageContent['hero_eyebrow'] }}</span><h1>{{ $pageContent['hero_title'] }}</h1><p>{{ $pageContent['hero_subtitle'] }}</p></div></section>
<div class="egho-shell">
<section class="egh-search-panel">@if($demoMode)<x-travel.demo-notice />@else<span class="egh-live-indicator">Live provider connected</span>@endif
<form method="POST" action="{{ route('tours.search') }}" class="egh-search-grid egh-search-grid-tours">@csrf
<label class="egho-field"><span>Destination</span><input name="destination" value="{{ old('destination') }}" placeholder="City or destination" maxlength="120" required></label>
<label class="egho-field"><span>Preferred date</span><input type="date" name="travel_date" value="{{ old('travel_date') }}" min="{{ now()->toDateString() }}"></label>
<label class="egho-field"><span>Travelers</span><select name="travelers">@for($i=1;$i<=12;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</select></label>
<button class="egho-btn egho-btn-primary" type="submit">{{ $demoMode ? 'View Demo Activities' : 'Search Activities' }}</button></form></section>
<section class="egh-section"><div class="egh-section-head"><div><h2>{{ $pageContent['featured_title'] }}</h2><p>{{ $demoMode ? 'Sample activities show how provider results will look.' : 'Search for current provider results.' }}</p></div></div>
@if($demoMode)<div class="egh-card-grid">@foreach($demoTours as $tour)<article class="egh-travel-card egh-results-demo-card"><span class="egh-demo-badge">Demo Preview</span><img src="{{ $tour['image'] }}" alt="Sample {{ strtolower($tour['category']) }} activity in {{ $tour['location'] }}" loading="lazy"><div class="egh-card-body"><h3>{{ $tour['title'] }}</h3><p>{{ $tour['location'] }}</p><div class="egh-chip-row"><span class="egh-chip">{{ $tour['category'] }}</span><span class="egh-chip">{{ $tour['duration'] }}</span></div><p>{{ $tour['summary'] }}</p><div class="egh-card-price"><div><small>Sample from price</small><strong>{{ $tour['sample_price'] }}</strong></div><span class="egh-muted-button" aria-disabled="true">Demo details</span></div></div></article>@endforeach</div>@endif</section>
<section class="egh-section"><div class="egh-section-head"><div><h2>{{ $pageContent['destinations_title'] }}</h2></div></div><div class="egh-info-grid">@foreach([['City Tours','Landmarks, neighbourhoods and guided highlights.'],['Adventure','Outdoor and active experience previews.'],['Family','Family-friendly sightseeing and discovery.'],['Cultural','Heritage, food and local culture experiences.']] as $item)<article class="egh-info-card"><h3>{{ $item[0] }}</h3><p>{{ $item[1] }}</p></article>@endforeach</div></section>
<section class="egh-section egh-faq"><div class="egh-section-head"><div><h2>Tours & activities FAQ</h2></div></div><details><summary>Are sample prices or spaces confirmed?</summary><p>No. Demo Preview cards are samples only. Live availability, schedules and prices appear only from a connected provider.</p></details><details><summary>Will the layout change after API connection?</summary><p>No major redesign is required: live provider results plug into the same search and result-card structure.</p></details></section>
</div></main>
@endsection