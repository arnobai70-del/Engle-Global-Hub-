@extends('layouts.site')
@section('title', 'Refer a Student')
@section('robots', 'noindex,nofollow')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<x-site.portal active="agent-referral" kind="agent">
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-card">
            <h1>{{ __('Refer a student') }}</h1>
            @if($errors->any())<div class="egho-result-summary" role="alert"><strong>{{ __('Please correct the following:') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="POST" action="{{ route('agent.referrals.store') }}" novalidate>
                @csrf
                <div class="egho-form-grid">
                    <label class="egho-field"><span>{{ __('Student full name') }}</span><input name="full_name" value="{{ old('full_name') }}" maxlength="150" required></label>
                    <label class="egho-field"><span>{{ __('Student email') }}</span><input type="email" name="email" value="{{ old('email') }}" maxlength="254" required></label>
                    <label class="egho-field"><span>{{ __('Student phone') }}</span><input type="tel" name="phone" value="{{ old('phone') }}" maxlength="32" required></label>
                    <label class="egho-field"><span>{{ __('Study destination') }}</span><select name="destination" required>@foreach($destinations as $destination)<option value="{{ $destination->iso2 }}" @selected(old('destination', 'SG') === $destination->iso2)>{{ __($destination->name) }}</option>@endforeach</select></label>
                    <label class="egho-field"><span>{{ __('Study level') }}</span><select name="study_level"><option value="">{{ __('Select a level') }}</option>@foreach($studyLevels as $value => $label)<option value="{{ $value }}" @selected(old('study_level') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label class="egho-field"><span>{{ __('Preferred intake') }}</span><input name="preferred_intake" value="{{ old('preferred_intake') }}" maxlength="40"></label>
                    <label class="egho-field"><span>{{ __('Highest qualification') }}</span><input name="highest_qualification" value="{{ old('highest_qualification') }}" maxlength="120"></label>
                    <label class="egho-field egho-field-wide"><span>{{ __('Notes for our consultants') }}</span><textarea class="egho-textarea" name="message" maxlength="2000">{{ old('message') }}</textarea></label>
                    <label style="grid-column:1/-1;display:flex;gap:10px;align-items:flex-start"><input type="checkbox" name="consent" value="1" required style="width:18px;height:18px;margin-top:2px;flex:none"><span>{{ __('The student has agreed that I share these details and that Eagle Global Hub LTD may contact them.') }}</span></label>
                </div>
                <div class="egho-form-foot"><button type="submit" class="egho-btn egho-btn-primary">{{ __('Submit referral') }}</button><a href="{{ route('agent.index') }}">{{ __('Cancel') }}</a></div>
            </form>
        </section>
    </div>
</main>
</x-site.portal>
@endsection
