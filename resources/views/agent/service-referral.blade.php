@extends('layouts.site')
@section('title', __('Refer an Enquiry'))
@section('robots', 'noindex,nofollow')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-card">
            <h1>{{ __('Refer a travel or business enquiry') }}</h1>
            @if($errors->any())<div class="egho-result-summary" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="POST" action="{{ route('agent.service-referrals.store') }}">@csrf
                <div class="egho-form-grid">
                    <label class="egho-field"><span>{{ __('Service') }}</span><select name="service" required>@foreach($services as $service)<option value="{{ $service->value }}" @selected(old('service') === $service->value)>{{ __($service->label()) }}</option>@endforeach</select></label>
                    <label class="egho-field"><span>{{ __('Type of request (if applicable)') }}</span><select name="category"><option value="">{{ __('Not applicable') }}</option>@foreach(\App\Models\ServiceEnquiry::CATEGORIES as $serviceKey => $categories)<optgroup label="{{ __(\App\Enums\BusinessService::from($serviceKey)->label()) }}">@foreach($categories as $value => $label)<option value="{{ $value }}" @selected(old('category') === $value)>{{ __($label) }}</option>@endforeach</optgroup>@endforeach</select></label>
                    <label class="egho-field"><span>{{ __('Client full name') }}</span><input name="full_name" value="{{ old('full_name') }}" maxlength="150" required></label>
                    <label class="egho-field"><span>{{ __('Client email') }}</span><input type="email" name="email" value="{{ old('email') }}" maxlength="254" required></label>
                    <label class="egho-field"><span>{{ __('Client phone') }}</span><input type="tel" name="phone" value="{{ old('phone') }}" maxlength="32" required></label>
                    <label class="egho-field"><span>{{ __('Route or destination') }}</span><input name="route_or_destination" value="{{ old('route_or_destination') }}" maxlength="200"></label>
                    <label class="egho-field"><span>{{ __('Number of travellers') }}</span><input type="number" name="travellers" value="{{ old('travellers', 1) }}" min="1" max="20" required></label>
                    <label class="egho-field egho-field-wide"><span>{{ __('Details') }}</span><textarea class="egho-textarea" name="message" maxlength="2000">{{ old('message') }}</textarea></label>
                    <label style="grid-column:1/-1;display:flex;gap:10px;align-items:flex-start"><input type="checkbox" name="consent" value="1" required style="width:18px;height:18px;margin-top:2px;flex:none"><span>{{ __('The client has agreed that I share these details and that Eagle Global Hub LTD may contact them.') }}</span></label>
                </div>
                <div class="egho-form-foot"><button type="submit" class="egho-btn egho-btn-primary">{{ __('Submit referral') }}</button><a href="{{ route('agent.index') }}">{{ __('Cancel') }}</a></div>
            </form>
        </section>
    </div>
</main>
@endsection
