@extends('layouts.site')

@section('title', 'Contact & Support')
@section(
    'meta_description',
    'Contact Eagle Global Hub LTD in Shahjadpur, Gulshan, Dhaka for travel account, booking and service support.'
)
@section('body_class', 'public-page-body egho-page-body')

@section('content')
@php($pageContent = app(\App\Services\Content\PageContent::class))
@php($supportIntro = $pageContent->section('support-intro'))
@php($supportContacts = $pageContent->contacts())
@php($supportFaqs = $pageContent->published(\App\Enums\ContentType::Faq))

    <main class="egho-page">
        <div class="egho-shell">

            <section class="egho-contact-hero">
                <div>
                    <span class="egho-eyebrow">{{ __('CONTACT & SUPPORT') }}</span>
                    <h1>{{ __('Get in touch with Eagle Global Hub LTD') }}</h1>
                    @if($supportIntro)
                        @foreach(preg_split('/\R{2,}/', trim((string) $supportIntro->localized('body'))) as $paragraph)<p>{!! nl2br(e($paragraph)) !!}</p>@endforeach
                    @else
                    <p>
                        {{ __('Contact our Dhaka office for travel account, booking status and service enquiries using the official phone, email and office address below.') }}
                    </p>
                    @endif
                </div>

                <div class="egho-contact-hero-media">
                    <img
                        src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1400&q=85"
                        srcset="
                            https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1000&q=85 1x,
                            https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1800&q=85 2x
                        "
                        alt="{{ __('Eagle Global Hub customer support') }}"
                        loading="lazy"
                        width="1400"
                        height="933"
                    >
                </div>
            </section>

            <div class="egho-contact-grid">

                <section class="egho-contact-card">
                    <h2>{{ __('Contact information') }}</h2>

                    <div class="egho-contact-row">
                        <span class="egho-contact-icon" aria-hidden="true">
                            &#9993;
                        </span>
                        <div>
                            <strong>{{ __('Support email') }}</strong>
                            <a href="mailto:{{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}">
                                {{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}
                            </a>
                        </div>
                    </div>

                    <div class="egho-contact-row">
                        <span class="egho-contact-icon" aria-hidden="true">
                            &#9742;
                        </span>
                        <div>
                            <strong>{{ __('Mobile') }}</strong>
                            <a href="tel:{{ preg_replace('/[^+0-9]/', '', data_get($siteContact, 'phone', '01953626481')) }}">
                                {{ data_get($siteContact, 'phone', '01953626481') }}
                            </a>
                        </div>
                    </div>

                    <div class="egho-contact-row">
                        <span class="egho-contact-icon" aria-hidden="true">
                            &#127968;
                        </span>
                        <div>
                            <strong>{{ __('Office address') }}</strong>
                            <span>{{ data_get($siteContact, 'address', 'Mysha Chowdhury Tower, Ga-30/B, Pragati Sharani, Shahjadpur, Gulshan, Dhaka-1212') }}</span>
                        </div>
                    </div>

                    @if(data_get($siteContact, 'hours'))
                    <div class="egho-contact-row">
                        <span class="egho-contact-icon" aria-hidden="true">
                            &#128337;
                        </span>
                        <div>
                            <strong>{{ __('Support hours') }}</strong>
                            <span>{{ data_get($siteContact, 'hours') }}</span>
                        </div>
                    </div>
                    @endif

                    <div class="egho-map-panel" role="note" aria-label="{{ __('Eagle Global Hub office address') }}">
                        <span class="egho-map-canvas" aria-hidden="true"></span>

                        <div class="egho-map-panel-inner egho-map-panel-copy">
                            <span class="egho-map-pin" aria-hidden="true">
                                &#128205;
                            </span>
                            <span>
                                <strong>{{ __('Dhaka office') }}</strong><br>
                                {{ __('Mysha Chowdhury Tower') }}<br>
                                {{ __('Ga-30/B, Pragati Sharani, Shahjadpur,') }}<br>
                                {{ __('Gulshan, Dhaka-1212') }}
                            </span>
                        </div>
                    </div>
                </section>

                <section class="egho-contact-card">
                    <h2>{{ __('Send us a message') }}</h2>

                    <div class="egho-sample-note">
                        <span aria-hidden="true">&#9432;</span>
                        <span>
                            <strong>{{ __('The website message form is not connected yet.') }}</strong>
                            {{ __('Please contact us directly at') }}
                            <a href="mailto:{{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}">
                                {{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}
                            </a>
                            {{ __('or call') }}
                            <a href="tel:{{ preg_replace('/[^+0-9]/', '', data_get($siteContact, 'phone', '01953626481')) }}">
                                {{ data_get($siteContact, 'phone', '01953626481') }}
                            </a>.
                        </span>
                    </div>

                    <div class="egho-form-grid">
                        <label class="egho-field">
                            <span>{{ __('Full name') }}</span>
                            <input type="text" disabled>
                        </label>

                        <label class="egho-field">
                            <span>{{ __('Email address') }}</span>
                            <input type="email" disabled>
                        </label>

                        <label class="egho-field">
                            <span>{{ __('Phone number') }}</span>
                            <input type="tel" disabled>
                        </label>

                        <label class="egho-field">
                            <span>{{ __('Subject') }}</span>
                            <select disabled>
                                <option>{{ __('Select a topic') }}</option>
                            </select>
                        </label>

                        <label class="egho-field egho-field-wide">
                            <span>{{ __('Message') }}</span>
                            <textarea class="egho-textarea" disabled></textarea>
                        </label>
                    </div>

                    <div class="egho-form-foot">
                        <a
                            href="mailto:{{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}"
                            class="egho-btn egho-btn-primary"
                        >
                            {{ __('Email support') }}
                        </a>
                        <small>
                            {{ __('For booking status, signed-in customers can also use their account and My Bookings pages.') }}
                        </small>
                    </div>
                </section>

            </div>

            <div class="egho-contact-grid" style="margin-top:20px">
                <article class="egho-contact-card">
                    <h2>{{ __('Booking Status') }}</h2>
                    <p class="egho-result-summary">
                        {{ __('Signed-in customers can open My Bookings to review stored order and payment status for their own flight booking attempts.') }}
                    </p>
                </article>

                <article class="egho-contact-card">
                    <h2>{{ __('Account Access') }}</h2>
                    <p class="egho-result-summary">
                        {{ __('If you cannot access protected pages, confirm that your email address is verified and that you are signed in with the account used for the booking.') }}
                    </p>
                </article>

                <article class="egho-contact-card">
                    <h2>{{ __('Official Contact Channels') }}</h2>
                    <p class="egho-result-summary">
                        {{ __('Mobile:') }} <a href="tel:{{ preg_replace('/[^+0-9]/', '', data_get($siteContact, 'phone', '01953626481')) }}">{{ data_get($siteContact, 'phone', '01953626481') }}</a><br>
                        {{ __('Email:') }} <a href="mailto:{{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}">{{ data_get($siteContact, 'email', 'info@eagleglobalhub.com') }}</a><br>
                        {{ data_get($siteContact, 'address', 'Mysha Chowdhury Tower, Ga-30/B, Pragati Sharani, Shahjadpur, Gulshan, Dhaka-1212') }}
                    </p>
                </article>
            </div>

            @if($supportContacts->isEmpty())
                <section class="egho-contact-card" id="directory" style="margin-top:20px" aria-labelledby="contact-directory-empty"><h2 id="contact-directory-empty">{{ __('Phone directory') }}</h2><p>{{ __('Our department numbers and the 24/7 IP calling line are being confirmed. Please use the official phone and email above in the meantime.') }}</p></section>
            @endif
            @if($supportContacts->isNotEmpty())
                <section class="egho-contact-card" id="directory" style="margin-top:20px" aria-labelledby="contact-directory">
                    <h2 id="contact-directory">{{ __('Phone directory') }}</h2>
                    @foreach($supportContacts as $line)
                        <div class="egho-contact-row">
                            <span class="egho-contact-icon" aria-hidden="true">{{ $line->kind === 'ip_24x7' ? '24' : '☎' }}</span>
                            <div>
                                <strong>{{ $line->label }}@if($line->kind === 'ip_24x7') — {{ __('24/7 IP calling') }}@endif</strong>
                                <a href="tel:{{ preg_replace('/[^+0-9]/', '', $line->phone) }}">{{ $line->phone }}</a>
                                @if($line->contact_name || $line->department)<span>{{ trim($line->contact_name.' '.($line->department ? '· '.$line->department : ''), ' ·') }}</span>@endif
                                @if($line->availability)<span>{{ $line->availability }}</span>@endif
                            </div>
                        </div>
                    @endforeach
                </section>
            @endif

            @if($supportFaqs->isNotEmpty())
                <section class="egho-contact-card" style="margin-top:20px" aria-labelledby="support-faqs">
                    <h2 id="support-faqs">{{ __('Frequently asked questions') }}</h2>
                    @foreach($supportFaqs as $faq)
                        <details><summary>{{ $faq->localized('title') }}</summary><p style="white-space:pre-line">{{ $faq->localized('body') ?: $faq->localized('summary') }}</p></details>
                    @endforeach
                </section>
                @push('head')
                <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $supportFaqs->map(fn ($faq) => ['@type' => 'Question', 'name' => $faq->title, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->body ?: $faq->summary]])->values()->all()], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
                @endpush
            @endif

        </div>
    </main>

@endsection
