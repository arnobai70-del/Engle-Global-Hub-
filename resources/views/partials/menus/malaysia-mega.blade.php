{{--
    "Study in Malaysia" mega-menu: the menu's own links, the planning tools that are switched on,
    and a consultation card. In the burger menu the three columns stack into a plain list.
    @var string $label
    @var list<array{label: string, url: string, current?: bool, new_tab?: bool}> $links
    @var bool $active
--}}
@php
    $availability = app(\App\Services\Content\PublicPageAvailability::class);
    $tools = collect([
        ['consultancy.malaysia.eligibility', __('Eligibility checker'), __('See which course level fits your results')],
        ['consultancy.malaysia.courses', __('Course finder'), __('Filter courses by level, subject, budget and intake')],
        ['consultancy.malaysia.cost-calculator', __('Cost calculator'), __('Yearly cost in MYR and Taka')],
        ['appointments.book', __('Book an appointment'), __('Pick a free time to talk to a consultant')],
    ])->filter(fn (array $tool): bool => \Illuminate\Support\Facades\Route::has($tool[0]) && $availability->isAvailable($tool[0]))->values();
@endphp
<details class="sg-dd sg-dd-mega" @if($active) data-active @endif>
    <summary>{{ $label }}</summary>
    <div class="sg-dd-menu sg-mega @if($tools->isEmpty()) sg-mega-no-tools @endif">
        <div class="sg-mega-col">
            <p class="sg-mega-title">{{ $label }}</p>
            @foreach($links as $link)
                <a href="{{ $link['url'] }}" @if($link['current'] ?? false) aria-current="page" @endif @if($link['new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif>{{ $link['label'] }}</a>
            @endforeach
        </div>
        @if($tools->isNotEmpty())
        <div class="sg-mega-col">
            <p class="sg-mega-title">{{ __('Plan your study') }}</p>
            @foreach($tools as [$route, $toolLabel, $toolHint])
                <a href="{{ route($route) }}" class="sg-mega-tool" @if(request()->routeIs($route)) aria-current="page" @endif><strong>{{ $toolLabel }}</strong><small>{{ $toolHint }}</small></a>
            @endforeach
        </div>
        @endif
        <div class="sg-mega-card">
            <strong>{{ __('Not sure where to start?') }}</strong>
            <p>{{ __('Talk to a consultant for free. We explain the options, costs and EMGS steps for you.') }}</p>
            <a class="sg-mega-cta" href="{{ route('consultancy.malaysia') }}#apply">{{ __('Free Consultation') }}</a>
        </div>
    </div>
</details>
