@extends('layouts.site')
@php
    $pageContent = app(\App\Services\Travel\TravelPageContent::class)->for('work_visa');
    $demoCatalog = app(\App\Services\Travel\TravelDemoCatalog::class);
    $workVisaServices = $demoCatalog->workVisaServices();
    $workVisaDestinations = $demoCatalog->workVisaDestinations();
@endphp
@section('title', $pageContent['meta_title'])
@section('meta_description', $pageContent['meta_description'])
@section('canonical', route('work-visa.index'))
@section('og_title', $pageContent['meta_title'])
@section('og_description', $pageContent['meta_description'])
@section('body_class', 'egho-page-body')
@push('head')
<link rel="stylesheet" href="{{ asset('css/egh-travel-pages.css') }}">
<style>
.egh-work-visa-hero-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}.egh-work-visa-hero-actions .egho-btn{display:inline-flex;align-items:center;justify-content:center;min-height:44px;text-decoration:none}.egh-work-visa-secondary{background:transparent!important;color:#fff!important;border:1px solid rgba(255,255,255,.9)!important}.egh-work-visa-secondary:hover,.egh-work-visa-secondary:focus-visible{background:#fff!important;color:#075cff!important;border-color:#fff!important}@media(max-width:640px){.egh-work-visa-hero-actions{align-items:stretch}.egh-work-visa-hero-actions .egho-btn{width:100%}}
</style>
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'Service','name'=>'Work Visa Processing Support','provider'=>['@type'=>'TravelAgency','name'=>$siteName??'Eagle Global Hub LTD'],'description'=>$pageContent['meta_description'],'url'=>route('work-visa.index')], JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
@section('content')
<main class="egh-travel-page">
<section class="egh-travel-hero"><div class="egho-shell"><span class="egho-eyebrow">{{ $pageContent['hero_eyebrow'] }}</span><h1>{{ $pageContent['hero_title'] }}</h1><p>{{ $pageContent['hero_subtitle'] }}</p><div class="egh-work-visa-hero-actions"><a href="{{ route('work-visa.apply') }}" class="egho-btn egho-btn-primary">{{ $pageContent['cta_label'] }}</a><a href="{{ route('visa.index') }}" class="egho-btn egho-btn-ghost egh-work-visa-secondary">{{ __('Visa information') }}</a></div></div></section>
<div class="egho-shell">
<x-site.provider-badge state="enquiry" />
<div class="egh-legal-note"><strong>{{ __('No guarantee:') }}</strong> {{ __('We do not promise employment, sponsorship, eligibility, visa approval or a processing time. Employers and destination authorities make their own decisions.') }}</div>
<section class="egh-section"><div class="egh-section-head"><div><h2>{{ $pageContent['featured_title'] }}</h2><p>{{ __('Professional preparation support, subject to route and authority requirements.') }}</p></div></div><div class="egh-info-grid">@foreach($workVisaServices as $item)<article class="egh-info-card"><h3>{{ __($item['title']) }}</h3><p>{{ __($item['summary']) }}</p></article>@endforeach</div></section>
@php($workCountries = rescue(fn () => \App\Models\ServiceDestination::query()->where('service', 'work_visa')->where('is_active', true)->with('country:id,name,iso2')->orderBy('sort_order')->get()->pluck('country')->filter(), collect(), false))
<section class="egh-section" aria-labelledby="work-visa-countries"><div class="egh-section-head"><div><h2 id="work-visa-countries">{{ __('Work permit destinations') }}</h2><p>{{ __('Choose a country to start a work visa enquiry for it. The list is managed by our team.') }}</p></div></div>@if($workCountries->isEmpty())<p>{{ __('Destinations are being updated. Please send a general enquiry.') }}</p>@else<ul class="sg-country-grid">@foreach($workCountries as $country)<li><a href="{{ route('service-enquiries.create', ['service' => 'work-visa', 'destination' => $country->iso2]) }}"><span class="sg-country-code" aria-hidden="true">{{ $country->iso2 }}</span><span>{{ $country->iso2 === 'AE' ? __('Dubai (United Arab Emirates)') : __($country->name) }}</span></a></li>@endforeach</ul>@endif</section>
<section class="egh-section"><div class="egh-section-head"><div><h2>{{ __('How work visa preparation works') }}</h2></div></div><div class="egh-step-grid">@foreach([['01','Consultation','Review your intended destination, background and route without promising eligibility.'],['02','Document checklist','Prepare identity, qualification, experience and genuine employer documents where applicable.'],['03','Application preparation','Organise information for the supported application route and verify completeness.'],['04','Authority decision','The employer and immigration authority assess sponsorship, eligibility and the final visa outcome.']] as $step)<article class="egh-step"><b>{{ $step[0] }}</b><h3>{{ __($step[1]) }}</h3><p>{{ __($step[2]) }}</p></article>@endforeach</div></section>
<section class="egh-section"><div class="egh-info-grid"><article class="egh-info-card"><h3>{{ __('Typical documents') }}</h3><ul class="egh-list"><li>{{ __('Passport and identity records') }}</li><li>{{ __('Qualifications and employment history') }}</li><li>{{ __('Language evidence where required') }}</li><li>{{ __('Genuine employer/sponsorship documents where the route requires them') }}</li></ul></article><article class="egh-info-card"><h3>{{ __('Eligibility information') }}</h3><p>{{ __('Eligibility varies by country, occupation, employer, salary, qualifications and current immigration rules. A consultation is not an approval decision.') }}</p></article><article class="egh-info-card"><h3>{{ __('Safe application support') }}</h3><p>{{ __('No fake vacancies or employers are listed. Any real employer-supported route must use genuine documents and independent employer confirmation.') }}</p></article></div></section>
<section class="egh-section egh-faq"><div class="egh-section-head"><div><h2>{{ __('Work visa FAQ') }}</h2></div></div><details><summary>{{ __('Do you guarantee a job or sponsorship?') }}</summary><p>{{ __('No. We do not create or guarantee employers, jobs or sponsorship.') }}</p></details><details><summary>{{ __('Can you guarantee visa approval?') }}</summary><p>{{ __('No. The relevant immigration authority decides the application.') }}</p></details><details><summary>{{ __('Are the destination cards live opportunities?') }}</summary><p>{{ __('No. They are clearly labelled Demo Preview cards used to demonstrate the page design.') }}</p></details></section>
<section class="egh-cta-panel"><div><h2>{{ __('Need help understanding a work visa route?') }}</h2><p>{{ __('Start with a consultation-style preview; no application, payment or visa outcome is created.') }}</p></div><a href="{{ route('work-visa.apply') }}" class="egho-btn">{{ $pageContent['cta_label'] }}</a></section>
</div></main>
@endsection