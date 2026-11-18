{{--
    "Student Visa" mega-menu: every study destination switched on in Admin › Services & Destinations,
    the planning tools that are switched on plus the menu item's own extra links, and a consultation card.
    Malaysia and Singapore open their own pages; other countries open the Student Visa page with that
    country chosen in the enquiry form. In the burger menu the columns stack into a plain list.
    @var string $label
    @var list<array{label: string, url: string, current?: bool, new_tab?: bool}> $links  extra links from Admin › Menus
    @var bool $active
--}}
@php
    $availability = app(\App\Services\Content\PublicPageAvailability::class);
    $countryPages = ['MY' => 'consultancy.malaysia', 'SG' => 'consultancy.singapore'];
    $countries = rescue(fn () => \App\Http\Requests\Consultancy\StoreStudentEnquiryRequest::destinations(), collect(), false)
        ->map(fn ($country): array => [
            'label' => __($country->name),
            'url' => isset($countryPages[$country->iso2]) ? route($countryPages[$country->iso2]) : route('services.show', ['slug' => 'student-visa', 'country' => __($country->name)]).'#quote-form',
            'current' => isset($countryPages[$country->iso2]) && request()->routeIs($countryPages[$country->iso2]),
        ]);
    $countryUrls = $countries->pluck('url')->map(fn (string $url): string => rtrim((string) strtok($url, '?#'), '/'))->all();
    // Extra links that do not repeat a country page (for example the old "Study in Malaysia" child).
    $extraLinks = collect($links)->reject(fn (array $link): bool => in_array(rtrim((string) strtok($link['url'], '?#'), '/'), $countryUrls, true) && ! str_contains($link['url'], '#'))->values();
    $tools = collect([
        ['consultancy.malaysia.eligibility', __('Eligibility checker'), __('See which course level fits your results')],
        ['consultancy.malaysia.courses', __('Course finder'), __('Filter courses by level, subject, budget and intake')],
        ['consultancy.malaysia.cost-calculator', __('Cost calculator'), __('Yearly cost in MYR and Taka')],
        ['appointments.book', __('Book an appointment'), __('Pick a free time to talk to a consultant')],
    ])->filter(fn (array $tool): bool => \Illuminate\Support\Facades\Route::has($tool[0]) && $availability->isAvailable($tool[0]))->values();
    $hasSecondColumn = $tools->isNotEmpty() || $extraLinks->isNotEmpty();
@endphp
<details class="sg-dd sg-dd-mega" @if($active) data-active @endif>
    <summary>{{ $label }}</summary>
    <div class="sg-dd-menu sg-mega @if(! $hasSecondColumn) sg-mega-no-tools @endif">
        <div class="sg-mega-col">
            <p class="sg-mega-title">{{ __('Study destinations') }}</p>
            @foreach($countries as $country)
                <a href="{{ $country['url'] }}" @if($country['current']) aria-current="page" @endif>{{ $country['label'] }}</a>
            @endforeach
            <a href="{{ route('services.show', 'student-visa') }}" class="sg-mega-all" @if(request()->is('services/student-visa')) aria-current="page" @endif>{{ __('All about the student visa') }} →</a>
        </div>
        @if($hasSecondColumn)
        <div class="sg-mega-col">
            <p class="sg-mega-title">{{ __('Plan your study') }}</p>
            @foreach($tools as [$route, $toolLabel, $toolHint])
                <a href="{{ route($route) }}" class="sg-mega-tool" @if(request()->routeIs($route)) aria-current="page" @endif><strong>{{ $toolLabel }}</strong><small>{{ $toolHint }}</small></a>
            @endforeach
            @foreach($extraLinks as $link)
                <a href="{{ $link['url'] }}" @if($link['current'] ?? false) aria-current="page" @endif @if($link['new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif>{{ $link['label'] }}</a>
            @endforeach
        </div>
        @endif
        <div class="sg-mega-card">
            <strong>{{ __('Not sure where to start?') }}</strong>
            <p>{{ __('Talk to a consultant for free. We explain the options, costs and visa steps for each country.') }}</p>
            <a class="sg-mega-cta" href="{{ route('consultancy.malaysia') }}#apply">{{ __('Free Consultation') }}</a>
        </div>
    </div>
</details>
