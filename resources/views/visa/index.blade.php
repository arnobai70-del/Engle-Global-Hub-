@extends('layouts.site')
@section('title', $pageContent['meta_title'])
@section('meta_description', $pageContent['meta_description'])
@section('canonical', route('visa.index'))
@section('og_title', $pageContent['meta_title'])
@section('og_description', $pageContent['meta_description'])
@section('body_class', 'egho-page-body')
@push('head')
<link rel="stylesheet" href="{{ asset('css/egh-travel-pages.css') }}">
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'Service','name'=>'Visa Assistance','provider'=>['@type'=>'TravelAgency','name'=>$siteName??'Eagle Global Hub LTD'],'description'=>$pageContent['meta_description'],'url'=>route('visa.index')], JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
@section('content')
<main class="egh-travel-page">
<section class="egh-travel-hero"><div class="egho-shell"><span class="egho-eyebrow">{{ $pageContent['hero_eyebrow'] }}</span><h1>{{ $pageContent['hero_title'] }}</h1><p>{{ $pageContent['hero_subtitle'] }}</p></div></section>
<div class="egho-shell">
<section class="egh-search-panel">
@if($demoMode)<x-travel.demo-notice message="Sample content shown for demonstration. Live, trip-specific visa information will appear when the provider API is connected." />@else<span class="egh-live-indicator">Live visa information provider connected</span>@endif
@if($countries->isNotEmpty())
<form method="POST" action="{{ route('visa.requirements') }}" class="egh-search-grid">@csrf
<label class="egho-field"><span>Passport nationality</span><select name="nationality" required><option value="">Select country</option>@foreach($countries as $country)<option value="{{ $country->iso3 }}">{{ $country->name }}</option>@endforeach</select></label>
<label class="egho-field"><span>Origin country</span><select name="origin_country" required><option value="">Select country</option>@foreach($countries as $country)<option value="{{ $country->iso3 }}">{{ $country->name }}</option>@endforeach</select></label>
<label class="egho-field"><span>Destination country</span><select name="destination_country" required><option value="">Select country</option>@foreach($countries as $country)<option value="{{ $country->iso3 }}">{{ $country->name }}</option>@endforeach</select></label>
<label class="egho-field"><span>Departure</span><input type="date" name="departure_date" min="{{ now()->toDateString() }}" required><input type="time" name="departure_time" required style="margin-top:6px"></label>
<label class="egho-field"><span>Arrival</span><input type="date" name="arrival_date" min="{{ now()->toDateString() }}" required><input type="time" name="arrival_time" required style="margin-top:6px"></label>
<button class="egho-btn egho-btn-primary">{{ $demoMode ? 'View Demo Guidance' : $pageContent['cta_label'] }}</button></form>
@else<section class="egho-notice"><h2>Country information is unavailable</h2><p>The country catalogue must be available before a trip-specific lookup can run.</p></section>@endif
</section>
<div class="egh-legal-note"><strong>Important:</strong> Eagle Global Hub can assist with information and document preparation. Visa approval, refusal, entry and processing decisions are made only by the relevant government authority and are never guaranteed.</div>
<section class="egh-section"><div class="egh-section-head"><div><h2>{{ $pageContent['featured_title'] }}</h2><p>Support categories vary by destination and visa rules.</p></div></div><div class="egh-info-grid">@foreach($visaServices as $item)<article class="egh-info-card"><h3>{{ $item['title'] }}</h3><p>{{ $item['summary'] }}</p></article>@endforeach</div></section>
<section class="egh-section"><div class="egh-section-head"><div><h2>{{ $pageContent['destinations_title'] }}</h2></div></div><div class="egh-step-grid">@foreach($visaSteps as $step)<article class="egh-step"><b>{{ $step['number'] }}</b><h3>{{ $step['title'] }}</h3><p>{{ $step['summary'] }}</p></article>@endforeach</div></section>
<section class="egh-section"><div class="egh-info-grid"><article class="egh-info-card"><h3>Typical documents</h3><ul class="egh-list">@foreach($visaDocuments as $document)<li>{{ $document }}</li>@endforeach</ul></article><article class="egh-info-card"><h3>Common visa types</h3><ul class="egh-list"><li>Tourist / visitor where applicable</li><li>Business where applicable</li><li>Student where applicable</li><li>Transit and other destination-specific categories</li></ul></article><article class="egh-info-card"><h3>Before you apply</h3><p>Requirements change by nationality, destination, travel purpose and authority. Always rely on the latest provider/authority information for a real application.</p></article></div></section>
<section class="egh-section egh-faq"><div class="egh-section-head"><div><h2>Visa assistance FAQ</h2></div></div><details><summary>Do you guarantee visa approval?</summary><p>No. Approval is solely the decision of the relevant authority.</p></details><details><summary>Is Demo Preview government advice?</summary><p>No. Demo Preview is sample content only and must not be used as a real application checklist.</p></details><details><summary>What changes after the provider API is connected?</summary><p>The lookup uses your validated trip details and renders the connected provider's information instead of sample guidance.</p></details></section>
</div></main>
@endsection