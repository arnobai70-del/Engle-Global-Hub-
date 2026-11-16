@extends('layouts.site')

@section('title', 'Terms & Policies')
@section(
    'meta_description',
    'Terms and policy notes for using the Eagle Global Hub LTD travel website.'
)
@section('body_class', 'public-page-body terms-page-body')

@section('content')
@php($termsSection = app(\App\Services\Content\PageContent::class)->section('terms'))

    <main class="egho-doc">

        <div class="egho-shell">

            <section class="egho-doc-hero">
                <span class="egho-eyebrow">
                    {{ __('TERMS & POLICIES') }}
                </span>

                <h1>
                    {{ __('Website terms and booking notices') }}
                </h1>

                <p>
                    {{ __('These customer-facing notices explain how the website presents flight search, booking status and supplier-dependent execution without replacing formal supplier or airline terms.') }}
                </p>
            </section>

            @if($termsSection)
            <section class="egho-doc-stack">
                <article class="egho-doc-card">
                    <h2>{{ $termsSection->localized('title') }}</h2>
                    @foreach(preg_split('/\R{2,}/', trim((string) $termsSection->localized('body'))) as $paragraph)<p>{!! nl2br(e($paragraph)) !!}</p>@endforeach
                    <p><small>{{ __('Last updated') }}: {{ $termsSection->updated_at->format('j M Y') }}</small></p>
                </article>
            </section>
            @else
            <section class="egho-doc-stack">

                <article class="egho-doc-card">
                    <h2>{{ __('Flight Availability') }}</h2>

                    <p>
                        {{ __('Flight availability, fares and payment readiness are dependent on the configured data source. Fixture or sandbox data must not be treated as live airline inventory.') }}
                    </p>
                </article>

                <article class="egho-doc-card">
                    <h2>{{ __('Booking Confirmation') }}</h2>

                    <p>
                        {{ __('Customer booking confirmations show stored order and payment status where available. They are not airline-issued tickets unless a supplier-issued ticket document is explicitly provided.') }}
                    </p>
                </article>

                <article class="egho-doc-card">
                    <h2>{{ __('Privacy Notice') }}</h2>

                    <p>
                        {{ __('Protected booking pages require account access and are scoped to the signed-in customer. The public website does not display supplier credentials or unsafe supplier identifiers.') }}
                    </p>
                </article>

            </section>
            @endif

            <p class="egho-doc-note">
                {{ __('These notices describe how this website presents travel data. They do not replace the terms, fare rules or ticket conditions issued by the airline or supplier that operates your journey.') }}
            </p>

        </div>

    </main>

@endsection
