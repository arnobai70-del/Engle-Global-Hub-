{{--
    Footer call-to-action band, chosen by the page type (App\Services\Content\PageType).
    @var string $type  study | travel | explore | neutral | none
    @var string $consultationUrl
    @var ?string $whatsappUrl
--}}
@php
    $facts = app(\App\Services\SiteFacts::class);
    $travelWhatsapp = $facts->whatsappUrl('Hello Eagle Global Hub, I would like help with my travel plans.');
    // A study destination page names its own country (@section('study_country')) and has its own form at #apply.
    $studyCountry = trim($__env->yieldContent('study_country'));
    $exploreWhatsapp = $facts->whatsappUrl('Hello Eagle Global Hub, I am planning a trip to Bangladesh and would like help with ...');
@endphp
@switch($type)
    @case(\App\Services\Content\PageType::STUDY)
        <div class="sg-footer-cta" data-footer-cta="study">
            @if($studyCountry !== '')
            <div><h2>{{ __('Ready to study in :country?', ['country' => $studyCountry]) }}</h2><p>{{ __('Talk to our consultants about courses, admission and the student visa.') }}</p></div>
            @else
            <div><h2>{{ __('Ready to study in Malaysia?') }}</h2><p>{{ __('Talk to our consultants about courses, admission and the EMGS Student Pass.') }}</p></div>
            @endif
            <div class="sg-footer-cta-actions"><a href="{{ $studyCountry !== '' ? '#apply' : $consultationUrl }}" class="sg-btn sg-btn-red">{{ __('Free Consultation') }}</a>@if($whatsappUrl)<a href="{{ $whatsappUrl }}" class="sg-btn sg-btn-whatsapp" target="_blank" rel="noopener noreferrer">WhatsApp</a>@endif</div>
        </div>
        @break
    @case(\App\Services\Content\PageType::TRAVEL)
        <div class="sg-footer-cta" data-footer-cta="travel">
            <div><h2>{{ __('Planning a trip?') }}</h2><p>{{ __('Talk to our travel team about visas, air tickets, hotels and tours. You get options and a written quote first.') }}</p></div>
            <div class="sg-footer-cta-actions"><a href="{{ route('travel-services.index') }}" class="sg-btn sg-btn-red">{{ __('Travel & visa services') }}</a>@if($travelWhatsapp)<a href="{{ $travelWhatsapp }}" class="sg-btn sg-btn-whatsapp" target="_blank" rel="noopener noreferrer">WhatsApp</a>@endif</div>
        </div>
        @break
    @case(\App\Services\Content\PageType::EXPLORE)
        <div class="sg-footer-cta" data-footer-cta="explore">
            <div><h2>{{ __('Planning a trip to Bangladesh?') }}</h2><p>{{ __('Talk to our travel team about hotels, transport, guides and tours.') }}</p></div>
            <div class="sg-footer-cta-actions"><a href="{{ route('explore-bangladesh.index') }}#plan-trip" class="sg-btn sg-btn-red">{{ __('Plan my trip') }}</a>@if($exploreWhatsapp)<a href="{{ $exploreWhatsapp }}" class="sg-btn sg-btn-whatsapp" target="_blank" rel="noopener noreferrer">WhatsApp</a>@endif</div>
        </div>
        @break
    @case(\App\Services\Content\PageType::NEUTRAL)
        <div class="sg-footer-cta" data-footer-cta="neutral">
            <div><h2>{{ __('Talk to our team') }}</h2><p>{{ __('Studying abroad or planning a trip? One office in Gulshan, Dhaka helps with both.') }}</p></div>
            <div class="sg-footer-cta-actions"><a href="{{ $consultationUrl }}" class="sg-btn sg-btn-red">{{ __('Study abroad') }}</a><a href="{{ route('travel-services.index') }}" class="sg-btn sg-btn-white">{{ __('Travel & visa') }}</a></div>
        </div>
        @break
@endswitch
