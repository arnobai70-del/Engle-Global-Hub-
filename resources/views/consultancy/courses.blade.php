@extends('layouts.site')

@section('title', __('Course finder: Study in Malaysia'))
@section('meta_description', __('Find courses at Malaysian universities and colleges by level, subject, yearly budget and intake, with fees and next intake dates.'))
@section('body_class', 'sg-body-v2')
@if(array_filter($filters) !== [] || request()->has('page'))
@section('robots', 'noindex,follow')
@endif

@section('content')
<main>
    @include('partials.tools.hero', ['id' => 'courses-title', 'title' => __('Course finder'), 'lead' => __('Courses at Malaysian universities and colleges we work with. Filter by level, subject, budget and intake, then ask us about any course.')])

    <section class="v2-section" aria-label="{{ __('Courses') }}">
        <div class="v2-shell">
            <form method="GET" action="{{ route('consultancy.malaysia.courses') }}" class="v2-card v2-finder-filters" role="search" aria-label="{{ __('Filter courses') }}">
                <label class="v2-field" for="cf-level">{{ __('Study level') }}
                    <select id="cf-level" name="level"><option value="">{{ __('Any level') }}</option>@foreach($levels as $value => $label)<option value="{{ $value }}" @selected(($filters['level'] ?? '') === $value)>{{ __($label) }}</option>@endforeach</select>
                </label>
                @if($subjects !== [])
                <label class="v2-field" for="cf-subject">{{ __('Subject') }}
                    <select id="cf-subject" name="subject"><option value="">{{ __('Any subject') }}</option>@foreach($subjects as $subject)<option value="{{ $subject }}" @selected(($filters['subject'] ?? '') === $subject)>{{ __($subject) }}</option>@endforeach</select>
                </label>
                @endif
                @if($hasFees)
                <label class="v2-field" for="cf-budget">{{ __('Yearly budget (MYR)') }}
                    <input id="cf-budget" name="budget" type="number" inputmode="numeric" min="1" step="1000" value="{{ $filters['budget'] ?? '' }}" placeholder="{{ __('Any budget') }}">
                </label>
                @endif
                @if($intakes !== [])
                <label class="v2-field" for="cf-intake">{{ __('Intake') }}
                    <select id="cf-intake" name="intake"><option value="">{{ __('Any intake') }}</option>@foreach($intakes as $value => $label)<option value="{{ $value }}" @selected(($filters['intake'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
                </label>
                @endif
                <div class="v2-finder-actions">
                    <button type="submit" class="v2-btn v2-btn-red">{{ __('Show courses') }}</button>
                    @if(array_filter($filters) !== [])<a class="v2-link" href="{{ route('consultancy.malaysia.courses') }}">{{ __('Clear filters') }}</a>@endif
                </div>
            </form>

            <p class="v2-finder-count" role="status">{{ trans_choice(':count course found|:count courses found', $courses->total(), ['count' => $courses->total()]) }}@if(! empty($filters['budget'])) · {{ __('Courses without a published fee are hidden when you set a budget.') }}@endif</p>

            @if($courses->isNotEmpty())
                <div class="v2-courses">
                    @foreach($courses as $course)
                        <article class="v2-course">
                            <div class="v2-course-top">
                                @if($course->study_level)<span class="v2-pill v2-pill-level">{{ __(\App\Models\StudentEnquiry::STUDY_LEVELS[$course->study_level] ?? $course->study_level) }}</span>@endif
                                @if($course->intakes->first())<small>{{ __('Next intake: :month', ['month' => $course->intakes->first()->starts_on->translatedFormat('M Y')]) }}</small>@endif
                            </div>
                            <h3>{{ $course->name }}</h3>
                            <p>{{ $course->institution?->name }}@if($course->subject) · {{ __($course->subject) }}@endif @if($course->duration_months) · {{ trans_choice(':count month|:count months', $course->duration_months, ['count' => $course->duration_months]) }}@endif</p>
                            <div class="v2-course-foot">
                                <strong>
                                    @if($course->tuition_fee_myr){{ __('MYR :amount per year', ['amount' => number_format($course->tuition_fee_myr)]) }}@if($rate)<small class="v2-finder-bdt">≈ {{ __('BDT :amount', ['amount' => number_format($course->tuition_fee_myr * $rate)]) }}</small>@endif
                                    @else{{ $course->tuition_note ?: __('Fees on request') }}@endif
                                </strong>
                                <a class="v2-link" href="{{ request()->fullUrlWithQuery(['course' => $course->id]) }}#ask">{{ __('Ask about this') }} →</a>
                            </div>
                            @if($course->source_url)<a class="v2-source" href="{{ $course->source_url }}" target="_blank" rel="noopener noreferrer">{{ __('Institution page') }}</a>@endif
                        </article>
                    @endforeach
                </div>
                @if($courses->hasPages())<div class="v2-pagination">{{ $courses->links() }}</div>@endif
            @else
                <div class="v2-card v2-empty"><p>{{ __('No course matches these filters. Try another level or intake, or ask us: we also work with institutions that are not listed here yet.') }}</p></div>
            @endif

            <div class="v2-finder-ask" id="ask">
                <form method="POST" action="{{ route('consultancy.malaysia.courses.enquire') }}" class="v2-form-card v2-form" novalidate>
                    @csrf
                    <h2>{{ __('Ask about a course') }}</h2>
                    <x-site.lead-success />
                    @if($errors->any())<ul class="v2-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
                    <label class="v2-field" for="cf-course">{{ __('Course') }}
                        <select id="cf-course" name="course_id"><option value="">{{ __('Not sure yet: help me choose') }}</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected((int) old('course_id', $selectedCourse) === $course->id)>{{ $course->name }} · {{ $course->institution?->name }}</option>@endforeach</select>
                    </label>
                    <div class="v2-row-2">
                        <label class="v2-field" for="cf-name">{{ __('Full name') }}<input id="cf-name" name="full_name" autocomplete="name" required maxlength="150" value="{{ old('full_name') }}" placeholder="{{ __('Your name') }}"></label>
                        <label class="v2-field" for="cf-phone">{{ __('Phone / WhatsApp') }}<input id="cf-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required maxlength="32" value="{{ old('phone') }}" placeholder="01XXXXXXXXX"></label>
                    </div>
                    <label class="v2-hp" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
                    <label class="v2-check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))><span>{{ __('I agree to be contacted about my study enquiry.') }} <a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a></span></label>
                    <button type="submit" class="v2-btn v2-btn-red v2-btn-block">{{ __('Request my free call') }}</button>
                </form>
            </div>
        </div>
    </section>
</main>
@endsection
