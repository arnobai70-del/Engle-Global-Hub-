@extends('layouts.site')

@section('title', __('About Us'))
@section('meta_description', __('Eagle Global Hub LTD is a Singapore student consultancy and travel agency in Gulshan, Dhaka. Meet the team, see how we work and visit our office.'))
@section('body_class', 'sg-body-v2')

@php
    $facts = app(\App\Services\SiteFacts::class);
    $pageContent = app(\App\Services\Content\PageContent::class);
    $siteContact = $siteContact ?? [];
    $founded = $facts->get('founded_year');
    $story = $facts->get('about_story');
    $teamPhoto = $facts->get('team_photo_url') ?? $officeGallery->first()?->url();
    $companyFacts = array_values(array_filter([
        ['label' => __('Registered as'), 'value' => $siteName ?? 'Eagle Global Hub LTD'],
        $facts->get('trade_licence_no') ? ['label' => __('Trade licence'), 'value' => $facts->get('trade_licence_no')] : null,
        filled(data_get($siteContact, 'address')) ? ['label' => __('Office'), 'value' => __('Gulshan, Dhaka')] : null,
        $founded ? ['label' => __('Founded'), 'value' => $founded] : null,
    ]));
    $values = $pageContent->points('about-values') ?: [
        ['title' => __('Honest advice'), 'text' => __('We tell you if a course or visa is not a good fit, even if it means no fee for us.')],
        ['title' => __('Written quotes'), 'text' => __('Every fee is agreed in writing before you pay anything.')],
        ['title' => __('One consultant'), 'text' => __('The same person follows your file from first call to arrival.')],
    ];
    $officeLine = collect([__('Gulshan, Dhaka'), $facts->get('office_hours')])->filter()->implode(' · ');
@endphp

@section('content')
<main class="v2-about">
    <section class="v2-about-hero" aria-labelledby="about-title">
        <div class="v2-shell {{ $teamPhoto ? 'v2-split' : 'v2-about-single' }}">
            <div>
                <span class="v2-eyebrow">{{ __('About us') }}</span>
                <h1 id="about-title">{{ $founded ? __('A Dhaka team helping students and travellers since :year', ['year' => $founded]) : __('A Dhaka team helping students and travellers') }}</h1>
                @if($intro = $pageContent->section('about-intro'))
                    <div class="v2-lead"><x-content-body :text="$intro->localized('body')" heading-tag="h3" /></div>
                @elseif($story)
                    <p class="v2-lead">{{ $story }}</p>
                @else
                    <p class="v2-lead">{{ __('Eagle Global Hub LTD is a study consultancy and travel agency in Gulshan, Dhaka. Our main focus is helping Bangladeshi students study in Singapore, with visa and travel support from the same team.') }}</p>
                @endif
                <div class="v2-actions"><a class="v2-btn v2-btn-red" href="{{ route('consultancy.singapore') }}#apply">{{ __('Book a free consultation') }}</a><a class="v2-btn v2-btn-outline" href="{{ route('support') }}">{{ __('Contact Us') }}</a></div>
            </div>
            @if($teamPhoto)<img class="v2-about-photo" src="{{ $teamPhoto }}" alt="{{ __('Our team in the office') }}" loading="eager" decoding="async">@endif
        </div>
    </section>

    <section class="v2-section-tight v2-section-white" aria-label="{{ __('Company facts') }}">
        <div class="v2-shell">
            <dl class="v2-facts">
                @foreach($companyFacts as $fact)<div><dt>{{ $fact['label'] }}</dt><dd>{{ $fact['value'] }}</dd></div>@endforeach
            </dl>
        </div>
    </section>

    <section class="v2-section" aria-labelledby="values-title">
        <div class="v2-shell">
            <h2 id="values-title" class="v2-h2">{{ __('How we work') }}</h2>
            <div class="v2-values">
                @foreach($values as $value)
                    <article class="v2-card"><h3>{{ $value['title'] }}</h3>@if($value['text'] !== '')<p>{{ $value['text'] }}</p>@endif</article>
                @endforeach
            </div>

            @if(($company['mission'] ?? '') !== '' || ($company['vision'] ?? '') !== '' || ($company['why_choose_us_bn'] ?? '') !== '')
            <div class="v2-statements">
                @if(($company['mission'] ?? '') !== '')
                    <section class="v2-card"><h2 class="v2-card-title">{{ __('Our Mission') }}</h2><x-content-body :text="$company['mission']" heading-tag="h3" /></section>
                @endif
                @if(($company['vision'] ?? '') !== '')
                    <section class="v2-card"><h2 class="v2-card-title">{{ __('Our Vision') }}</h2><x-content-body :text="$company['vision']" heading-tag="h3" /></section>
                @endif
                @if(($company['why_choose_us_bn'] ?? '') !== '')
                    <section class="v2-card"><h2 class="v2-card-title">{{ __('What makes us different') }}</h2><x-content-body :text="$company['why_choose_us_bn']" heading-tag="h3" /></section>
                @endif
            </div>
            @endif
        </div>
    </section>

    @if($team->isNotEmpty())
    <section class="v2-section v2-section-white" id="team" aria-labelledby="team-title">
        <div class="v2-shell">
            <h2 id="team-title" class="v2-h2">{{ __('Our team') }}</h2>
            <ul class="v2-team v2-team-wide">
                @foreach($team as $member)
                    <li>
                        @if($member->photo_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($member->photo_path) }}" alt="{{ $member->name }}" loading="lazy" decoding="async">@else<span class="v2-team-initial" aria-hidden="true">{{ mb_substr($member->name, 0, 1) }}</span>@endif
                        <strong>{{ $member->name }}</strong><span>{{ $member->role_title }}</span>
                        @if($member->bio)<p>{{ $member->bio }}</p>@endif
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
    @endif

    @if($officeGallery->isNotEmpty())
    <section class="v2-section" id="office" aria-labelledby="office-title">
        <div class="v2-shell">
            <h2 id="office-title" class="v2-h2">{{ __('Our office') }}</h2>
            <div class="v2-gallery">
                @foreach($officeGallery as $image)
                    <figure><img src="{{ $image->url() }}" alt="{{ $image->alt_text }}" loading="lazy" @if($image->width) width="{{ $image->width }}" height="{{ $image->height }}" @endif>@if($image->caption)<figcaption>{{ $image->caption }}</figcaption>@endif</figure>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($companyProfiles->isNotEmpty())
    <section class="v2-section v2-section-white" id="company" aria-labelledby="company-title">
        <div class="v2-shell">
            <h2 id="company-title" class="v2-h2">{{ __('Company profile') }}</h2>
            <div class="v2-related">@foreach($companyProfiles as $profile)<a href="{{ $profile->publicUrl() }}">{{ $profile->localized('title') }} <span aria-hidden="true">→</span></a>@endforeach</div>
        </div>
    </section>
    @endif

    <section class="v2-section" aria-labelledby="meet-title">
        <div class="v2-shell">
            <div class="v2-cta-band">
                <div><h2 id="meet-title">{{ __('Meet us at the office') }}</h2>@if($officeLine !== '')<p>{{ $officeLine }}</p>@endif</div>
                <div class="v2-actions"><a class="v2-btn v2-btn-red" href="{{ route('support') }}">{{ __('Contact us') }}</a><a class="v2-btn v2-btn-white" href="{{ route('consultancy.singapore') }}#apply">{{ __('Free consultation') }}</a></div>
            </div>
        </div>
    </section>
</main>
@endsection
