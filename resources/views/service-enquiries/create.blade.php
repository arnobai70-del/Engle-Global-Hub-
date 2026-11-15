@extends('layouts.site')
@section('title', __(':service enquiry', ['service' => __($service->label())]))
@section('meta_description', __('Send a :service enquiry to Eagle Global Hub LTD. Our team replies personally; nothing is booked or charged when you submit.', ['service' => __($service->label())]))
@section('body_class', 'public-page-body egho-page-body')
@section('content')
@php($slug = \App\Models\ServiceEnquiry::slugFor($service))
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero">
            <div>
                <span class="egho-eyebrow">{{ mb_strtoupper(__($service->label())) }}</span>
                <h1>{{ __(':service enquiry', ['service' => __($service->label())]) }}</h1>
                <p>{{ __('Tell us what you need and our team will contact you with options and prices. Submitting this form does not book, reserve or charge anything.') }}</p>
            </div>
        </section>
        <div class="egho-contact-grid">
            <section class="egho-contact-card" aria-labelledby="enquiry-form">
                <h2 id="enquiry-form">{{ __('Your details') }}</h2>
                @if(session('enquiry_reference'))
                    <div class="egho-result-summary" role="status"><strong>{{ __('Thank you — your enquiry has been received.') }}</strong> <span>{{ __('Your reference is') }} <strong>{{ session('enquiry_reference') }}</strong>.@auth You can follow it under <a href="{{ route('service-enquiries.mine') }}">{{ __('My Enquiries') }}</a>.@endauth</span></div>
                @endif
                @if($errors->any())<div class="egho-result-summary" role="alert"><strong>{{ __('Please correct the following:') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                <form method="POST" action="{{ route('service-enquiries.store', $slug) }}" novalidate>
                    @csrf
                    <div class="egho-form-grid">
                        <label class="egho-field"><span>{{ __('Full name') }}</span><input name="full_name" value="{{ old('full_name', auth()->user()?->name) }}" maxlength="150" autocomplete="name" required></label>
                        <label class="egho-field"><span>{{ __('Email address') }}</span><input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" maxlength="254" autocomplete="email" required></label>
                        <label class="egho-field"><span>{{ __('Phone / WhatsApp number') }}</span><input type="tel" name="phone" value="{{ old('phone') }}" maxlength="32" autocomplete="tel" placeholder="+8801XXXXXXXXX" required></label>
                        @if(isset(\App\Models\ServiceEnquiry::CATEGORIES[$service->value]))
                            <label class="egho-field"><span>{{ __('Type of request') }}</span><select name="category" required><option value="">{{ __('Select an option') }}</option>@foreach(\App\Models\ServiceEnquiry::CATEGORIES[$service->value] as $value => $label)<option value="{{ $value }}" @selected(old('category', request('category')) === $value)>{{ __($label) }}</option>@endforeach</select></label>
                        @endif
                        @if($service->hasDestinations())
                            <label class="egho-field"><span>{{ __('Destination country') }}</span><select name="destination_country_id" required><option value="">{{ __('Select a country') }}</option>@foreach($destinations as $country)<option value="{{ $country->id }}" @selected((string) old('destination_country_id') === (string) $country->id)>{{ $country->name }}</option>@endforeach</select></label>
                        @else
                            <label class="egho-field"><span>{{ __('Route or destination') }}</span><input name="route_or_destination" value="{{ old('route_or_destination') }}" maxlength="200" placeholder="e.g. Dhaka to Cox's Bazar"></label>
                        @endif
                        <label class="egho-field"><span>{{ __('Travel / start date') }}</span><input type="date" name="travel_date" value="{{ old('travel_date') }}" min="{{ now()->toDateString() }}"></label>
                        <label class="egho-field"><span>{{ __('Return / end date') }}</span><input type="date" name="return_date" value="{{ old('return_date') }}" min="{{ now()->toDateString() }}"></label>
                        <label class="egho-field"><span>{{ __('Number of travellers') }}</span><input type="number" name="travellers" value="{{ old('travellers', 1) }}" min="1" max="20" required></label>
                        <label class="egho-field egho-field-wide"><span>{{ __('Anything else we should know?') }}</span><textarea class="egho-textarea" name="message" maxlength="2000">{{ old('message') }}</textarea></label>
                        <label style="grid-column:1/-1;display:flex;gap:10px;align-items:flex-start"><input type="checkbox" name="consent" value="1" required style="width:18px;height:18px;margin-top:2px;flex:none" @checked(old('consent'))><span>{{ __('I agree that Eagle Global Hub LTD may contact me about this enquiry.') }}</span></label>
                        <div hidden aria-hidden="true"><label>{{ __('Website') }} <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                    </div>
                    <div class="egho-form-foot"><button type="submit" class="egho-btn egho-btn-primary">{{ __('Send enquiry') }}</button><small>{{ __('Please do not include passport or card numbers.') }}</small></div>
                </form>
            </section>
            <section class="egho-contact-card" aria-labelledby="other-services">
                <h2 id="other-services">{{ __('Other services') }}</h2>
                <ul>
                    @foreach($services as $other)
                        @if($other !== $service)<li><a href="{{ route('service-enquiries.create', \App\Models\ServiceEnquiry::slugFor($other)) }}">{{ $other->label() }}</a></li>@endif
                    @endforeach
                    @feature('student-consultancy')<li><a href="{{ route('consultancy.singapore') }}">{{ __('Student Visa — Study in Singapore') }}</a></li>@endfeature
                </ul>
            </section>
        </div>
    </div>
</main>
@endsection
