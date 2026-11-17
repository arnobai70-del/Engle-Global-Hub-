<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $siteName=$siteName??'Eagle Global Hub LTD';$siteTagline=$siteTagline??'Travel & Visa Services';$siteContact=$siteContact??[];$siteSocial=$siteSocial??[];$footerLinks=$footerLinks??[];
        $seoOverrides=app(\App\Services\SeoMetadata::class)->forRoute(request()->route()?->getName());$pageTitle=$seoOverrides['title']??trim($__env->yieldContent('title','Travel'));$pageDescription=$seoOverrides['description']??trim($__env->yieldContent('meta_description','Travel and booking services from '.$siteName.'.'));$canonical=trim($__env->yieldContent('canonical',url()->current()));$robots=trim($__env->yieldContent('robots','index,follow'));$ogTitle=trim($__env->yieldContent('og_title',$pageTitle));$ogDescription=trim($__env->yieldContent('og_description',$pageDescription));$ogImage=trim($__env->yieldContent('og_image',''));$twitterCard=trim($__env->yieldContent('twitter_card','summary_large_image'));$fullTitle=request()->routeIs('home')?$pageTitle:$pageTitle.' | '.$siteName;
        $decodeSection=static fn(string $value):string=>html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8');$pageTitle=$decodeSection($pageTitle);$pageDescription=$decodeSection($pageDescription);$canonical=$decodeSection($canonical);$ogTitle=$decodeSection(trim($__env->yieldContent('og_title')))?:$pageTitle;$ogDescription=$decodeSection(trim($__env->yieldContent('og_description')))?:$pageDescription;$ogImage=$decodeSection($ogImage);$twitterCard=$decodeSection($twitterCard);$fullTitle=request()->routeIs('home')?$pageTitle:$pageTitle.' | '.$siteName;
        $bilingualRoutes=['home','consultancy.malaysia','consultancy.malaysia.guides.index','consultancy.malaysia.institutions.index','consultancy.singapore','consultancy.guides.index','consultancy.institutions.index','travel-services.index','visa.index','work-visa.index','hotels.index','tours.index','support','terms','about','explore-bangladesh.index','offers.index','service-enquiries.create'];$hreflangBase=in_array(request()->route()?->getName(),$bilingualRoutes,true)&&str_starts_with($robots,'index')?(string) strtok($canonical,'?'):null;if($hreflangBase!==null&&app()->getLocale()==='bn'){$canonical=$hreflangBase.'?lang=bn';}
        $themeCss=static function(string $file):string{$modifiedAt=@filemtime(public_path($file));return asset($file).($modifiedAt?'?v='.$modifiedAt:'');};
        $travelServices=$travelServices??[];
        $servicePageRouteName=static function(array $service):?string{
            $routeName=$service['page_route_name']??$service['route_name']??null;
            return is_string($routeName)&&\Illuminate\Support\Facades\Route::has($routeName)?$routeName:null;
        };
        $brandName=trim(preg_replace('/\s+LTD\.?$/i','',$siteName));
        $branding=rescue(fn()=>app(\App\Services\SettingService::class)->group('branding'),[],false);$headerTagline=app()->getLocale()==='bn'&&filled($branding['header_tagline_bn']??null)?$branding['header_tagline_bn']:(filled($branding['header_tagline']??null)?$branding['header_tagline']:__('Your Gateway to Study in Malaysia'));$siteLogo=$siteLogo??asset('images/eagle-global-hub-logo.png');
        $currentLocale=app()->getLocale();
        $routeName=(string) request()->route()?->getName();
        $flightsUrl=auth()->check()?(auth()->user()->can('flights.search')?route('flights.index'):route('dashboard')):route('login');
        $phoneHref=data_get($siteContact,'phone')?'tel:'.preg_replace('/[^+0-9]/','',data_get($siteContact,'phone')):null;
    @endphp
    <title>{{ $fullTitle }}</title><meta name="description" content="{{ $pageDescription }}"><meta name="robots" content="{{ $robots }}"><link rel="canonical" href="{{ $canonical }}">@if($hreflangBase)<link rel="alternate" hreflang="en" href="{{ $hreflangBase }}"><link rel="alternate" hreflang="bn" href="{{ $hreflangBase }}?lang=bn"><link rel="alternate" hreflang="x-default" href="{{ $hreflangBase }}">@endif
    <meta property="og:type" content="website"><meta property="og:site_name" content="{{ $siteName }}"><meta property="og:title" content="{{ $ogTitle ?: $fullTitle }}"><meta property="og:description" content="{{ $ogDescription ?: $pageDescription }}"><meta property="og:url" content="{{ $canonical }}">@if($ogImage)<meta property="og:image" content="{{ $ogImage }}">@endif
    <meta name="twitter:card" content="{{ $twitterCard }}"><meta name="twitter:title" content="{{ $ogTitle ?: $fullTitle }}"><meta name="twitter:description" content="{{ $ogDescription ?: $pageDescription }}">@if($ogImage)<meta name="twitter:image" content="{{ $ogImage }}">@endif
    <link rel="icon" type="image/png" href="{{ $siteLogo }}">
    @vite(['resources/css/app.css','resources/js/app.js']) {{ Vite::fonts() }}
    <link rel="stylesheet" href="{{ $themeCss('css/egh-ota.css') }}"><link rel="stylesheet" href="{{ $themeCss('css/egh-workspace.css') }}"><link rel="stylesheet" href="{{ $themeCss('css/egh-chrome.css') }}"><link rel="stylesheet" href="{{ $themeCss('css/egh-home-dynamic.css') }}"><link rel="stylesheet" href="{{ $themeCss('css/egh-travel-pages.css') }}"><link rel="stylesheet" href="{{ $themeCss('css/egh-final-polish.css') }}">@stack('head')
    <link rel="preload" href="{{ asset('fonts/inter-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/poppins-700-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ $themeCss('css/egh-sg.css') }}">
    <link rel="stylesheet" href="{{ $themeCss('css/egh-v2.css') }}">
    @include('partials.analytics.meta-pixel-head')
    {{-- Travelpayouts Drive: provider-supplied project tag, included once on public site pages. --}}
    <script nowprocket data-noptimize="1" data-cfasync="false" data-wpfc-render="false" seraph-accel-crit="1" data-no-defer="1" data-cmp-ab="2">
    (function () {
        var script = document.createElement("script");
        script.async = 1;
        script.setAttribute("data-cmp-ab", "2");
        script.src = 'https://tpembars.com/NTk1NTQ0.js?t=595544';
        document.head.appendChild(script);
    })();
    </script>
</head>
<body class="site-body sg-body @yield('body_class')" data-route="{{ $routeName }}" data-sending-label="{{ __('Sending…') }}">
@include('partials.analytics.meta-pixel-noscript')
<a href="#main-content" class="site-skip-link">{{ __('Skip to content') }}</a>
@php
    $facts = app(\App\Services\SiteFacts::class);
    $pageAvailable = static fn (string $route): bool => app(\App\Services\Content\PublicPageAvailability::class)->isAvailable($route);
    $officeHours = $facts->get('office_hours') ?? data_get($siteContact, 'hours');
    $consultationUrl = route('consultancy.malaysia').'#apply';
    $whatsappUrl = $facts->whatsappUrl(__('Hello Eagle Global Hub, I would like a free consultation.'));
    $navActive = static fn (array $prefixes): bool => collect($prefixes)->contains(fn (string $prefix): bool => str_starts_with($routeName, $prefix));
@endphp
<header class="sg-site-header">
    <div class="sg-topbar">
        <div class="sg-shell sg-topbar-inner">
            <div class="sg-topbar-links">
                @if($officeHours)<span class="sg-topbar-hours">{{ $officeHours }}</span>@endif
            </div>
            <div class="sg-topbar-contact">
                @if($phoneHref)<a href="{{ $phoneHref }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>{{ data_get($siteContact,'phone') }}</a>@endif
                @if(data_get($siteContact,'email'))<a href="mailto:{{ data_get($siteContact,'email') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>{{ data_get($siteContact,'email') }}</a>@endif
                <span class="sg-locale" aria-label="{{ __('Language') }}">
                    <a class="egho-switch" href="{{ request()->fullUrlWithQuery(['lang'=>'en']) }}" hreflang="en" lang="en" {!! $currentLocale==='en'?'aria-current="true" ':'' !!}data-locale-switch>EN</a>
                    <span aria-hidden="true">|</span>
                    <a class="egho-switch" href="{{ request()->fullUrlWithQuery(['lang'=>'bn']) }}" hreflang="bn" lang="bn" {!! $currentLocale==='bn'?'aria-current="true" ':'' !!}data-locale-switch>বাংলা</a>
                </span>
            </div>
        </div>
    </div>
    <div class="sg-header">
        <div class="sg-shell sg-header-inner">
            <a href="{{ route('home') }}" class="sg-brand" aria-label="{{ $siteName }} home">
                <img class="sg-brand-logo" src="{{ $siteLogo }}" alt="{{ $siteName }} logo" width="240" height="241">
                <span class="sg-brand-copy"><strong>{{ $brandName }}</strong><small>{{ $headerTagline }}</small></span>
            </a>
            <button type="button" class="sg-burger" aria-controls="sg-primary-nav" aria-expanded="false" data-sg-burger><span class="sg-visually-hidden">{{ __('Menu') }}</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
            <nav class="sg-nav" id="sg-primary-nav" aria-label="Primary navigation" data-sg-nav>
                @php($headerMenu = app(\App\Services\Navigation\MenuService::class)->tree('header'))
                @if($headerMenu !== null)
                    @include('partials.menus.header', ['items' => $headerMenu])
                @else
                @feature('student-consultancy')
                <details class="sg-dd" @if($navActive(['consultancy.malaysia'])) data-active @endif>
                    <summary>{{ __('Study in Malaysia') }}</summary>
                    <div class="sg-dd-menu">
                        <a href="{{ route('consultancy.malaysia') }}">{{ __('Study in Malaysia') }}</a>
                        @if($pageAvailable('consultancy.malaysia.guides.index'))<a href="{{ route('consultancy.malaysia.guides.index') }}">{{ __('Malaysia Guides') }}</a>@endif
                        @if($pageAvailable('consultancy.malaysia.institutions.index'))<a href="{{ route('consultancy.malaysia.institutions.index') }}">{{ __('Institutions in Malaysia') }}</a>@endif
                        <a href="{{ $consultationUrl }}">{{ __('Free Consultation') }}</a>
                        @auth<a href="{{ route('student.applications.index') }}">{{ __('My Applications') }}</a>@endauth
                    </div>
                </details>
                <details class="sg-dd" @if($navActive(['consultancy.singapore', 'consultancy.guides.', 'consultancy.institutions.'])) data-active @endif>
                    <summary>{{ __('Study in Singapore') }}</summary>
                    <div class="sg-dd-menu">
                        <a href="{{ route('consultancy.singapore') }}">{{ __('Study in Singapore') }}</a>
                        <a href="{{ route('services.show', 'student-visa') }}">{{ __('Student Visa') }}</a>
                        @if($pageAvailable('consultancy.guides.index'))<a href="{{ route('consultancy.guides.index') }}">{{ __('Singapore Guides') }}</a>@endif
                        @if($pageAvailable('consultancy.institutions.index'))<a href="{{ route('consultancy.institutions.index') }}">{{ __('Institutions') }}</a>@endif
                        <a href="{{ route('consultancy.singapore') }}#apply">{{ __('Free Consultation') }}</a>
                    </div>
                </details>
                @endfeature
                <details class="sg-dd" @if($navActive(['visa.', 'work-visa.', 'services.', 'travel-services.', 'hotels.', 'tours.', 'flights.', 'service-enquiries.'])) data-active @endif>
                    <summary>{{ __('Visa & Travel') }}</summary>
                    <div class="sg-dd-menu">
                        @feature('visa')<a href="{{ route('services.show', 'tourist-visa') }}">{{ __('Tourist Visa') }}</a><a href="{{ route('services.show', 'business-visa') }}">{{ __('Business Visa') }}</a><a href="{{ route('work-visa.index') }}">{{ __('Work Visa') }}</a>@endfeature
                        <a href="{{ route('services.show', 'air-ticket') }}">{{ __('Air Tickets') }}</a>
                        @feature('hotels')<a href="{{ route('hotels.index') }}">{{ __('Hotels') }}</a>@endfeature
                        <a href="{{ route('services.show', 'holiday-packages') }}">{{ __('Holiday Packages') }}</a>
                        <a href="{{ route('services.show', 'group-tours') }}">{{ __('Group Tours') }}</a>
                        <a href="{{ route('services.show', 'foreigner-support') }}">{{ __('Foreigner Support') }}</a>
                        <a href="{{ route('travel-services.index') }}">{{ __('All travel services') }}</a>
                    </div>
                </details>
                <a href="{{ route('home') }}#how-it-works" class="sg-nav-link">{{ __('How it works') }}</a>
                <details class="sg-dd" @if(in_array($routeName, ['about', 'why-us', 'b2b.info'], true)) data-active @endif>
                    <summary>{{ __('About') }}</summary>
                    <div class="sg-dd-menu">
                        @feature('about')<a href="{{ route('about') }}">{{ __('About Us') }}</a>@endfeature
                        <a href="{{ route('why-us') }}">{{ __('Why Us') }}</a>
                    </div>
                </details>
                @feature('support')<a href="{{ route('support') }}" class="sg-nav-link" @if($routeName === 'support') aria-current="page" @endif>{{ __('Contact') }}</a>@endfeature
                @endif
                <div class="sg-nav-actions">
                    @auth
                        <details class="sg-dd sg-dd-account">
                            <summary><span class="sg-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><span class="sg-account-name">{{ __('My Account') }}</span></summary>
                            <div class="sg-dd-menu sg-dd-menu-end">
                                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                                @feature('account')<a href="{{ route('account.overview') }}">{{ __('My Account') }}</a>@endfeature
                                <a href="{{ route('student.applications.index') }}">{{ __('My Applications') }}</a>
                                <a href="{{ route('service-enquiries.mine') }}">{{ __('My Enquiries') }}</a>
                                <a href="{{ route('notifications.index') }}">{{ __('Notifications') }}</a>
                                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">{{ __('Logout') }}</button></form>
                            </div>
                        </details>
                    @else
                        <a href="{{ route('login') }}" class="sg-btn sg-btn-outline sg-btn-sm">{{ __('Login') }}</a>
                    @endauth
                    @php($ctaMenu = app(\App\Services\Navigation\MenuService::class)->tree('header_cta'))
                    @if($ctaMenu !== null)
                        @foreach($ctaMenu as $button)<a href="{{ $button['url'] }}" class="sg-btn sg-btn-red sg-btn-sm" @if($button['new_tab']) target="_blank" rel="noopener noreferrer" @endif>{{ $button['label'] }}</a>@endforeach
                    @else
                    <a href="{{ $consultationUrl }}" class="sg-btn sg-btn-red sg-btn-sm">{{ __('Free Consultation') }}</a>
                    @endif
                </div>
            </nav>
        </div>
    </div>
</header>
@if(app()->environment(['local','testing'])&&!request()->routeIs('home'))<div class="site-environment-banner" role="status"><strong>Development / sandbox environment:</strong> supplier availability may use fixture or sandbox data and must not be treated as live inventory.</div>@endif
@if(request()->attributes->get('super_admin_feature_preview'))<div class="site-environment-banner" role="status"><strong>Super Admin Preview:</strong> this feature is disabled or hidden for other users.</div>@endif
@if(session('api_waiting'))<div class="sg-shell"><div class="v2-notice" role="status" style="margin-top:1rem">{{ session('api_waiting') }}</div></div>@endif
<div id="main-content">@yield('content')</div>
<footer class="sg-footer v2-footer">
    <div class="sg-shell">
        @unless(request()->routeIs('home', 'consultancy.malaysia', 'consultancy.singapore'))
        <div class="sg-footer-cta">
            <div><h2>{{ __('Ready to study in Malaysia?') }}</h2><p>{{ __('Talk to our consultants about courses, admission and the EMGS Student Pass.') }}</p></div>
            <div class="sg-footer-cta-actions"><a href="{{ $consultationUrl }}" class="sg-btn sg-btn-red">{{ __('Free Consultation') }}</a>@if($whatsappUrl)<a href="{{ $whatsappUrl }}" class="sg-btn sg-btn-whatsapp" target="_blank" rel="noopener noreferrer">WhatsApp</a>@endif</div>
        </div>
        @endunless
        <div class="sg-footer-main">
            <div class="sg-footer-about">
                <a href="{{ route('home') }}" class="sg-brand sg-brand-light"><img class="sg-brand-logo" src="{{ $siteLogo }}" alt="{{ $siteName }} logo" width="240" height="241" loading="lazy" decoding="async"><span class="sg-brand-copy"><strong>{{ $siteName }}</strong></span></a>
                <p>{{ filled($siteFooterDescription ?? null) ? __($siteFooterDescription) : __('Malaysia and Singapore student consultancy, visa and travel support from Dhaka.') }}</p>
                <ul class="sg-footer-contact">@if(data_get($siteContact,'address'))<li>{{ data_get($siteContact,'address') }}</li>@endif @if($phoneHref)<li><a href="{{ $phoneHref }}">{{ data_get($siteContact,'phone') }}</a></li>@endif @if(data_get($siteContact,'email'))<li><a href="mailto:{{ data_get($siteContact,'email') }}">{{ data_get($siteContact,'email') }}</a></li>@endif</ul>
                @if($siteSocial!==[])<div class="sg-footer-social">@foreach($siteSocial as $network=>$url)<a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ucfirst($network) }}">{{ strtoupper(substr($network,0,2)) }}</a>@endforeach</div>@endif
            </div>
            @php($footerMenu = app(\App\Services\Navigation\MenuService::class)->tree('footer'))
            @if($footerMenu !== null)
                @include('partials.menus.footer', ['items' => $footerMenu])
            @else
            <div><h3>{{ __('Study') }}</h3><ul>
                @feature('student-consultancy')<li><a href="{{ route('consultancy.malaysia') }}">{{ __('Study in Malaysia') }}</a></li><li><a href="{{ route('consultancy.singapore') }}">{{ __('Study in Singapore') }}</a></li>@endfeature
                @if($pageAvailable('consultancy.institutions.index'))<li><a href="{{ route('consultancy.institutions.index') }}">{{ __('Institutions') }}</a></li>@endif
                <li><a href="{{ route('services.show', 'student-visa') }}">{{ __('Student\'s Pass') }}</a></li>
                @if($pageAvailable('consultancy.guides.index'))<li><a href="{{ route('consultancy.guides.index') }}">{{ __('Guides') }}</a></li>@endif
                @if($pageAvailable('success-stories.index'))<li><a href="{{ route('success-stories.index') }}">{{ __('Success stories') }}</a></li>@endif
            </ul></div>
            <div><h3>{{ __('Services') }}</h3><ul>
                @feature('visa')<li><a href="{{ route('visa.index') }}">{{ __('Visa services') }}</a></li>@endfeature
                <li><a href="{{ route('services.show', 'air-ticket') }}">{{ __('Air tickets') }}</a></li>
                <li><a href="{{ route('travel-services.index') }}">{{ __('Hotels & tours') }}</a></li>
                @feature('visa')<li><a href="{{ route('work-visa.index') }}">{{ __('Work visa') }}</a></li>@endfeature
            </ul></div>
            <div><h3>{{ __('Company') }}</h3><ul>
                @feature('about')<li><a href="{{ route('about') }}">{{ __('About us') }}</a></li>@endfeature
                <li><a href="{{ route('why-us') }}">{{ __('Why Us') }}</a></li>
                @feature('support')<li><a href="{{ route('support') }}">{{ __('Contact') }}</a></li>@endfeature
                <li><a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a></li>
                <li><a href="{{ route('terms') }}">{{ __('Terms & Conditions') }}</a></li>
                @foreach($footerLinks as $link)@if(!empty($link['url'])&&!empty($link['title']))<li><a href="{{ $link['url'] }}">{{ $link['title'] }}</a></li>@endif @endforeach
            </ul></div>
            <div><h3>{{ __('More from us') }}</h3><ul>
                <li><a href="{{ route('services.show', 'export-import') }}">{{ __('Export & Import') }}</a></li>
                <li><a href="{{ route('services.show', 'civil-construction') }}">{{ __('Civil & Construction Work') }}</a></li>
                <li><a href="{{ route('b2b.info') }}">{{ __('Become a partner (B2B)') }}</a></li>
                <li><a href="{{ route('search') }}">{{ __('Search') }}</a></li>
                @foreach(['explore-bangladesh.index' => 'Explore Bangladesh', 'offers.index' => 'Offers & Promotions', 'reviews.index' => 'Reviews', 'forums.index' => 'Forums', 'inspiration.index' => 'Get Inspired', 'restaurants.index' => 'Restaurants', 'rewards.index' => 'Rewards', 'map' => 'Map', 'mobile-app' => 'Mobile App'] as $moreRoute => $moreLabel)
                    @if($pageAvailable($moreRoute))<li><a href="{{ route($moreRoute) }}">{{ __($moreLabel) }}</a></li>@endif
                @endforeach
            </ul></div>
            @endif
        </div>
        <div class="sg-footer-bottom"><small>&copy; {{ now()->year }} {{ $siteName }}@if($facts->get('trade_licence_no')) · {{ __('Trade licence :number', ['number' => $facts->get('trade_licence_no')]) }}@endif</small><small><a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a> · <a href="{{ route('terms') }}">{{ __('Terms & Conditions') }}</a></small></div>
    </div>
</footer>
<script src="{{ $themeCss('js/egh-sg-nav.js') }}" defer></script>
<x-site.whatsapp-float />
<x-site.mobile-bar />
@stack('scripts')</body></html>
