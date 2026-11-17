@props(['source' => 'home', 'title' => null, 'subtitle' => null, 'anchor' => 'consultation-form', 'qualification' => false])
@php
    $facts = app(\App\Services\SiteFacts::class);
    $levels = \App\Models\StudentEnquiry::STUDY_LEVELS;
    $intakeOptions = collect(range(0, 5))->map(fn (int $offset): string => now()->startOfMonth()->addMonths(3 * $offset + 1)->translatedFormat('M Y'))->unique()->values();
@endphp
<div {{ $attributes->class(['v2-form-card']) }} id="{{ $anchor }}">
    <form method="POST" action="{{ route('leads.consultation') }}" class="v2-form" novalidate>
        @csrf
        <input type="hidden" name="form" value="{{ $source }}">
        <input type="hidden" name="return_anchor" value="{{ $anchor }}">
        <h2>{{ $title ?? __('Free consultation') }}</h2>
        <p>{{ $subtitle ?? ($facts->get('reply_time') ? __('A consultant calls you back: :time.', ['time' => $facts->get('reply_time')]) : __('A consultant calls you back to discuss your options.')) }}</p>
        <x-site.lead-success />
        @if($errors->any() && old('form') === $source)<ul class="v2-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
        <label class="v2-field" for="{{ $anchor }}-name">{{ __('Full name') }}<input id="{{ $anchor }}-name" name="full_name" autocomplete="name" required maxlength="150" value="{{ old('form') === $source ? old('full_name') : '' }}" placeholder="{{ __('Your name') }}"></label>
        <label class="v2-field" for="{{ $anchor }}-phone">{{ __('Phone / WhatsApp') }}<input id="{{ $anchor }}-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required maxlength="32" value="{{ old('form') === $source ? old('phone') : '' }}" placeholder="01XXXXXXXXX"></label>
        <div class="v2-row-2">
            <label class="v2-field" for="{{ $anchor }}-level">{{ __('Study level') }}<select id="{{ $anchor }}-level" name="study_level">@foreach($levels as $value => $label)<option value="{{ $value }}" @selected((old('form') === $source ? old('study_level') : 'diploma') === $value)>{{ __($label) }}</option>@endforeach</select></label>
            <label class="v2-field" for="{{ $anchor }}-intake">{{ __('Intake') }}<select id="{{ $anchor }}-intake" name="preferred_intake"><option value="">{{ __('Not sure yet') }}</option>@foreach($intakeOptions as $intake)<option value="{{ $intake }}" @selected(old('preferred_intake') === $intake)>{{ $intake }}</option>@endforeach</select></label>
        </div>
        @if($qualification)
        <label class="v2-field" for="{{ $anchor }}-qualification">{{ __('Highest qualification') }}<input id="{{ $anchor }}-qualification" name="highest_qualification" maxlength="120" value="{{ old('form') === $source ? old('highest_qualification') : '' }}" placeholder="{{ __('e.g. HSC 2025, GPA 4.5') }}"></label>
        @endif
        <label class="v2-hp" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
        <label class="v2-check"><input type="checkbox" name="consent" value="1" required @checked(old('form') === $source && old('consent'))><span>{{ __('I agree to be contacted about my study enquiry.') }} <a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a></span></label>
        <button type="submit" class="v2-btn v2-btn-red v2-btn-block">{{ __('Request my free call') }}</button>
    </form>
</div>
