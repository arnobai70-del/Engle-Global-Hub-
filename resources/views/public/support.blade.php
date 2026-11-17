@extends('layouts.site')

@section('title', __('Contact Us'))
@section('meta_description', __('Talk to Eagle Global Hub LTD in Gulshan, Dhaka: WhatsApp, call, email or send a message for study in Malaysia and Singapore, visa and travel help.'))
@section('body_class', 'sg-body-v2')

@php
    $pageContent = app(\App\Services\Content\PageContent::class);
    $facts = app(\App\Services\SiteFacts::class);
    $supportIntro = $pageContent->section('support-intro');
    $supportContacts = $pageContent->contacts();
    $supportFaqs = $pageContent->published(\App\Enums\ContentType::Faq);
    $siteContact = $siteContact ?? [];
    $email = data_get($siteContact, 'email');
    $address = data_get($siteContact, 'address');
    $hours = $facts->get('office_hours') ?? data_get($siteContact, 'hours');
    $whatsappUrl = $facts->whatsappUrl(__('Hello Eagle Global Hub, I have a question.'));
    $directionsUrl = filled($address) ? 'https://www.google.com/maps/search/?api=1&query='.urlencode((string) $address) : null;
    $topics = \App\Http\Controllers\LeadController::CONTACT_TOPICS;
@endphp

@section('content')
<main class="v2-contact">
    <div class="v2-shell">
        <nav class="v2-crumbs" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> / <span aria-current="page">{{ __('Contact Us') }}</span></nav>
        <header class="v2-contact-head">
            <h1>{{ __('Talk to us') }}</h1>
            @if($supportIntro)
                @foreach(preg_split('/\R{2,}/', trim((string) $supportIntro->localized('body'))) as $paragraph)<p>{!! nl2br(e($paragraph)) !!}</p>@endforeach
            @else
                <p>{{ $whatsappUrl ? __('Fastest reply on WhatsApp. Or call, email, or visit our office in Gulshan, Dhaka.') : __('Call, email, or visit our office in Gulshan, Dhaka.') }}</p>
            @endif
        </header>

        <section aria-labelledby="contact-information">
            <h2 id="contact-information" class="sg-visually-hidden">{{ __('Contact information') }}</h2>
            <div class="v2-contact-cards">
                @if($whatsappUrl)
                    <a class="v2-contact-card is-whatsapp" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">
                        <span>WhatsApp</span><strong>{{ $facts->whatsappNumber() }}</strong><small>{{ __('Tap to start a chat') }}</small>
                    </a>
                @endif
                @if($facts->phone())
                    <a class="v2-contact-card" href="{{ $facts->telUrl() }}">
                        <span>{{ __('Call') }}</span><strong>{{ $facts->phone() }}</strong>@if($hours)<small>{{ $hours }}</small>@endif
                    </a>
                @endif
                @if(filled($email))
                    <a class="v2-contact-card" href="mailto:{{ $email }}">
                        <span>{{ __('Email') }}</span><strong>{{ $email }}</strong>@if($facts->get('reply_time'))<small>{{ __('Reply: :time', ['time' => $facts->get('reply_time')]) }}</small>@endif
                    </a>
                @endif
            </div>
        </section>

        <div class="v2-contact-grid">
            <section class="v2-form-card v2-contact-form" id="contact-form" aria-labelledby="contact-form-title">
                <form method="POST" action="{{ route('leads.contact') }}" class="v2-form" novalidate>
                    @csrf
                    <input type="hidden" name="return_anchor" value="contact-form">
                    <h2 id="contact-form-title">{{ __('Send a message') }}</h2>
                    <x-site.lead-success />
                    @if($errors->any())<ul class="v2-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
                    <div class="v2-row-2">
                        <label class="v2-field" for="contact-name">{{ __('Full name') }}<input id="contact-name" name="full_name" autocomplete="name" required maxlength="150" value="{{ old('full_name') }}"></label>
                        <label class="v2-field" for="contact-phone">{{ __('Phone / WhatsApp') }}<input id="contact-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required maxlength="32" value="{{ old('phone') }}" placeholder="01XXXXXXXXX"></label>
                    </div>
                    <label class="v2-field" for="contact-email">{{ __('Email (optional)') }}<input id="contact-email" name="email" type="email" autocomplete="email" maxlength="254" value="{{ old('email') }}"></label>
                    <label class="v2-field" for="contact-topic">{{ __('Topic') }}<select id="contact-topic" name="topic" required>@foreach($topics as $value => $label)<option value="{{ $value }}" @selected(old('topic', 'study') === $value)>{{ __($label) }}</option>@endforeach</select></label>
                    <label class="v2-field" for="contact-message">{{ __('Message') }}<textarea id="contact-message" name="message" required maxlength="2000">{{ old('message') }}</textarea></label>
                    <label class="v2-hp" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
                    <label class="v2-check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))><span>{{ __('I agree to be contacted about my enquiry.') }} <a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a></span></label>
                    <button type="submit" class="v2-btn v2-btn-red v2-btn-block">{{ __('Send message') }}</button>
                    <p class="v2-form-note">{{ __('Your message goes straight to our team. We reply by phone, WhatsApp or email.') }}</p>
                </form>
            </section>

            <div class="v2-contact-side">
                @if($facts->mapEmbedUrl())
                    <iframe class="v2-map" src="{{ $facts->mapEmbedUrl() }}" title="{{ __('Map of our office') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                @endif
                <section class="v2-card" aria-labelledby="office-address">
                    <h2 id="office-address" class="v2-card-title">{{ __('Office address') }}</h2>
                    @if(filled($address))<p>{{ $address }}</p>@endif
                    @if($hours)<p class="v2-muted">{{ $hours }}</p>@endif
                    @if($directionsUrl)<a class="v2-link" href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer">{{ __('Get directions') }} →</a>@endif
                </section>
                @if($facts->get('office_photo_url'))
                    <img class="v2-photo" src="{{ $facts->get('office_photo_url') }}" alt="{{ __('Our office reception') }}" loading="lazy" decoding="async">
                @endif
            </div>
        </div>

        @if($supportContacts->isNotEmpty())
            <section class="v2-card v2-directory" id="directory" aria-labelledby="contact-directory">
                <h2 id="contact-directory" class="v2-card-title">{{ __('Phone directory') }}</h2>
                <ul>
                    @foreach($supportContacts as $line)
                        <li>
                            <strong>{{ $line->label }}@if($line->kind === 'ip_24x7') — {{ __('24/7 IP calling') }}@endif</strong>
                            <a href="tel:{{ preg_replace('/[^+0-9]/', '', $line->phone) }}">{{ $line->phone }}</a>
                            @if($line->contact_name || $line->department)<span>{{ trim($line->contact_name.' '.($line->department ? '· '.$line->department : ''), ' ·') }}</span>@endif
                            @if($line->availability)<span>{{ $line->availability }}</span>@endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if($supportFaqs->isNotEmpty())
            <section class="v2-contact-faq" aria-labelledby="support-faqs">
                <h2 id="support-faqs" class="v2-h2">{{ __('Frequently asked questions') }}</h2>
                <div class="sg-faq">
                    @foreach($supportFaqs as $faq)
                        <details><summary>{{ $faq->localized('title') }}</summary><div class="sg-faq-answer"><x-content-body :text="$faq->localized('body') ?: $faq->localized('summary')" /></div></details>
                    @endforeach
                </div>
            </section>
            @push('head')
            <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $supportFaqs->map(fn ($faq) => ['@type' => 'Question', 'name' => $faq->title, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->body ?: $faq->summary]])->values()->all()], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
            @endpush
        @endif
    </div>
</main>
@endsection
