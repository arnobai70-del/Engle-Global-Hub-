@extends('layouts.site')

@section('title', __('Study in Singapore from Bangladesh'))
@section(
    'meta_description',
    __('Free study consultation for Bangladeshi students planning to study in Singapore: course and institution shortlisting, admission applications, document preparation and Student\'s Pass guidance from Eagle Global Hub LTD.')
)
@section('body_class', 'public-page-body egho-page-body')

@push('head')
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
@php
    $singaporeGuides = app(\App\Services\Content\PageContent::class)->published(\App\Enums\ContentType::Guide);
    $guideUrl = static fn (string $slug): ?string => $singaporeGuides->firstWhere('slug', $slug)?->publicUrl();
    $img = static fn (string $id, int $width = 900): string => 'https://images.unsplash.com/photo-'.$id.'?auto=format&fit=crop&w='.$width.'&q=80';
    $guideImages = ['1496939376851-89342e90adcd', '1508964942454-1a56651d54ac', '1525625293386-3f8f99389edd', '1541339907198-e08756dedf3f', '1565967511849-76a60a516170'];
    $tile = static fn (string $path): string => '<svg viewBox="0 0 24 24" aria-hidden="true">'.$path.'</svg>';
    $tiles = [
        ['title' => __('Admission Information'), 'text' => __('Entry requirements, process and timelines'), 'href' => $guideUrl('university-admission') ?? '#guides', 'icon' => '<path d="M3 21h18M5 21V10l7-5 7 5v11"/><path d="M9 21v-6h6v6"/>'],
        ['title' => __('Institutions'), 'text' => __('Polytechnics, universities and colleges'), 'href' => route('consultancy.institutions.index'), 'icon' => '<path d="M3 21h18M4 10h16M12 3 3 8h18zM6 10v8M10 10v8M14 10v8M18 10v8"/>'],
        ['title' => __('Courses & Intakes'), 'text' => __('Available courses and intake periods'), 'href' => $guideUrl('course-selection') ?? '#consultation', 'icon' => '<path d="M10.5 13.5 3 11l1.5-1.5 7.5.8 4.5-4.8a2 2 0 0 1 2.8 2.8L14.5 12.8l.8 7.5L13.8 22l-2.5-7.5"/>'],
        ['title' => __('Student\'s Pass (ICA)'), 'text' => __('Application, approval and formalities'), 'href' => $guideUrl('students-pass-guidance') ?? '#guides', 'icon' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="11" r="2"/><path d="M5.5 16c.6-1.6 1.7-2.4 3-2.4s2.4.8 3 2.4M14 10h4M14 13.5h4"/>'],
        ['title' => __('Living in Singapore'), 'text' => __('Accommodation, cost of living and daily life'), 'href' => $guideUrl('student-accommodation') ?? $guideUrl('tuition-fees') ?? '#consultation', 'icon' => '<path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/>'],
        ['title' => __('Singapore Guides'), 'text' => __('Step-by-step, official-source guides'), 'href' => route('consultancy.guides.index'), 'icon' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>'],
    ];
@endphp
    <main class="egho-page">
        <div class="egho-shell">

            <section class="egho-contact-hero">
                <div>
                    <span class="egho-eyebrow">{{ __('SINGAPORE STUDENT CONSULTANCY') }}</span>
                    <h1>{{ __('Study in Singapore from Bangladesh') }}</h1>
                    <p>
                        {{ __('Eagle Global Hub LTD guides Bangladeshi students through every step of studying in Singapore: choosing a course and institution, preparing admission documents, and getting ready for the Student\'s Pass application and arrival.') }}
                    </p>
                    <p><a href="#consultation" class="sg-btn sg-btn-red">{{ __('Request a free consultation') }}</a></p>
                </div>
            </section>

            <section class="sg-study-tiles" aria-label="{{ __('Study in Singapore') }}">
                @foreach($tiles as $item)
                    <a class="sg-card sg-feature" href="{{ $item['href'] }}"><span class="sg-icon">{!! $tile($item['icon']) !!}</span><h3>{{ $item['title'] }}</h3><p>{{ $item['text'] }}</p></a>
                @endforeach
            </section>

            <section class="sg-section-tight">
                <div class="sg-card sg-why" style="padding:clamp(1.25rem,3vw,2rem)">
                    <img src="{{ $img('1541339907198-e08756dedf3f', 1000) }}" alt="{{ __('Graduates celebrating in Singapore') }}" width="1000" height="750" loading="lazy" decoding="async">
                    <div>
                        <span class="sg-eyebrow">{{ __('Why Singapore?') }}</span>
                        <h2>{{ __('How we support you') }}</h2>
                        <ul class="sg-checklist">
                            <li>{{ __('Free consultation and eligibility review') }}</li>
                            <li>{{ __('Course, institution and intake selection') }}</li>
                            <li>{{ __('Admission application and document checklist') }}</li>
                            <li>{{ __('Offer-letter follow-up') }}</li>
                            <li>{{ __('Student\'s Pass preparation and application tracking') }}</li>
                            <li>{{ __('Air ticket, accommodation and pre-departure assistance') }}</li>
                            <li>{{ __('Arrival and post-arrival support') }}</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="sg-section-tight" id="guides" aria-labelledby="guides-title">
                <div class="sg-section-head sg-section-head-left">
                    <h2 id="guides-title">{{ __('Singapore study guides') }}</h2>
                    <p>{{ __('Checked against official Singapore sources such as ICA and MOE.') }}</p>
                </div>
                @if($singaporeGuides->isNotEmpty())
                    <div class="sg-guide-list">
                        @foreach($singaporeGuides as $guide)
                            <a class="sg-card sg-guide" href="{{ $guide->publicUrl() }}">
                                <img src="{{ $img($guideImages[$loop->index % count($guideImages)], 300) }}" alt="" width="96" height="68" loading="lazy" decoding="async">
                                <span><strong>{{ $guide->localized('title') }}</strong><span>{{ \Illuminate\Support\Str::limit((string) $guide->localized('summary'), 110) }}</span></span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="sg-card" style="padding:1.25rem 1.5rem"><p style="margin:0">{{ __('Guides are being reviewed against official sources and will be published soon. Ask our consultants in the meantime.') }}</p></div>
                @endif
            </section>

            <div class="sg-consult sg-section-tight">
                <section class="egho-contact-card" aria-labelledby="consultancy-steps">
                    <h2 id="consultancy-steps">{{ __('Your journey to Singapore, step by step') }}</h2>
                    <ol>
                        <li>{{ __('Free consultation and eligibility review') }}</li>
                        <li>{{ __('Course, institution and intake selection') }}</li>
                        <li>{{ __('Admission application and document checklist') }}</li>
                        <li>{{ __('Student\'s Pass preparation and application tracking') }}</li>
                        <li>{{ __('Arrival and post-arrival support') }}</li>
                    </ol>
                    <p class="egho-sample-note">
                        {{ __('Admission decisions are made by each institution. Student\'s Pass decisions are made by Singapore\'s Immigration & Checkpoints Authority (ICA). We help you prepare and track your application; we cannot guarantee admission or a Student\'s Pass.') }}
                    </p>
                </section>
                <section class="egho-contact-card" id="consultation" aria-labelledby="consultancy-form">
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
                                        <option value="{{ $destination->iso2 }}" @selected(old('destination', strtoupper((string) request('destination', 'SG'))) === $destination->iso2)>{{ __($destination->name) }}</option>
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
