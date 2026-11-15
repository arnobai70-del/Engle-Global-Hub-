@extends('layouts.site')

@section('title', 'About')
@section(
    'meta_description',
    'Learn about the Eagle Global Hub LTD flight-first travel booking experience.'
)
@section('body_class', 'public-page-body')

@section('content')
@php($aboutIntro = app(\App\Services\Content\PageContent::class)->section('about-intro'))

    <main class="egho-doc">

        <div class="egho-shell">

            <section class="egho-doc-hero">
                <span class="egho-eyebrow">
                    ABOUT
                </span>

                <h1>
                    Eagle Global Hub LTD
                </h1>

                @if($aboutIntro)
                    @foreach(preg_split('/\R{2,}/', trim((string) $aboutIntro->localized('body'))) as $paragraph)<p>{!! nl2br(e($paragraph)) !!}</p>@endforeach
                @else
                <p>
                    Eagle Global Hub LTD provides a flight-first travel experience
                    built around clear search, careful review, secure account
                    access and visible booking progress.
                </p>
                @endif

                <div class="egho-doc-badges">
                    <span>Flight first</span>
                    <span>Secure accounts</span>
                    <span>Honest availability</span>
                </div>
            </section>

            <section class="egho-doc-grid">

                <article class="egho-doc-card">
                    <span class="egho-doc-card-num" aria-hidden="true">01</span>

                    <h2>What We Support</h2>

                    <p>
                        The current website focuses on flight search, traveler
                        review, order status, payment status and customer booking
                        confirmation pages where stored data is available.
                    </p>
                </article>

                <article class="egho-doc-card">
                    <span class="egho-doc-card-num" aria-hidden="true">02</span>

                    <h2>How We Present Travel Data</h2>

                    <p>
                        Availability and execution depend on the configured flight
                        data source. Development and fixture results are labelled
                        separately from live supplier inventory.
                    </p>
                </article>

                <article class="egho-doc-card">
                    <span class="egho-doc-card-num" aria-hidden="true">03</span>

                    <h2>Additional Services</h2>

                    <p>
                        Hotels, tours and visa discovery is shown as available only
                        when its provider integration is configured. Transactional
                        booking or application access is not implied.
                    </p>
                </article>

            </section>

            @if(($company['mission'] ?? '') !== '' || ($company['vision'] ?? '') !== '')
                <section class="egho-doc-grid" aria-label="{{ __('Mission and vision') }}">
                    @if($company['mission'] !== '')
                        <article class="egho-doc-card">
                            <h2>{{ __('Our Mission') }}</h2>
                            <x-content-body :text="$company['mission']" heading-tag="h3" />
                        </article>
                    @endif
                    @if($company['vision'] !== '')
                        <article class="egho-doc-card">
                            <h2>{{ __('Our Vision') }}</h2>
                            <x-content-body :text="$company['vision']" heading-tag="h3" />
                        </article>
                    @endif
                </section>
            @endif

            @if(($company['why_choose_us_bn'] ?? '') !== '')
                <section class="egho-doc-grid">
                    <article class="egho-doc-card">
                        <h2>{{ __('What makes us different') }}</h2>
                        <x-content-body :text="$company['why_choose_us_bn']" heading-tag="h3" />
                    </article>
                </section>
            @endif

            @if(isset($team) && $team->isNotEmpty())
                <section class="egho-doc-grid" aria-label="{{ __('Our team') }}">
                    @foreach($team as $member)
                        <article class="egho-doc-card">
                            @if($member->photo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($member->photo_path) }}" alt="{{ $member->name }}" loading="lazy" width="120" height="120" style="width:96px;height:96px;object-fit:cover;border-radius:50%">
                            @endif
                            <h2>{{ $member->name }}</h2>
                            <p><strong>{{ $member->role_title }}</strong></p>
                            @if($member->bio)<p>{{ $member->bio }}</p>@endif
                        </article>
                    @endforeach
                </section>
            @endif

            @if(isset($officeGallery) && $officeGallery->isNotEmpty())
                <section class="egho-doc-grid" aria-label="{{ __('Our office') }}">
                    @foreach($officeGallery as $image)
                        <figure class="egho-doc-card" style="margin:0">
                            <img src="{{ $image->url() }}" alt="{{ $image->alt_text }}" loading="lazy" @if($image->width) width="{{ $image->width }}" height="{{ $image->height }}" @endif style="width:100%;height:auto;border-radius:12px">
                            @if($image->caption)<figcaption>{{ $image->caption }}</figcaption>@endif
                        </figure>
                    @endforeach
                </section>
            @endif

        </div>

    </main>

@endsection
