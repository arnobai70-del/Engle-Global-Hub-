@extends('layouts.site')

@section('title', __('Study in Singapore from Bangladesh'))
@section(
    'meta_description',
    __('Free study consultation for Bangladeshi students planning to study in Singapore: course and institution shortlisting, admission applications, document preparation and Student\'s Pass guidance from Eagle Global Hub LTD.')
)
@section('body_class', 'public-page-body egho-page-body')
@if(app()->getLocale() === 'bn')
@section('canonical', route('consultancy.singapore', ['lang' => 'bn']))
@endif

@push('head')
    @foreach(array_keys(config('localization.supported')) as $hreflang)
        <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $hreflang === 'en' ? route('consultancy.singapore') : route('consultancy.singapore', ['lang' => $hreflang]) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ route('consultancy.singapore') }}">
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => 'Singapore Student Consultancy',
        'serviceType' => 'Study abroad consultancy',
        'areaServed' => 'Bangladesh',
        'provider' => ['@type' => 'Organization', 'name' => 'Eagle Global Hub LTD', 'url' => url('/')],
        'url' => route('consultancy.singapore'),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@php($singaporeGuides = app(\App\Services\Content\PageContent::class)->published(\App\Enums\ContentType::Guide))
    <main class="egho-page">
        <div class="egho-shell">

            <section class="egho-contact-hero">
                <div>
                    <span class="egho-eyebrow">{{ __('SINGAPORE STUDENT CONSULTANCY') }}</span>
                    <h1>{{ __('Study in Singapore from Bangladesh') }}</h1>
                    <p>
                        {{ __('Eagle Global Hub LTD guides Bangladeshi students through every step of studying in Singapore: choosing a course and institution, preparing admission documents, and getting ready for the Student\'s Pass application and arrival.') }}
                    </p>
                </div>
            </section>

            <div class="egho-contact-grid">

                <section class="egho-contact-card" aria-labelledby="consultancy-steps">
                    <h2 id="consultancy-steps">{{ __('How we support you') }}</h2>
                    <ol>
                        <li>{{ __('Free consultation and eligibility review') }}</li>
                        <li>{{ __('Course, institution and intake selection') }}</li>
                        <li>{{ __('Admission application and document checklist') }}</li>
                        <li>{{ __('Offer-letter follow-up') }}</li>
                        <li>{{ __('Student\'s Pass preparation and application tracking') }}</li>
                        <li>{{ __('Air ticket, accommodation and pre-departure assistance') }}</li>
                        <li>{{ __('Arrival and post-arrival support') }}</li>
                    </ol>
                    @if($singaporeGuides->isNotEmpty())
                        <h3>{{ __('Singapore study guides') }}</h3>
                        <ul>@foreach($singaporeGuides as $guide)<li><a href="{{ $guide->publicUrl() }}">{{ $guide->localized('title') }}</a></li>@endforeach</ul>
                    @endif
                    <p class="egho-sample-note">
                        {{ __('Admission decisions are made by each institution. Student\'s Pass decisions are made by Singapore\'s Immigration & Checkpoints Authority (ICA). We help you prepare and track your application; we cannot guarantee admission or a Student\'s Pass.') }}
                    </p>
                </section>

                <section class="egho-contact-card" aria-labelledby="consultancy-form">
                    <h2 id="consultancy-form">{{ __('Request a free consultation') }}</h2>

                    @if (session('enquiry_reference'))
                        <div class="egho-result-summary" role="status">
                            <strong>{{ __('Thank you — your enquiry has been received.') }}</strong>
                            <span>{{ __('Your reference is :reference. A consultant will contact you using the details you provided.', ['reference' => session('enquiry_reference')]) }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="egho-result-summary" role="alert">
                            <strong>{{ __('Please correct the following:') }}</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('consultancy.enquiries.store') }}" novalidate>
                        @csrf
                        <div class="egho-form-grid">
                            <label class="egho-field">
                                <span>{{ __('Full name') }}</span>
                                <input type="text" name="full_name" value="{{ old('full_name') }}" maxlength="150" autocomplete="name" required>
                            </label>

                            <label class="egho-field">
                                <span>{{ __('Email address') }}</span>
                                <input type="email" name="email" value="{{ old('email') }}" maxlength="254" autocomplete="email" required>
                            </label>

                            <label class="egho-field">
                                <span>{{ __('Phone / WhatsApp number') }}</span>
                                <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="32" autocomplete="tel" placeholder="+8801XXXXXXXXX" required>
                            </label>

                            <label class="egho-field">
                                <span>{{ __('Study destination') }}</span>
                                <select name="destination" required>
                                    @foreach ($destinations as $destination)
                                        <option value="{{ $destination->iso2 }}" @selected(old('destination', 'SG') === $destination->iso2)>{{ $destination->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="egho-field">
                                <span>{{ __('Planned study level') }}</span>
                                <select name="study_level">
                                    <option value="">{{ __('Select a level') }}</option>
                                    @foreach ($studyLevels as $value => $label)
                                        <option value="{{ $value }}" @selected(old('study_level') === $value)>{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="egho-field">
                                <span>{{ __('Highest qualification') }}</span>
                                <input type="text" name="highest_qualification" value="{{ old('highest_qualification') }}" maxlength="120" placeholder="{{ __('e.g. HSC 2025, A Level') }}">
                            </label>

                            <label class="egho-field">
                                <span>{{ __('Preferred intake') }}</span>
                                <input type="text" name="preferred_intake" value="{{ old('preferred_intake') }}" maxlength="40" placeholder="{{ __('e.g. January 2027') }}">
                            </label>

                            <label class="egho-field">
                                <span>{{ __('How did you hear about us?') }}</span>
                                <select name="lead_source">
                                    <option value="">{{ __('Select an option') }}</option>
                                    @foreach ($leadSources as $value => $label)
                                        <option value="{{ $value }}" @selected(old('lead_source') === $value)>{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="egho-field egho-field-wide">
                                <span>{{ __('Your question (optional)') }}</span>
                                <textarea class="egho-textarea" name="message" maxlength="2000">{{ old('message') }}</textarea>
                            </label>

                            <label style="grid-column:1/-1;display:flex;gap:10px;align-items:flex-start">
                                <input type="checkbox" name="wants_consultation" value="1" style="width:18px;height:18px;margin-top:2px;flex:none" @checked(old('wants_consultation'))>
                                <span>{{ __('I would like to book a free consultation') }}</span>
                            </label>

                            <label style="grid-column:1/-1;display:flex;gap:10px;align-items:flex-start">
                                <input type="checkbox" name="consent" value="1" required style="width:18px;height:18px;margin-top:2px;flex:none" @checked(old('consent'))>
                                <span>{{ __('I agree that Eagle Global Hub LTD may contact me about this study enquiry.') }}</span>
                            </label>

                            <div hidden aria-hidden="true">
                                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                            </div>
                        </div>

                        <div class="egho-form-foot">
                            <button type="submit" class="egho-btn egho-btn-primary">{{ __('Send enquiry') }}</button>
                            <small>{{ __('Please do not include passport or other identity document numbers in this form.') }}</small>
                        </div>
                    </form>
                </section>

            </div>
        </div>
    </main>
@endsection
