@extends('layouts.site')

@section('title', __('Book a free consultation'))
@section('meta_description', __('Pick a free time and book a free study consultation with Eagle Global Hub: in our Gulshan office, by phone or by video call.'))
@section('body_class', 'sg-body-v2')

@php
    $booking = session('booking');
    $modes = collect($settings['modes'])->mapWithKeys(fn (string $mode): array => [$mode => __(\App\Models\Appointment::MODES[$mode])]);
@endphp

@section('content')
<main>
    <section class="sg-page-hero v2-tool-hero" style="--sg-page-hero: url('{{ asset('images/malaysia/kuala-lumpur-day.webp') }}')" aria-labelledby="book-title">
        <div class="sg-shell">
            <nav class="sg-crumbs" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <span aria-current="page">{{ __('Book a free consultation') }}</span></nav>
            <h1 id="book-title">{{ __('Book a free consultation') }}</h1>
            <p>{{ __('Choose a free time. We confirm by email straight away, and your consultant is ready for you.') }}</p>
        </div>
    </section>

    <section class="v2-section" aria-label="{{ __('Choose a time') }}">
        <div class="v2-shell">
            @if($booking)
                <div class="v2-success v2-book-done" role="status" data-lead-success>
                    <strong>{{ __('Your consultation is booked for :when.', ['when' => $booking['when']]) }}</strong>
                    <p>{{ __('We sent the details to :email. Your reference is :reference.', ['email' => $booking['email'], 'reference' => $booking['reference']]) }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('appointments.book.store') }}" class="v2-book" novalidate data-booking>
                @csrf
                @if($errors->any())<ul class="v2-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif

                <fieldset class="v2-form-card v2-book-slots">
                    <legend class="v2-h3"><b class="v2-step-badge">1</b> {{ __('Choose a day and time') }}</legend>
                    <p class="v2-tool-note">{{ __('Times are Dhaka time. Each consultation takes :minutes minutes.', ['minutes' => $settings['slot_minutes']]) }}</p>
                    <div class="v2-book-days" role="tablist" aria-label="{{ __('Days') }}" data-booking-days hidden>
                        @foreach(array_keys($free) as $date)
                            <button type="button" role="tab" class="v2-book-day" data-booking-day="{{ $date }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                <span>{{ \Carbon\Carbon::parse($date)->translatedFormat('D') }}</span><strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('j M') }}</strong>
                            </button>
                        @endforeach
                    </div>
                    @foreach($free as $date => $times)
                        <div class="v2-book-times" data-booking-times="{{ $date }}" role="group" aria-label="{{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F') }}">
                            <p class="v2-book-date">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F') }}</p>
                            @foreach($times as $time)
                                <label class="v2-book-time"><input type="radio" name="slot" value="{{ $date }} {{ $time }}" required @checked(old('slot') === $date.' '.$time)><span>{{ \Carbon\Carbon::parse($date.' '.$time)->translatedFormat('g:i A') }}</span></label>
                            @endforeach
                        </div>
                    @endforeach
                </fieldset>

                <div class="v2-form-card v2-form v2-book-details">
                    <h2 class="v2-h3"><b class="v2-step-badge">2</b> {{ __('Your details') }}</h2>
                    <label class="v2-field" for="bk-mode">{{ __('How would you like to meet?') }}
                        <select id="bk-mode" name="mode" required>@foreach($modes as $value => $label)<option value="{{ $value }}" @selected(old('mode') === $value)>{{ $label }}</option>@endforeach</select>
                    </label>
                    @if($destinations->isNotEmpty())
                    <label class="v2-field" for="bk-destination">{{ __('Where do you want to study?') }}
                        <select id="bk-destination" name="destination"><option value="">{{ __('Not sure yet') }}</option>@foreach($destinations as $country)<option value="{{ $country->iso2 }}" @selected(old('destination') === $country->iso2)>{{ __($country->name) }}</option>@endforeach</select>
                    </label>
                    @endif
                    <label class="v2-field" for="bk-name">{{ __('Full name') }}<input id="bk-name" name="full_name" autocomplete="name" required maxlength="150" value="{{ old('full_name') }}" placeholder="{{ __('Your name') }}"></label>
                    <div class="v2-row-2">
                        <label class="v2-field" for="bk-phone">{{ __('Phone / WhatsApp') }}<input id="bk-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required maxlength="32" value="{{ old('phone') }}" placeholder="01XXXXXXXXX"></label>
                        <label class="v2-field" for="bk-email">{{ __('Email (for the confirmation)') }}<input id="bk-email" name="email" type="email" autocomplete="email" required maxlength="254" value="{{ old('email') }}" placeholder="name@example.com"></label>
                    </div>
                    <label class="v2-field" for="bk-note">{{ __('Anything we should know? (optional)') }}<textarea id="bk-note" name="note" rows="3" maxlength="1000">{{ old('note') }}</textarea></label>
                    <label class="v2-hp" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
                    <label class="v2-check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))><span>{{ __('I agree to be contacted about my study enquiry.') }} <a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a></span></label>
                    <button type="submit" class="v2-btn v2-btn-red v2-btn-block">{{ __('Book my free consultation') }}</button>
                </div>
            </form>
        </div>
    </section>
</main>
@endsection
