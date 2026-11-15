@extends('layouts.site')
@php
    $type = $entry->type;
    $title = $entry->localized('title');
    $pageUrl = $preview ? url()->current() : $entry->publicUrl();
    $listingRoutes = ['company_profile' => 'company.index', 'business_service' => 'business-services.index', 'project' => 'business-services.index', 'restaurant' => 'restaurants.index', 'inspiration' => 'inspiration.index', 'success_story' => 'success-stories.index', 'guide' => 'consultancy.singapore'];
    $parentRoute = $listingRoutes[$type->value] ?? 'home';
    $enquiryService = $type === \App\Enums\ContentType::BusinessService && in_array($entry->category, ['export-import', 'civil-construction'], true) ? $entry->category : null;
    $schemaType = match ($type) { \App\Enums\ContentType::Restaurant => 'Restaurant', \App\Enums\ContentType::Inspiration, \App\Enums\ContentType::Guide, \App\Enums\ContentType::SuccessStory => 'Article', \App\Enums\ContentType::BusinessService => 'Service', default => 'WebPage' };
@endphp
@section('title', $entry->seo_title ?: $title)
@section('meta_description', $entry->seo_description ?: (string) $entry->localized('summary'))
@section('body_class', 'public-page-body egho-page-body')
@if($preview)
@section('robots', 'noindex,nofollow')
@endif
@php($hasBengali = ! $preview && $pageUrl !== null && trim((string) $entry->body_bn) !== '')
@if($entry->canonical_url)
@section('canonical', $entry->canonical_url)
@elseif($hasBengali && app()->getLocale() === 'bn')
@section('canonical', $pageUrl.'?lang=bn')
@endif
@if($entry->cover)
@section('og_image', $entry->cover->url())
@endif
@push('head')
@if($hasBengali && ! $entry->canonical_url)
<link rel="alternate" hreflang="en" href="{{ $pageUrl }}">
<link rel="alternate" hreflang="bn" href="{{ $pageUrl }}?lang=bn">
<link rel="alternate" hreflang="x-default" href="{{ $pageUrl }}">
@endif
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => [
    array_filter([
        '@type' => $schemaType,
        $schemaType === 'Article' ? 'headline' : 'name' => $title,
        'description' => $entry->localized('summary'),
        'url' => $pageUrl,
        'image' => $entry->cover?->url(),
        'address' => $schemaType === 'Restaurant' ? $entry->location : null,
        'telephone' => $schemaType === 'Restaurant' ? $entry->contact_phone : null,
        'datePublished' => $schemaType === 'Article' ? $entry->published_at?->toAtomString() : null,
        'dateModified' => $schemaType === 'Article' ? $entry->updated_at?->toAtomString() : null,
        'publisher' => $schemaType === 'Article' ? ['@type' => 'Organization', 'name' => 'Eagle Global Hub LTD'] : null,
    ]),
    ['@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $type->label(), 'item' => route($parentRoute)],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => $pageUrl],
    ]],
]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        @if($preview)<div class="egho-result-summary" role="status"><strong>{{ __('Preview') }}:</strong> {{ ucfirst($entry->status) }} — {{ __('not visible to the public unless published.') }}</div>@endif
        <nav aria-label="Breadcrumb"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <a href="{{ route($parentRoute) }}">{{ __($type->label()) }}</a> › <span aria-current="page">{{ $title }}</span></nav>
        <section class="egho-contact-hero">
            <div>
                <span class="egho-eyebrow">{{ mb_strtoupper(__($type->label())) }}</span>
                <h1>{{ $title }}</h1>
                <p>{{ $entry->localized('summary') }}</p>
            </div>
            @if($entry->cover)<div class="egho-contact-hero-media"><img src="{{ $entry->cover->url() }}" alt="{{ $entry->cover->alt_text }}" @if($entry->cover->width) width="{{ $entry->cover->width }}" height="{{ $entry->cover->height }}" @endif style="height:auto"></div>@endif
        </section>
        <div class="egho-contact-grid">
            <section class="egho-contact-card">
                <x-content-body :text="$entry->localized('body')" />
                @if($type->requiresOfficialSource() && $entry->last_verified_at)
                    <p><small>{{ __('Information last checked against official sources') }}: {{ $entry->last_verified_at->format('j M Y') }}</small></p>
                @endif
                @if($type->requiresOfficialSource() && ! empty($entry->source_urls))
                    <h2>{{ __('Official sources') }}</h2>
                    <ul>@foreach($entry->source_urls as $source)<li><a href="{{ $source }}" rel="noopener nofollow" target="_blank">{{ parse_url($source, PHP_URL_HOST) }}</a></li>@endforeach</ul>
                    <p class="egho-sample-note">{{ __('Rules and requirements can change. Always confirm with the official source; final decisions are made by the institution or the authorities.') }}</p>
                @endif
            </section>
            @if($entry->location || $entry->contact_phone || $entry->contact_email || $entry->website_url || $enquiryService)
                <section class="egho-contact-card">
                    <h2>{{ __('Contact information') }}</h2>
                    @if($entry->location)<p>{{ $entry->location }}@if($entry->latitude !== null) — <a href="https://www.openstreetmap.org/?mlat={{ $entry->latitude }}&mlon={{ $entry->longitude }}#map=17/{{ $entry->latitude }}/{{ $entry->longitude }}" target="_blank" rel="noopener">{{ __('View on map') }}</a>@endif</p>@endif
                    @if($entry->contact_phone)<p><a href="tel:{{ preg_replace('/[^+0-9]/', '', $entry->contact_phone) }}">{{ $entry->contact_phone }}</a></p>@endif
                    @if($entry->contact_email)<p><a href="mailto:{{ $entry->contact_email }}">{{ $entry->contact_email }}</a></p>@endif
                    @if($entry->website_url)<p><a href="{{ $entry->website_url }}" target="_blank" rel="noopener">{{ parse_url($entry->website_url, PHP_URL_HOST) }}</a></p>@endif
                    @if($enquiryService)<p><a class="egho-btn egho-btn-primary" href="{{ route('service-enquiries.create', $enquiryService) }}">{{ __('Send an enquiry') }}</a></p>@endif
                </section>
            @endif
        </div>
        @if($entry->gallery->isNotEmpty())
            <section class="egho-contact-card" aria-label="{{ __('Photo gallery') }}"><div class="egho-contact-grid">@foreach($entry->gallery as $image)<img src="{{ $image->url() }}" alt="{{ $image->alt_text }}" loading="lazy" @if($image->width) width="{{ $image->width }}" height="{{ $image->height }}" @endif style="width:100%;height:auto;border-radius:12px">@endforeach</div></section>
        @endif
        @if(isset($projects) && $projects->isNotEmpty())
            <section class="egho-contact-card"><h2>{{ __('Projects') }}</h2><ul>@foreach($projects as $project)<li><a href="{{ $project->publicUrl() }}">{{ $project->localized('title') }}</a>@if($project->location) — {{ $project->location }}@endif</li>@endforeach</ul></section>
        @endif
        @if($relatedDestinations->isNotEmpty())
            <section class="egho-contact-card"><h2>{{ __('Places You May Like') }}</h2><ul>@foreach($relatedDestinations as $destination)<li><a href="{{ route('explore-bangladesh.show', $destination->slug) }}">{{ $destination->name }}</a></li>@endforeach</ul></section>
        @endif
        @if($related->isNotEmpty())
            <section class="egho-contact-card"><h2>{{ __('Related') }}</h2><ul>@foreach($related as $item)@if($item->publicUrl())<li><a href="{{ $item->publicUrl() }}">{{ $item->localized('title') }}</a></li>@endif @endforeach</ul></section>
        @endif
    </div>
</main>
@endsection
