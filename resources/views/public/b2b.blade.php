@extends('layouts.site')

@section('title', __('Become a B2B partner'))
@section('meta_description', __('Agents and travel businesses can partner with Eagle Global Hub LTD to refer students and travel clients. Apply online; our team reviews every application.'))
@section('body_class', 'sg-body-v2')

@php
    $applyUrl = auth()->check() ? route('b2b.register') : route('register', ['type' => 'agent']);
    $whatsappUrl = app(\App\Services\SiteFacts::class)->whatsappUrl(__('Hello, I would like to become a B2B partner.'));
    $steps = [
        [__('Create an account'), __('Choose "Agent / B2B partner" when you sign up.')],
        [__('Send your application'), __('Tell us about your agency, your location and the clients you serve.')],
        [__('Our team reviews it'), __('We may call you to verify your details before approval.')],
        [__('Start referring'), __('After approval, refer students and travel clients and follow each referral in the Agent Portal.')],
    ];
@endphp

@section('content')
<main>
    <section class="v2-service-hero-wrap">
        <div class="v2-shell">
            <nav class="v2-crumbs" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> / <span aria-current="page">{{ __('B2B partners') }}</span></nav>
            <div class="v2-service-hero">
                <div>
                    <span class="v2-pill v2-pill-light">{{ __('Partner programme') }}</span>
                    <h1>{{ __('Become a B2B partner') }}</h1>
                    <p>{{ __('Agents and travel businesses can refer students and travel clients to Eagle Global Hub. Our team handles the consultation, documents and follow-up, and you can track every referral online.') }}</p>
                    <div class="v2-actions">
                        <a class="v2-btn v2-btn-red" href="{{ $applyUrl }}">{{ __('Register as partner') }}</a>
                        @if($whatsappUrl)<a class="v2-btn v2-btn-wa" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">{{ __('Ask on WhatsApp') }}</a>@endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="v2-section v2-section-white" aria-labelledby="b2b-who-title">
        <div class="v2-shell v2-split v2-split-top">
            <div>
                <h2 id="b2b-who-title" class="v2-h2">{{ __('Who it is for') }}</h2>
                <ul class="v2-ticks">
                    <li>{{ __('Education agents and student counsellors') }}</li>
                    <li>{{ __('Travel agencies and ticketing offices') }}</li>
                    <li>{{ __('Schools, coaching centres and community organisations that advise students') }}</li>
                </ul>
            </div>
            <div>
                <h2 class="v2-h2">{{ __('What you get') }}</h2>
                <ul class="v2-ticks">
                    <li>{{ __('Our consultants handle the consultation, documents and follow-up for your clients') }}</li>
                    <li>{{ __('Study in Malaysia and Singapore, visas, air tickets, hotels and tours in one place') }}</li>
                    <li>{{ __('Each referral tracked in your Agent Portal') }}</li>
                    <li>{{ __('One contact person at our Gulshan office') }}</li>
                </ul>
            </div>
        </div>
    </section>
    <section class="v2-section" id="apply">
        <div class="v2-shell">
            <h2 class="v2-h2">{{ __('How it works') }}</h2>
            <ol class="v2-steps-list">
                @foreach($steps as [$title, $text])
                    <li><span class="v2-step-no" aria-hidden="true">{{ $loop->iteration }}</span><div><strong>{{ $title }}</strong><p>{{ $text }}</p></div></li>
                @endforeach
            </ol>
            <p class="v2-notice">{{ __('Approval is not automatic. Partner terms, including any commission, are agreed with you in writing.') }}</p>
            <div class="v2-actions"><a class="v2-btn v2-btn-red" href="{{ $applyUrl }}">{{ __('Register as partner') }}</a>@if($whatsappUrl)<a class="v2-btn v2-btn-wa" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">{{ __('Ask on WhatsApp') }}</a>@endif</div>
        </div>
    </section>
</main>
@endsection
