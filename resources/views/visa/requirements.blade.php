@extends('layouts.site')
@section('title', 'Visa Requirements')
@section('meta_description', $demoMode ? 'Demo visa requirement guidance with sample content only.' : 'Visa requirement information from the configured provider.')
@section('canonical', route('visa.index'))
@section('robots', 'noindex,follow')
@section('body_class', 'egho-page-body')
@push('head')<link rel="stylesheet" href="{{ asset('css/egh-travel-pages.css') }}">@endpush
@section('content')
<main class="egh-travel-page"><div class="egho-shell" style="padding-top:30px">
@if($demoMode)<x-travel.demo-notice message="Sample content shown for demonstration. Live, trip-specific requirements will appear when the provider API is connected." />@else<span class="egh-live-indicator">Live provider information</span>@endif
<header class="egh-section"><div class="egh-section-head"><div><h1 style="font-size:30px;margin:0;color:#08284d">Visa information for {{ $criteria['destination_country'] }}</h1><p>Passport {{ $criteria['nationality'] }} · {{ $criteria['origin_country'] }} to {{ $criteria['destination_country'] }} · {{ $criteria['departure_date'] }}</p></div><a href="{{ route('visa.index') }}" class="egho-btn egho-btn-ghost">Check another trip</a></div></header>
@if(($information['summary']??'')==='' && ($information['requirements']??[])===[] && ($information['documents']??[])===[])
<section class="egho-notice"><span class="egho-notice-status">No information returned</span><h2>No visa information was returned</h2><p>The configured source returned no information. Eligibility, documents and approval have not been assumed.</p></section>
@else
@if(!empty($information['summary']))<section class="egh-info-card" style="margin-bottom:18px"><h2 style="margin-top:0">{{ $demoMode ? 'Demo trip guidance' : 'Trip summary' }}</h2><p>{{ $information['summary'] }}</p></section>@endif
<div class="egh-info-grid"><article class="egh-info-card"><h2>Requirements</h2>@if(($information['requirements']??[])===[])<p>No requirement details were returned.</p>@else<ul class="egh-list">@foreach($information['requirements'] as $item)<li>{{ $item }}</li>@endforeach</ul>@endif</article><article class="egh-info-card"><h2>Documents</h2>@if(($information['documents']??[])===[])<p>No document details were returned.</p>@else<ul class="egh-list">@foreach($information['documents'] as $item)<li>{{ $item }}</li>@endforeach</ul>@endif</article><article class="egh-info-card"><h2>Decision authority</h2><p>Requirements may change. Eagle Global Hub does not guarantee approval, boarding, entry or processing time. The relevant authority makes the final decision.</p></article></div>
@endif
<div class="egh-legal-note"><strong>Important:</strong> @if($demoMode) This is demonstration content, not a real visa assessment. @endif Visa approval depends on the relevant authority.</div>
</div></main>
@endsection