@props(['service', 'serviceTitle' => '', 'topicLabel' => null, 'topics' => [], 'anchor' => 'quote-form'])
@php($whatsappUrl = app(\App\Services\SiteFacts::class)->whatsappUrl(__('Hello, I would like a quote for :service.', ['service' => $serviceTitle])))
<div {{ $attributes->class(['v2-form-card']) }} id="{{ $anchor }}">
    <form method="POST" action="{{ route('leads.quote', $service) }}" class="v2-form" novalidate>
        @csrf
        <input type="hidden" name="return_anchor" value="{{ $anchor }}">
        <h2>{{ __('Get a quote') }}</h2>
        <x-site.lead-success />
        @if($errors->any())<ul class="v2-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
        <label class="v2-field" for="{{ $anchor }}-topic">{{ $topicLabel ?? __('Destination or topic') }}
            @if($topics !== [])
                <select id="{{ $anchor }}-topic" name="route_or_destination"><option value="">{{ __('Choose one') }}</option>@foreach($topics as $topic)<option value="{{ $topic }}" @selected(old('route_or_destination', request()->query('country')) === $topic)>{{ $topic }}</option>@endforeach</select>
            @else
                <input id="{{ $anchor }}-topic" name="route_or_destination" maxlength="200" value="{{ old('route_or_destination', is_string(request()->query('country')) ? request()->query('country') : '') }}">
            @endif
        </label>
        <label class="v2-field" for="{{ $anchor }}-month">{{ __('Travel month') }}<input id="{{ $anchor }}-month" type="month" name="travel_month" min="{{ now()->format('Y-m') }}" value="{{ old('travel_month') }}"></label>
        <label class="v2-field" for="{{ $anchor }}-name">{{ __('Full name') }}<input id="{{ $anchor }}-name" name="full_name" autocomplete="name" required maxlength="150" value="{{ old('full_name') }}"></label>
        <label class="v2-field" for="{{ $anchor }}-phone">{{ __('Phone / WhatsApp') }}<input id="{{ $anchor }}-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required maxlength="32" value="{{ old('phone') }}" placeholder="{{ $service === 'student-visa' ? '01XXXXXXXXX' : '+44 7700 900123' }}" @if($service !== 'student-visa') aria-describedby="{{ $anchor }}-phone-hint" @endif>@if($service !== 'student-visa')<small class="v2-field-hint" id="{{ $anchor }}-phone-hint">{{ __('Include the country code if your number is not from Bangladesh.') }}</small>@endif</label>
        <label class="v2-hp" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
        <label class="v2-check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))><span>{{ __('I agree to be contacted about my enquiry.') }} <a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a></span></label>
        <button type="submit" class="v2-btn v2-btn-red v2-btn-block">{{ __('Send enquiry') }}</button>
        @if($whatsappUrl)<a class="v2-btn v2-btn-wa v2-btn-block" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">{{ __('Or ask on WhatsApp') }}</a>@endif
        <p class="v2-form-note">{{ __('Free. Nothing is booked or charged.') }}</p>
    </form>
</div>
