@extends('layouts.site')

@section('title', __('Travel Services'))
@section('meta_description', __('Visas, air tickets, hotels, tours and transport from Eagle Global Hub LTD in Dhaka. Send an enquiry and our team replies with options and a written quote.'))
@section('body_class', 'public-page-body egho-page-body')

@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero">
            <div>
                <span class="egho-eyebrow">{{ __('TRAVEL SERVICES') }}</span>
                <h1>{{ __('Travel Services') }}</h1>
                <p>{{ __('Visas, air tickets, hotels, tours and transport in one place. Send an enquiry and our team replies with options and a written quote before you pay anything.') }}</p>
            </div>
        </section>

        <nav aria-label="{{ __('Breadcrumb') }}" class="sg-breadcrumb"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <span aria-current="page">{{ __('Travel Services') }}</span></nav>

        @foreach($groups as $groupTitle => $services)
            @if($services !== [])
            <section class="sg-section-tight" aria-labelledby="travel-group-{{ $loop->index }}">
                <h2 id="travel-group-{{ $loop->index }}" class="sg-related-title">{{ __($groupTitle) }}</h2>
                <div class="sg-grid sg-grid-4">
                    @foreach($services as $service)
                        <article class="sg-card sg-feature sg-feature-left sg-service" data-service="{{ $service['key'] }}">
                            <span class="sg-icon {{ $service['state'] === 'enquiry' ? 'sg-icon-navy' : '' }}"><x-site.icon :name="$service['icon']" /></span>
                            <h3>{{ __($service['title']) }}</h3>
                            <p>{{ __($service['summary']) }}</p>
                            <x-site.provider-badge :state="$service['state']" />
                            <a class="sg-link-arrow" href="{{ $service['href'] }}">{{ $service['state'] === 'live' ? __($service['cta']) : __('Send an enquiry') }} <x-site.icon name="arrow" /></a>
                        </article>
                    @endforeach
                </div>
            </section>
            @endif
        @endforeach
        <section class="sg-card sg-cta-row">
            <div><h2>{{ __('Not sure which service you need?') }}</h2><p>{{ __('Visa decisions are made by the relevant authority and fares by the airline or supplier. We help you prepare and keep you informed.') }}</p></div>
            <div class="sg-cta-actions">@if($visaInfoVisible)<a class="sg-btn sg-btn-outline" href="{{ route('visa.index') }}">{{ __('Visa information') }}</a>@endif<a class="sg-btn sg-btn-red" href="{{ route('support') }}#contact-form">{{ __('Send an enquiry') }}</a></div>
        </section>
    </div>
</main>
@endsection
