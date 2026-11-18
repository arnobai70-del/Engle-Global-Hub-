@extends('layouts.site')

@section('title', __('Eligibility checker: Study in Malaysia'))
@section('meta_description', __('Answer four questions about your results, English and study gap to see which course levels in Malaysia may fit you. Free, no sign-up.'))
@section('body_class', 'sg-body-v2')
@if($answers !== null)
@section('robots', 'noindex,follow')
@endif

@section('content')
<main>
    @include('partials.tools.hero', ['id' => 'eligibility-title', 'title' => __('Eligibility checker'), 'lead' => __('Four quick questions. We show the course levels in Malaysia that may fit your results, using the entry rules our consultants work with.')])

    <section class="v2-section" aria-label="{{ __('Your answers') }}">
        <div class="v2-shell v2-tool-grid">
            <form method="GET" action="{{ route('consultancy.malaysia.eligibility') }}#result" class="v2-form-card v2-form v2-tool-form" novalidate>
                <h2>{{ __('Your details') }}</h2>
                @if($errors->any() && ! old('lead'))<ul class="v2-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
                <label class="v2-field" for="el-qualification"><span><b class="v2-step-badge">1</b> {{ __('Highest completed qualification') }}</span>
                    <select id="el-qualification" name="qualification" required>
                        <option value="">{{ __('Choose one') }}</option>
                        @foreach($qualifications as $value => $label)<option value="{{ $value }}" @selected(($answers['qualification'] ?? request('qualification')) === $value)>{{ __($label) }}</option>@endforeach
                    </select>
                </label>
                <label class="v2-field" for="el-gpa"><span><b class="v2-step-badge">2</b> {{ __('Your result (GPA or CGPA)') }}</span>
                    <input id="el-gpa" name="gpa" type="number" inputmode="decimal" step="0.01" min="0" max="5" required value="{{ $answers['gpa'] ?? request('gpa') }}" placeholder="{{ __('e.g. 4.50') }}">
                </label>
                <label class="v2-field" for="el-english"><span><b class="v2-step-badge">3</b> {{ __('English score (IELTS band or equivalent)') }}</span>
                    <select id="el-english" name="english">
                        <option value="">{{ __('No English test yet') }}</option>
                        @foreach($englishScores as $score)<option value="{{ $score }}" @selected((string) request('english') === $score)>{{ $score }}</option>@endforeach
                    </select>
                </label>
                <label class="v2-field" for="el-gap"><span><b class="v2-step-badge">4</b> {{ __('Years since your last study') }}</span>
                    <select id="el-gap" name="gap_years" required>
                        @foreach(range(0, 10) as $years)<option value="{{ $years }}" @selected((int) ($answers['gap_years'] ?? request('gap_years', 0)) === $years)>{{ $years === 10 ? __('10 or more') : $years }}</option>@endforeach
                    </select>
                </label>
                <button type="submit" class="v2-btn v2-btn-red v2-btn-block">{{ __('Check my options') }}</button>
                <p class="v2-tool-note">{{ __('Admission is decided by each institution. This check is a guide, not an offer.') }}</p>
            </form>

            <div class="v2-tool-result" id="result" tabindex="-1">
                @if($answers === null)
                    <div class="v2-card v2-tool-placeholder">
                        <h2>{{ __('How it works') }}</h2>
                        <ol class="v2-ticks">
                            <li>{{ __('Answer the four questions.') }}</li>
                            <li>{{ __('See the course levels that may fit you.') }}</li>
                            <li>{{ __('Leave your name and phone: a consultant calls you with matching institutions.') }}</li>
                        </ol>
                    </div>
                @else
                    <h2 class="v2-h3">{{ $matches->isNotEmpty() ? __('Course levels that may fit you') : __('We could not match a course level yet') }}</h2>
                    @if($matches->isNotEmpty())
                        <ul class="v2-tool-matches">
                            @foreach($matches as $match)
                                <li class="v2-card">
                                    <strong>{{ $match->levelLabel() }}</strong>
                                    @if($match->note())<p>{{ $match->note() }}</p>@endif
                                    @if(\Illuminate\Support\Facades\Route::has('consultancy.malaysia.courses') && app(\App\Services\Content\PublicPageAvailability::class)->isAvailable('consultancy.malaysia.courses'))
                                        <a class="v2-link" href="{{ route('consultancy.malaysia.courses', ['level' => $match->study_level]) }}">{{ __('See :level courses', ['level' => $match->levelLabel()]) }} →</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>{{ __('Your answers do not meet the entry rules we have on file. A consultant can still look at your case, for example a foundation route or a course after an English test.') }}</p>
                    @endif

                    <form method="POST" action="{{ route('consultancy.malaysia.eligibility.store') }}" class="v2-form-card v2-form" novalidate>
                        @csrf
                        <input type="hidden" name="lead" value="1">
                        @foreach(['qualification', 'gpa', 'english', 'gap_years'] as $field)<input type="hidden" name="{{ $field }}" value="{{ request($field) }}">@endforeach
                        <h3>{{ __('Get a call about these options') }}</h3>
                        <x-site.lead-success />
                        @if($errors->any() && old('lead'))<ul class="v2-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
                        <label class="v2-field" for="el-name">{{ __('Full name') }}<input id="el-name" name="full_name" autocomplete="name" required maxlength="150" value="{{ old('full_name') }}" placeholder="{{ __('Your name') }}"></label>
                        <label class="v2-field" for="el-phone">{{ __('Phone / WhatsApp') }}<input id="el-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required maxlength="32" value="{{ old('phone') }}" placeholder="01XXXXXXXXX"></label>
                        <label class="v2-hp" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
                        <label class="v2-check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))><span>{{ __('I agree to be contacted about my study enquiry.') }} <a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a></span></label>
                        <button type="submit" class="v2-btn v2-btn-red v2-btn-block">{{ __('Request my free call') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
</main>
@endsection
