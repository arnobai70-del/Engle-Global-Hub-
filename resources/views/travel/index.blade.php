@extends('layouts.site')

@section('title', __('Travel Services'))
@section('meta_description', __('Flights, hotels, tours and visa support from Eagle Global Hub LTD. Each service shows whether it uses a live provider, a demo preview or a personal enquiry.'))
@section('body_class', 'public-page-body egho-page-body')

@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero">
            <div>
                <span class="egho-eyebrow">{{ __('TRAVEL SERVICES') }}</span>
                <h1>{{ __('Travel Services') }}</h1>
                <p>{{ __('Flights, stays, tours and visa support in one place. Each service is labelled so you know whether results come from a live provider, a demo preview or a personal enquiry to our team.') }}</p>
            </div>
        </section>

        <nav aria-label="{{ __('Breadcrumb') }}" class="sg-breadcrumb"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <span aria-current="page">{{ __('Travel Services') }}</span></nav>

        <ul class="sg-legend" aria-label="{{ __('What the labels mean') }}">
            <li><x-site.provider-badge state="live" /> {{ __('Results come from a connected and tested supplier.') }}</li>
            <li><x-site.provider-badge state="waiting" /> {{ __('Available after the provider API is connected; use an enquiry for now.') }}</li>
            <li><x-site.provider-badge state="enquiry" /> {{ __('Send your details and a consultant replies personally.') }}</li>
            <li><x-site.provider-badge state="demo" /> {{ __('Local demo data only; never live.') }}</li>
        </ul>

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
                            <a class="sg-link-arrow" href="{{ $service['href'] }}">{{ __($service['cta']) }} <x-site.icon name="arrow" /></a>
                        </article>
                    @endforeach
                </div>
            </section>
            @endif
        @endforeach
        <section class="sg-card sg-cta-row">
            <div><h2>{{ __('Not sure which service you need?') }}</h2><p>{{ __('Visa decisions are made by the relevant authority and fares by the airline or supplier. We help you prepare and keep you informed.') }}</p></div>
            <div class="sg-cta-actions">@if($visaInfoVisible)<a class="sg-btn sg-btn-outline" href="{{ route('visa.index') }}">{{ __('Visa information') }}</a>@endif<a class="sg-btn sg-btn-red" href="{{ route('service-enquiries.create', 'tourist-visa') }}">{{ __('Send an Enquiry') }}</a></div>
        </section>
    </div>
</main>
@endsection
