@extends('layouts.site')
@section('title', __('Egabe B2B Partner Registration'))
@section('robots', 'noindex,follow')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero"><div><span class="egho-eyebrow">EGABE B2B</span><h1>{{ __('Become a business partner') }}</h1><p>{{ __('Travel agencies, education partners and companies can refer customers and follow their progress. Applications are reviewed by our team before access is given.') }}</p></div></section>
        <section class="egho-contact-card">
            @if(session('status'))<div class="egho-result-summary" role="status"><strong>{{ session('status') }}</strong></div>@endif
            @if($errors->any())<div class="egho-result-summary" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @if($existing)
                <p>{{ __('Your application status') }}: <strong>{{ __(ucfirst($existing->status)) }}</strong>.@if($existing->status === 'active') <a href="{{ route('agent.index') }}">{{ __('Open the partner portal') }}</a>@endif</p>
            @else
                <form method="POST" action="{{ route('b2b.register.store') }}">@csrf
                    <div class="egho-form-grid">
                        <label class="egho-field"><span>{{ __('Company name') }}</span><input name="company_name" value="{{ old('company_name') }}" maxlength="150" required></label>
                        <label class="egho-field"><span>{{ __('Contact person') }}</span><input name="name" value="{{ old('name', auth()->user()->name) }}" maxlength="150" required></label>
                        <label class="egho-field"><span>{{ __('Phone / WhatsApp number') }}</span><input type="tel" name="phone" value="{{ old('phone') }}" maxlength="32" required></label>
                        <label class="egho-field"><span>{{ __('Trade licence / registration number (optional)') }}</span><input name="registration_number" value="{{ old('registration_number') }}" maxlength="100"></label>
                        <label class="egho-field"><span>{{ __('Website (optional)') }}</span><input type="url" name="website_url" value="{{ old('website_url') }}" maxlength="2048" placeholder="https://"></label>
                        <label class="egho-field egho-field-wide"><span>{{ __('About your business (optional)') }}</span><textarea class="egho-textarea" name="notes" maxlength="2000">{{ old('notes') }}</textarea></label>
                        <label style="grid-column:1/-1;display:flex;gap:10px;align-items:flex-start"><input type="checkbox" name="consent" value="1" required style="width:18px;height:18px;margin-top:2px;flex:none"><span>{{ __('I confirm these details are correct and agree to be contacted about the partnership.') }}</span></label>
                    </div>
                    <div class="egho-form-foot"><button type="submit" class="egho-btn egho-btn-primary">{{ __('Apply') }}</button><small>{{ __('Your sign-in email') }}: {{ auth()->user()->email }}</small></div>
                </form>
            @endif
        </section>
    </div>
</main>
@endsection
