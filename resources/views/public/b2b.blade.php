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
                        <a class="v2-btn v2-btn-red" href="{{ $applyUrl }}">{{ __('Apply as partner') }}</a>
                        @if($whatsappUrl)<a class="v2-btn v2-btn-wa" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">{{ __('Ask on WhatsApp') }}</a>@endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="v2-section">
        <div class="v2-shell">
            <h2 class="v2-h2">{{ __('How it works') }}</h2>
            <ol class="v2-steps-list">
                @foreach($steps as [$title, $text])
                    <li><span class="v2-step-no" aria-hidden="true">{{ $loop->iteration }}</span><div><strong>{{ $title }}</strong><p>{{ $text }}</p></div></li>
                @endforeach
            </ol>
            <p class="v2-notice">{{ __('Approval is not automatic. Partner terms, including any commission, are agreed with you in writing.') }}</p>
        </div>
    </section>
</main>
@endsection
