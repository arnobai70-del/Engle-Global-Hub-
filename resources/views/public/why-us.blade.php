@extends('layouts.site')

@section('title', __('Why Choose Eagle Global Hub'))
@section('meta_description', __('Why students and travellers choose Eagle Global Hub LTD: honest advice, one consultant for your file, private documents, clear costs and travel help in one place.'))
@section('body_class', 'sg-why-body')

@php
    $whyUs = app(\App\Services\Content\WhyUsContent::class);
    $icons = ['shield', 'chat', 'doc', 'wallet', 'plane', 'star', 'globe', 'clock'];
    $reasons = $whyUs->reasons();
    $promises = $whyUs->promises();
    $steps = $whyUs->steps();
    $faqs = app(\App\Services\Content\PageContent::class)->published(\App\Enums\ContentType::Faq)->take(6);
    $consultationUrl = route('consultancy.singapore').'#consultation';
    $services = [
        ['url' => route('consultancy.singapore'), 'icon' => 'cap', 'title' => __('Study in Singapore')],
        ['url' => route('services.show', 'student-visa'), 'icon' => 'id', 'title' => __('Student Visa')],
        ['url' => route('services.show', 'tourist-visa'), 'icon' => 'passport', 'title' => __('Tourist Visa')],
        ['url' => route('work-visa.index'), 'icon' => 'briefcase', 'title' => __('Work Visa')],
        ['url' => route('services.show', 'air-ticket'), 'icon' => 'plane', 'title' => __('Air Ticket')],
        ['url' => route('services.show', 'holiday-packages'), 'icon' => 'mountain', 'title' => __('Holiday Packages')],
    ];
@endphp

@section('content')
<main>
    <section class="sg-page-hero" style="--sg-page-hero: url('https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1800&q=80')" aria-labelledby="why-title">
        <div class="sg-shell">
            <nav class="sg-crumbs" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <span aria-current="page">{{ __('Why Us') }}</span></nav>
            <h1 id="why-title">{{ __('Why choose Eagle Global Hub') }}</h1>
            <p>{{ __('Studying abroad or planning a trip is a big decision. Here is how we work, what you can expect from us, and what we will never do.') }}</p>
            <div class="sg-hero-actions">
                <a class="sg-btn sg-btn-red" href="{{ $consultationUrl }}">{{ __('Book a free consultation') }}</a>
                <a class="sg-btn sg-btn-white" href="{{ route('support') }}">{{ __('Contact Us') }}</a>
            </div>
        </div>
    </section>

    <section class="sg-section sg-section-white" aria-labelledby="why-reasons-title">
        <div class="sg-shell">
            <div class="sg-section-head">
                <span class="sg-eyebrow">{{ __('Our difference') }}</span>
                <h2 id="why-reasons-title">{{ __('What you can expect from us') }}</h2>
            </div>
            <div class="sg-grid sg-grid-why {{ count($reasons) % 3 === 0 ? 'sg-grid-why-3' : '' }}">
                @foreach($reasons as $reason)
                    <article class="sg-card sg-why-card"><x-site.art :name="$icons[$loop->index % count($icons)]" /><h3>{{ $reason['title'] }}</h3>@if($reason['text'] !== '')<p>{{ $reason['text'] }}</p>@endif</article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="sg-section" aria-labelledby="why-promise-title">
        <div class="sg-shell sg-split sg-split-top">
            <div>
                <span class="sg-eyebrow">{{ __('Our promise') }}</span>
                <h2 id="why-promise-title">{{ __('What we will never do') }}</h2>
                <p>{{ __('Trust is earned by being straight with you, especially when the answer is not the one you hoped for.') }}</p>
                <a class="sg-btn sg-btn-ghost-red" href="{{ route('terms') }}">{{ __('Terms & Conditions') }}</a>
            </div>
            <ul class="sg-promise">
                @foreach($promises as $promise)
                    <li><x-site.art name="shield" /><span><strong>{{ $promise['title'] }}</strong>{{ $promise['text'] }}</span></li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="sg-section sg-band" aria-labelledby="why-steps-title">
        <div class="sg-shell">
            <div class="sg-section-head">
                <span class="sg-eyebrow">{{ __('How it works') }}</span>
                <h2 id="why-steps-title">{{ __('Simple steps, one team') }}</h2>
            </div>
            <ol class="sg-steps sg-steps-{{ min(5, max(3, count($steps))) }}">
                @foreach($steps as $step)
                    <li><strong>{{ $step['title'] }}</strong>{{ $step['text'] }}</li>
                @endforeach
            </ol>
            <div class="sg-band-cta"><a href="{{ $consultationUrl }}" class="sg-btn sg-btn-red">{{ __('Start with a free consultation') }}</a></div>
        </div>
    </section>

    <section class="sg-section sg-section-white" aria-labelledby="why-services-title">
        <div class="sg-shell">
            <div class="sg-section-head">
                <span class="sg-eyebrow">{{ __('Our Services') }}</span>
                <h2 id="why-services-title">{{ __('One team for study and travel') }}</h2>
            </div>
            <div class="sg-tiles sg-tiles-flat">
                @foreach($services as $service)
                    <a class="sg-tile" href="{{ $service['url'] }}"><x-site.art :name="$service['icon']" /><span>{{ $service['title'] }}</span></a>
                @endforeach
            </div>
            <div class="sg-center"><a class="sg-btn sg-btn-ghost-red" href="{{ route('travel-services.index') }}">{{ __('All travel services') }}</a></div>
        </div>
    </section>

    @if($faqs->isNotEmpty())
    <section class="sg-section" aria-labelledby="why-faq-title">
        <div class="sg-shell sg-split sg-split-top">
            <div>
                <span class="sg-eyebrow">{{ __('FAQ') }}</span>
                <h2 id="why-faq-title">{{ __('Questions we hear every day') }}</h2>
                <p>{{ __('Short, honest answers. Ask us anything else in a free consultation.') }}</p>
            </div>
            <div class="sg-faq">
                @foreach($faqs as $faq)
                    <details @if($loop->first) open @endif><summary>{{ $faq->localized('title') }}</summary><div class="sg-faq-answer"><x-content-body :text="$faq->localized('body')" /></div></details>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</main>
@endsection
