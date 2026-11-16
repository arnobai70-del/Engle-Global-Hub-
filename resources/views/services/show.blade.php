@extends('layouts.site')

@section('title', __($service['title']))
@section('meta_description', __($service['summary']))
@section('body_class', 'public-page-body egho-page-body')

@push('head')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $service['title'],
        'description' => $service['summary'],
        'provider' => ['@type' => 'TravelAgency', 'name' => $siteName ?? 'Eagle Global Hub LTD', 'url' => url('/')],
        'url' => route('services.show', $slug),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@php
    $parent = match ($service['group']) {
        'study' => ['label' => __('Study in Singapore'), 'url' => route('consultancy.singapore')],
        'business' => ['label' => __('Business Services'), 'url' => route('business-services.index')],
        default => ['label' => __('Travel Services'), 'url' => route('travel-services.index')],
    };
    $actionUrl = static function (array $action): string {
        if (! empty($action['auth']) && ! auth()->check()) {
            return route('login');
        }

        return route(...$action['route']).(isset($action['fragment']) ? '#'.$action['fragment'] : '');
    };
    $primaryAction = collect($service['actions'] ?? [])->firstWhere('primary', true);
    $fallbackUrl = $enquiryUrl ?? ($primaryAction ? $actionUrl($primaryAction) : null);
    $fallbackLabel = $enquiryUrl === null && $primaryAction ? __($primaryAction['label']) : null;
    $countryUrl = static function ($country) use ($service, $enquiryUrl): string {
        if (($service['country_link'] ?? null) === 'consultation') {
            return $country->iso2 === 'SG' ? route('consultancy.singapore') : route('consultancy.singapore', ['destination' => $country->iso2]).'#consultation';
        }

        return $enquiryUrl.(str_contains((string) $enquiryUrl, '?') ? '&' : '?').'destination='.$country->iso2;
    };
@endphp
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero" style="--sg-hero-image: url('https://images.unsplash.com/photo-{{ $service['image'] }}?auto=format&fit=crop&w=1800&q=80')">
            <div>
                <span class="egho-eyebrow">{{ mb_strtoupper($parent['label']) }}</span>
                <h1>{{ __($service['title']) }}</h1>
                <p>{{ __($service['summary']) }}</p>
                <p><x-site.provider-badge :state="$pageState" /></p>
            </div>
        </section>

        <nav aria-label="{{ __('Breadcrumb') }}" class="sg-breadcrumb"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <a href="{{ $parent['url'] }}">{{ $parent['label'] }}</a> › <span aria-current="page">{{ __($service['title']) }}</span></nav>

        @foreach($apis as $capability => $state)
            @if($state !== \App\Services\Integrations\ProviderStatus::LIVE)
                <x-site.api-notice :capability="$capability" :state="$state" :enquiry-url="$fallbackUrl" :enquiry-label="$fallbackLabel" />
            @endif
        @endforeach

        <div class="sg-service-layout">
            <section class="sg-card sg-service-main" aria-labelledby="service-help">
                <span class="sg-icon"><x-site.icon :name="$service['icon']" /></span>
                <h2 id="service-help">{{ __('How we help') }}</h2>
                <ul class="sg-checklist">@foreach($service['points'] as $point)<li>{{ __($point) }}</li>@endforeach</ul>

                <div class="sg-cta-actions">
                    @foreach($service['actions'] ?? [] as $action)
                        <a class="sg-btn {{ ! empty($action['primary']) ? 'sg-btn-red' : 'sg-btn-outline' }}" href="{{ $actionUrl($action) }}">{{ __($action['label']) }}</a>
                    @endforeach
                    @if($enquiryUrl)
                        <a class="sg-btn sg-btn-red" href="{{ $enquiryUrl }}">{{ __('Send an enquiry') }}</a>
                    @endif
                    @if($cmsEntry)
                        <a class="sg-btn sg-btn-outline" href="{{ $cmsEntry->publicUrl() }}">{{ __('Learn more') }}</a>
                    @endif
                </div>
                <p class="sg-note">{{ __('Our team replies personally. Nothing is booked or charged when you send an enquiry.') }}</p>
            </section>

            <aside class="sg-card sg-service-side" aria-labelledby="service-how">
                <h2 id="service-how">{{ __('How it works') }}</h2>
                <ol class="sg-mini-steps">
                    <li>{{ __('Send your details and dates.') }}</li>
                    <li>{{ __('A consultant checks your request and replies.') }}</li>
                    <li>{{ __('You confirm before anything is booked or paid.') }}</li>
                </ol>
                @if($apis->isNotEmpty())
                    <h3>{{ __('Online features') }}</h3>
                    <ul class="sg-api-list">
                        @foreach($apis as $capability => $state)
                            <li><span>{{ __(\App\Services\Integrations\ProviderStatus::CAPABILITIES[$capability]['label']) }}</span><x-site.provider-badge :state="$state" /></li>
                        @endforeach
                    </ul>
                @endif
            </aside>
        </div>

        @if($countries->isNotEmpty())
            <section class="sg-section-tight" aria-labelledby="service-countries">
                <div class="sg-section-head sg-section-head-left">
                    <h2 id="service-countries">{{ __('Destinations we support') }}</h2>
                    <p>{{ __('Choose a country to start an enquiry for it. The list is managed by our team.') }}</p>
                </div>
                <ul class="sg-country-grid">
                    @foreach($countries as $country)
                        <li><a href="{{ $countryUrl($country) }}"><span class="sg-country-code" aria-hidden="true">{{ $country->iso2 }}</span><span>{{ $country->iso2 === 'AE' ? __('Dubai (United Arab Emirates)') : __($country->name) }}</span></a></li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if($related->isNotEmpty())
            <section class="sg-section-tight" aria-labelledby="service-related">
                <h2 id="service-related" class="sg-related-title">{{ __('Related services') }}</h2>
                <div class="sg-grid sg-grid-4">
                    @foreach($related as $relatedSlug => $item)
                        <a class="sg-card sg-feature" href="{{ route('services.show', $relatedSlug) }}"><span class="sg-icon sg-icon-navy"><x-site.icon :name="$item['icon']" /></span><h3>{{ __($item['title']) }}</h3></a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</main>
@endsection
