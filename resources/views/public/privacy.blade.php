@extends('layouts.site')

@section('title', __('Privacy Policy'))
@section('meta_description', __('How Eagle Global Hub LTD collects, uses, stores and protects the personal information you share with us.'))
@section('body_class', 'sg-body-v2')

@php
    $custom = app(\App\Services\Content\PageContent::class)->section('privacy-policy');
    $facts = app(\App\Services\SiteFacts::class);
    $siteContact = $siteContact ?? [];
    $sections = [
        [__('Who we are'), [__('Eagle Global Hub LTD is a study consultancy and travel agency in Dhaka, Bangladesh. This policy explains how we handle the personal information you give us through this website, by phone, by WhatsApp or at our office.')]],
        [__('What we collect'), [
            __('Contact details you give us: name, phone or WhatsApp number and, if you share it, your email address.'),
            __('Details about your enquiry: the course, intake, destination, travel dates or message you send.'),
            __('If you open an account: your login email, password (stored only in encrypted form) and account settings.'),
            __('If you become a student client: the documents you upload, such as your passport, certificates and financial papers, and the history of your application.'),
            __('Basic technical data needed to run the website safely, such as your IP address in security logs.'),
        ]],
        [__('Why we use it'), [
            __('To answer your enquiry and give you the consultation or quote you asked for.'),
            __('To prepare and follow your study, visa or travel application with you.'),
            __('To keep your account secure and to meet our legal and accounting duties.'),
            __('We do not sell your personal information, and we do not use it for unrelated advertising.'),
        ]],
        [__('Who can see it'), [
            __('Only authorised Eagle Global Hub staff who work on your enquiry or application.'),
            __('Institutions, embassies, Singapore\'s ICA or travel suppliers, only when you ask us to apply or book on your behalf and only the information that application needs.'),
            __('Service providers that run this website for us (hosting and email), under confidentiality.'),
        ]],
        [__('How we protect it'), [
            __('Documents are stored privately and are never published on the website.'),
            __('Pages with personal information are only available after you sign in, and staff access depends on their role.'),
        ]],
        [__('How long we keep it'), [
            __('We keep enquiry and application records for as long as we need them to serve you and to meet legal requirements, and then delete or anonymise them.'),
        ]],
        [__('Your choices'), [
            __('You can ask us to show, correct or delete your personal information, or to stop contacting you, at any time.'),
            __('Contact us using the details below. We will reply as soon as we can.'),
        ]],
    ];
@endphp

@section('content')
<main class="v2-legal">
    <div class="v2-shell">
        <nav class="v2-crumbs" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> / <span aria-current="page">{{ __('Privacy Policy') }}</span></nav>
        <h1>{{ __('Privacy Policy') }}</h1>
        @if($custom)
            <div class="v2-legal-body"><x-content-body :text="$custom->localized('body')" /></div>
            @if($custom->updated_at)<p class="v2-legal-updated">{{ __('Last updated: :date', ['date' => $custom->updated_at->translatedFormat('j F Y')]) }}</p>@endif
        @else
            <div class="v2-legal-body">
                @foreach($sections as [$heading, $points])
                    <section>
                        <h2>{{ $heading }}</h2>
                        @if(count($points) === 1)<p>{{ $points[0] }}</p>@else<ul>@foreach($points as $point)<li>{{ $point }}</li>@endforeach</ul>@endif
                    </section>
                @endforeach
            </div>
        @endif
        <section class="v2-card v2-legal-contact" aria-labelledby="privacy-contact">
            <h2 id="privacy-contact">{{ __('Questions about your data') }}</h2>
            <ul>
                @if(filled(data_get($siteContact, 'email')))<li>{{ __('Email') }}: <a href="mailto:{{ data_get($siteContact, 'email') }}">{{ data_get($siteContact, 'email') }}</a></li>@endif
                @if($facts->phone())<li>{{ __('Phone') }}: <a href="{{ $facts->telUrl() }}">{{ $facts->phone() }}</a></li>@endif
                @if(filled(data_get($siteContact, 'address')))<li>{{ __('Address') }}: {{ data_get($siteContact, 'address') }}</li>@endif
            </ul>
            <a class="v2-link" href="{{ route('support') }}">{{ __('Contact Us') }}</a>
        </section>
    </div>
</main>
@endsection
