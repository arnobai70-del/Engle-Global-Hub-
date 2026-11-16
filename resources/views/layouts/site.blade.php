<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $siteName=$siteName??'Eagle Global Hub LTD';$siteTagline=$siteTagline??'Travel & Visa Services';$siteContact=$siteContact??[];$siteSocial=$siteSocial??[];$footerLinks=$footerLinks??[];
        $seoOverrides=app(\App\Services\SeoMetadata::class)->forRoute(request()->route()?->getName());$pageTitle=$seoOverrides['title']??trim($__env->yieldContent('title','Travel'));$pageDescription=$seoOverrides['description']??trim($__env->yieldContent('meta_description','Travel and booking services from '.$siteName.'.'));$canonical=trim($__env->yieldContent('canonical',url()->current()));$robots=trim($__env->yieldContent('robots','index,follow'));$ogTitle=trim($__env->yieldContent('og_title',$pageTitle));$ogDescription=trim($__env->yieldContent('og_description',$pageDescription));$ogImage=trim($__env->yieldContent('og_image',''));$twitterCard=trim($__env->yieldContent('twitter_card','summary_large_image'));$fullTitle=request()->routeIs('home')?$pageTitle:$pageTitle.' | '.$siteName;
        $decodeSection=static fn(string $value):string=>html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8');$pageTitle=$decodeSection($pageTitle);$pageDescription=$decodeSection($pageDescription);$canonical=$decodeSection($canonical);$ogTitle=$decodeSection(trim($__env->yieldContent('og_title')))?:$pageTitle;$ogDescription=$decodeSection(trim($__env->yieldContent('og_description')))?:$pageDescription;$ogImage=$decodeSection($ogImage);$twitterCard=$decodeSection($twitterCard);$fullTitle=request()->routeIs('home')?$pageTitle:$pageTitle.' | '.$siteName;
        $bilingualRoutes=['home','consultancy.singapore','consultancy.guides.index','consultancy.institutions.index','travel-services.index','visa.index','work-visa.index','hotels.index','tours.index','support','terms','about','explore-bangladesh.index','offers.index','service-enquiries.create'];$hreflangBase=in_array(request()->route()?->getName(),$bilingualRoutes,true)&&str_starts_with($robots,'index')?(string) strtok($canonical,'?'):null;if($hreflangBase!==null&&app()->getLocale()==='bn'){$canonical=$hreflangBase.'?lang=bn';}
        $themeCss=static function(string $file):string{$modifiedAt=@filemtime(public_path($file));return asset($file).($modifiedAt?'?v='.$modifiedAt:'');};
        $travelServices=$travelServices??[];
        $servicePageRouteName=static function(array $service):?string{
            $routeName=$service['page_route_name']??$service['route_name']??null;
            return is_string($routeName)&&\Illuminate\Support\Facades\Route::has($routeName)?$routeName:null;
        };
        $brandName=trim(preg_replace('/\s+LTD\.?$/i','',$siteName));
        $branding=rescue(fn()=>app(\App\Services\SettingService::class)->group('branding'),[],false);$headerTagline=app()->getLocale()==='bn'&&filled($branding['header_tagline_bn']??null)?$branding['header_tagline_bn']:(filled($branding['header_tagline']??null)?$branding['header_tagline']:__('Your Gateway to Study in Singapore'));$siteLogo=$siteLogo??asset('images/eagle-global-hub-logo.png');
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
    <link rel="stylesheet" href="{{ $themeCss('css/egh-sg.css') }}">
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
<header class="sg-site-header">
    <div class="sg-topbar">
        <div class="sg-shell sg-topbar-inner">
            <div class="sg-topbar-links">
                @feature('support')<a href="{{ route('support') }}">{{ __('Customer Support') }}</a>@endfeature
                @feature('about')<a href="{{ route('offers.index') }}">{{ __('Offers') }}</a>@endfeature
            </div>
            <div class="sg-topbar-contact">
                @if($phoneHref)<a href="{{ $phoneHref }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>{{ data_get($siteContact,'phone') }}</a>@endif
                @if(data_get($siteContact,'email'))<a href="mailto:{{ data_get($siteContact,'email') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>{{ data_get($siteContact,'email') }}</a>@endif
                @if(!$phoneHref&&!data_get($siteContact,'email'))<span>{{ __('Official contact channels are not configured yet') }}</span>@endif
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
                <a href="{{ route('home') }}" class="sg-nav-link" @if($routeName==='home') aria-current="page" @endif>{{ __('Home') }}</a>
                @feature('student-consultancy')
                <details class="sg-dd" @if(str_starts_with($routeName,'consultancy.')||str_starts_with($routeName,'student.')||str_starts_with($routeName,'success-stories')) data-active @endif>
                    <summary>{{ __('Study in Singapore') }}</summary>
                    <div class="sg-dd-menu">
                        <a href="{{ route('consultancy.singapore') }}">{{ __('Study in Singapore') }}</a>
                        <a href="{{ route('services.show', 'student-visa') }}">{{ __('Student Visa') }}</a>
                        <a href="{{ route('consultancy.guides.index') }}">{{ __('Singapore Guides') }}</a>
                        <a href="{{ route('consultancy.institutions.index') }}">{{ __('Institutions') }}</a>
                        <a href="{{ route('consultancy.singapore') }}#consultation">{{ __('Free Consultation') }}</a>
                        <a href="{{ route('success-stories.index') }}">{{ __('Success Stories') }}</a>
                        @auth<a href="{{ route('student.applications.index') }}">{{ __('My Applications') }}</a>@endauth
                    </div>
                </details>
                @endfeature
                @feature('visa')
                <details class="sg-dd" @if(str_starts_with($routeName,'visa.')||str_starts_with($routeName,'work-visa.')||$routeName==='service-enquiries.create') data-active @endif>
                    <summary>{{ __('Visa Services') }}</summary>
                    <div class="sg-dd-menu">
                        <a href="{{ route('visa.index') }}">{{ __('Visa information') }}</a>
                        <a href="{{ route('services.show', 'tourist-visa') }}">{{ __('Tourist Visa') }}</a>
                        <a href="{{ route('services.show', 'business-visa') }}">{{ __('Business Visa') }}</a>
                        <a href="{{ route('work-visa.index') }}">{{ __('Work Visa') }}</a>
                        <a href="{{ route('services.show', 'foreigner-support') }}">{{ __('Foreigner Support') }}</a>
                    </div>
                </details>
                @endfeature
                <details class="sg-dd" @if(str_starts_with($routeName,'travel-services.')||str_starts_with($routeName,'services.')||str_starts_with($routeName,'flights.')||str_starts_with($routeName,'hotels.')||str_starts_with($routeName,'tours.')||str_starts_with($routeName,'offers.')||str_starts_with($routeName,'bookings.')) data-active @endif>
                    <summary>{{ __('Travel Services') }}</summary>
                    <div class="sg-dd-menu">
                        <a href="{{ route('travel-services.index') }}">{{ __('All travel services') }}</a>
                        @feature('flights')<a href="{{ route('services.show', 'air-ticket') }}">{{ __('Air Ticket') }}</a><a href="{{ route('services.show', 'business-class') }}">{{ __('Business Class') }}</a>@endfeature
                        @foreach($travelServices as $serviceKey=>$service)@feature($serviceKey)@php($servicePageRoute=$servicePageRouteName($service))@if($serviceKey!=='flights'&&$serviceKey!=='visa'&&$servicePageRoute)<a href="{{ route($servicePageRoute) }}">{{ __($service['label']) }}</a>@endif @endfeature @endforeach
                        <a href="{{ route('services.show', 'holiday-packages') }}">{{ __('Holiday Packages') }}</a>
                        <a href="{{ route('services.show', 'group-tours') }}">{{ __('Group Tours') }}</a>
                        <a href="{{ route('services.show', 'private-tours') }}">{{ __('Private Tours') }}</a>
                        <a href="{{ route('travel-services.index') }}#travel-group-2">{{ __('Trains, Bus, Cars & Bike') }}</a>
                        @feature('about')<a href="{{ route('offers.index') }}">{{ __('Offers & Promotions') }}</a>@endfeature
                        <a href="{{ route('rewards.index') }}">{{ __('Rewards') }}</a>
                        @auth @feature('bookings')<a href="{{ auth()->user()->can('flights.book')?route('bookings.index'):route('dashboard') }}">{{ __('Manage Booking') }}</a>@endfeature @endauth
                    </div>
                </details>
                <details class="sg-dd" @if(str_starts_with($routeName,'explore-bangladesh.')||str_starts_with($routeName,'inspiration.')||str_starts_with($routeName,'restaurants.')) data-active @endif>
                    <summary>{{ __('Explore Bangladesh') }}</summary>
                    <div class="sg-dd-menu">
                        <a href="{{ route('explore-bangladesh.index') }}">{{ __('Explore Bangladesh') }}</a>
                        <a href="{{ route('inspiration.index') }}">{{ __('Get Inspired') }}</a>
                        <a href="{{ route('restaurants.index') }}">{{ __('Restaurants') }}</a>
                        <a href="{{ route('reviews.index') }}">{{ __('Reviews') }}</a>
                        <a href="{{ route('forums.index') }}">{{ __('Forums') }}</a>
                        <a href="{{ route('map') }}">{{ __('Map') }}</a>
                    </div>
                </details>
                <details class="sg-dd" @if(in_array($routeName,['about','support','terms'],true)||str_starts_with($routeName,'company.')||str_starts_with($routeName,'business-services.')||str_starts_with($routeName,'b2b.')) data-active @endif>
                    <summary>{{ __('About') }}</summary>
                    <div class="sg-dd-menu">
                        @feature('about')<a href="{{ route('about') }}">{{ __('About Us') }}</a>@endfeature
                        <a href="{{ route('company.index') }}">{{ __('Company Profile') }}</a>
                        <a href="{{ route('services.show', 'export-import') }}">{{ __('Export & Import') }}</a>
                        <a href="{{ route('services.show', 'civil-construction') }}">{{ __('Civil & Construction Work') }}</a>
                        <a href="{{ route('b2b.register') }}">{{ __('Egabe B2B') }}</a>
                        <a href="{{ route('mobile-app') }}">{{ __('Mobile App') }}</a>
                        @feature('support')<a href="{{ route('support') }}">{{ __('Contact Us') }}</a>@endfeature
                        <a href="{{ route('terms') }}">{{ __('Terms & Conditions') }}</a>
                    </div>
                </details>
                @endif
                <div class="sg-nav-actions">
                    <a href="{{ route('search') }}" class="sg-icon-btn" aria-label="{{ __('Search') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></a>
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
                    <a href="{{ route('consultancy.singapore') }}#consultation" class="sg-btn sg-btn-red sg-btn-sm">{{ __('Apply Now') }}</a>
                    @endif
                </div>
            </nav>
        </div>
    </div>
</header>
@if(app()->environment(['local','testing'])&&!request()->routeIs('home'))<div class="site-environment-banner" role="status"><strong>Development / sandbox environment:</strong> supplier availability may use fixture or sandbox data and must not be treated as live inventory.</div>@endif
@if(request()->attributes->get('super_admin_feature_preview'))<div class="site-environment-banner" role="status"><strong>Super Admin Preview:</strong> this feature is disabled or hidden for other users.</div>@endif
@if(session('api_waiting'))<div class="sg-shell"><div class="sg-api-notice is-waiting" role="status" style="margin-top:1rem"><x-site.provider-badge state="waiting" /><div><p>{{ session('api_waiting') }}</p></div></div></div>@endif
<div id="main-content">@yield('content')</div>
<footer class="sg-footer">
    <div class="sg-shell">
        <div class="sg-footer-cta">
            <div><h2>{{ __('Ready to study in Singapore?') }}</h2><p>{{ __('Talk to our consultants about courses, admission and the Student\'s Pass.') }}</p></div>
            <div class="sg-footer-cta-actions"><a href="{{ route('consultancy.singapore') }}#consultation" class="sg-btn sg-btn-red">{{ __('Free Consultation') }}</a>@feature('support')<a href="{{ route('support') }}" class="sg-btn sg-btn-white">{{ __('Contact Us') }}</a>@endfeature</div>
        </div>
        <div class="sg-footer-main">
            <div class="sg-footer-about">
                <a href="{{ route('home') }}" class="sg-brand sg-brand-light"><img class="sg-brand-logo" src="{{ $siteLogo }}" alt="{{ $siteName }} logo" width="240" height="241" loading="lazy" decoding="async"><span class="sg-brand-copy"><strong>{{ $brandName }}</strong><small>{{ filled($siteTagline) ? __($siteTagline) : '' }}</small></span></a>
                <p>{{ filled($siteFooterDescription ?? null) ? __($siteFooterDescription) : '' }}</p>
                @if($siteSocial!==[])<div class="sg-footer-social">@foreach($siteSocial as $network=>$url)<a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ucfirst($network) }}">{{ strtoupper(substr($network,0,2)) }}</a>@endforeach</div>@endif
            </div>
            @php($footerMenu = app(\App\Services\Navigation\MenuService::class)->tree('footer'))
            @if($footerMenu !== null)
                @include('partials.menus.footer', ['items' => $footerMenu])
            @else
            <div><h3>{{ __('Quick Links') }}</h3><ul><li><a href="{{ route('home') }}">{{ __('Home') }}</a></li>@feature('about')<li><a href="{{ route('about') }}">{{ __('About Us') }}</a></li>@endfeature @feature('support')<li><a href="{{ route('support') }}">{{ __('Contact Us') }}</a></li>@endfeature<li><a href="{{ route('terms') }}">{{ __('Terms & Conditions') }}</a></li><li><a href="{{ route('map') }}">{{ __('Map') }}</a></li><li><a href="{{ route('mobile-app') }}">{{ __('Mobile App') }}</a></li><li><a href="{{ route('search') }}">{{ __('Search') }}</a></li>@foreach($footerLinks as $link)@if(!empty($link['url'])&&!empty($link['title']))<li><a href="{{ $link['url'] }}">{{ $link['title'] }}</a></li>@endif @endforeach</ul></div>
            <div><h3>{{ __('Study in Singapore') }}</h3><ul>@feature('student-consultancy')<li><a href="{{ route('consultancy.singapore') }}">{{ __('Student Consultancy') }}</a></li><li><a href="{{ route('consultancy.guides.index') }}">{{ __('Singapore Guides') }}</a></li><li><a href="{{ route('consultancy.institutions.index') }}">{{ __('Institutions') }}</a></li>@endfeature<li><a href="{{ route('success-stories.index') }}">{{ __('Success Stories') }}</a></li><li><a href="{{ route('explore-bangladesh.index') }}">{{ __('Explore Bangladesh') }}</a></li></ul></div>
            <div><h3>{{ __('Our Services') }}</h3><ul><li><a href="{{ route('travel-services.index') }}">{{ __('Travel Services') }}</a></li>@feature('flights')<li><a href="{{ route('services.show', 'air-ticket') }}">{{ __('Air Ticket') }}</a></li>@endfeature @foreach($travelServices as $serviceKey=>$service)@feature($serviceKey)@php($servicePageRoute=$servicePageRouteName($service))@if($serviceKey!=='flights'&&$servicePageRoute)<li><a href="{{ route($servicePageRoute) }}">{{ __($service['label']) }}</a></li>@endif @endfeature @endforeach @feature('visa')<li><a href="{{ route('work-visa.index') }}">{{ __('Work Visa Processing') }}</a></li>@endfeature<li><a href="{{ route('service-enquiries.create', 'tourist-visa') }}">{{ __('Send an Enquiry') }}</a></li></ul></div>
            @endif
            <div><h3>{{ __('Contact Info') }}</h3><ul class="sg-footer-contact">@if(data_get($siteContact,'address'))<li>{{ data_get($siteContact,'address') }}</li>@endif @if($phoneHref)<li><a href="{{ $phoneHref }}">{{ data_get($siteContact,'phone') }}</a></li>@endif @if(data_get($siteContact,'email'))<li><a href="mailto:{{ data_get($siteContact,'email') }}">{{ data_get($siteContact,'email') }}</a></li>@endif @if(data_get($siteContact,'hours'))<li>{{ data_get($siteContact,'hours') }}</li>@endif @if(!array_filter($siteContact))<li>{{ __('Official contact channels are not configured yet.') }}</li>@endif</ul></div>
        </div>
        <div class="sg-footer-bottom"><small>&copy; {{ now()->year }} {{ $siteName }}. {{ __('All rights reserved.') }}</small><small><a href="{{ route('terms') }}">{{ __('Privacy & Terms') }}</a> · {{ __('Secure account & booking steps') }}</small></div>
    </div>
</footer>
<script src="{{ $themeCss('js/egh-sg-nav.js') }}" defer></script>
@stack('scripts')</body></html>
