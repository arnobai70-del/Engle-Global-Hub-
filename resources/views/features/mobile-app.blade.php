@extends('layouts.site')

@section('title', __('Mobile App'))
@section('meta_description', __('The Eagle Global Hub mobile app: store links when published, and the secure API that powers it.'))
@section('body_class', 'public-page-body egho-page-body')

@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero">
            <div>
                <span class="egho-eyebrow">{{ __('MOBILE APP') }}</span>
                <h1>{{ __('Eagle Global Hub on your phone') }}</h1>
                <p>{{ __('Follow your study application, documents and notifications from a mobile app. The app uses the same secure account as this website.') }}</p>
            </div>
        </section>

        <nav aria-label="{{ __('Breadcrumb') }}" class="sg-breadcrumb"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <span aria-current="page">{{ __('Mobile App') }}</span></nav>

        <x-site.api-notice capability="mobile_app" :enquiry-url="route('student.applications.index')" :enquiry-label="__('Use the web portal now')" />
        <x-site.api-notice capability="push" />

        <div class="sg-grid sg-grid-2 sg-section-tight">
            <section class="sg-card sg-feature sg-feature-left" aria-labelledby="app-download">
                <span class="sg-icon"><x-site.icon name="phone" /></span>
                <h2 id="app-download">{{ __('Download') }}</h2>
                @if($googlePlayUrl || $appStoreUrl)
                    <div class="sg-cta-actions">
                        @if($googlePlayUrl)<a class="sg-btn sg-btn-navy" href="{{ $googlePlayUrl }}" target="_blank" rel="noopener noreferrer">{{ __('Google Play') }}</a>@endif
                        @if($appStoreUrl)<a class="sg-btn sg-btn-navy" href="{{ $appStoreUrl }}" target="_blank" rel="noopener noreferrer">{{ __('App Store') }}</a>@endif
                    </div>
                @else
                    <p>{{ __('No app build has been published yet, so there is nothing to download. Store links will appear here when the app is released.') }}</p>
                    <button type="button" class="sg-btn sg-btn-outline" disabled aria-disabled="true">{{ __('Download — not available yet') }}</button>
                @endif
            </section>
            <section class="sg-card sg-feature sg-feature-left" aria-labelledby="app-today">
                <span class="sg-icon sg-icon-navy"><x-site.icon name="cap" /></span>
                <h2 id="app-today">{{ __('Available today on the web') }}</h2>
                <ul class="sg-checklist">
                    <li>{{ __('Application status and timeline') }}</li>
                    <li>{{ __('Secure document upload') }}</li>
                    <li>{{ __('Messages with your consultant') }}</li>
                    <li>{{ __('Notifications') }}</li>
                </ul>
                <a class="sg-btn sg-btn-red" href="{{ route('student.applications.index') }}">{{ __('Open the student portal') }}</a>
            </section>
        </div>
    </div>
</main>
@endsection
