@extends('layouts.site')

@section('title', __('Cost calculator: Study in Malaysia'))
@section('meta_description', __('Estimate the yearly cost of studying in Malaysia in Ringgit and Taka: tuition, housing, food and the Student Pass, using our current figures.'))
@section('body_class', 'sg-body-v2')

@php
    $money = static fn (float $amount): string => number_format($amount, 0);
    $range = static fn (float $min, float $max, string $prefix): string => $prefix.' '.($min === $max ? number_format($min, 0) : number_format($min, 0).' – '.number_format($max, 0));
@endphp

@section('content')
<main>
    @include('partials.tools.hero', ['id' => 'cost-title', 'title' => __('Cost calculator'), 'lead' => __('See the yearly cost of studying in Malaysia in Ringgit and in Taka. Change any amount to match your own plan.')])

    <section class="v2-section" aria-label="{{ __('Cost calculator') }}">
        <div class="v2-shell v2-tool-grid v2-cost">
            <form class="v2-form-card v2-form" data-cost-calculator data-rate="{{ $rate }}">
                <h2>{{ __('Yearly costs') }}</h2>
                <div class="v2-cost-rows">
                    @foreach($items as $item)
                        <div class="v2-cost-row" data-cost-row>
                            <label class="v2-check" for="cost-on-{{ $item['key'] }}"><input type="checkbox" id="cost-on-{{ $item['key'] }}" checked data-cost-include><span>{{ $item['label'] }}</span></label>
                            <label class="v2-field v2-cost-amount" for="cost-{{ $item['key'] }}"><span class="sg-visually-hidden">{{ $item['label'] }} (MYR)</span>
                                <span class="v2-cost-input"><span aria-hidden="true">MYR</span><input id="cost-{{ $item['key'] }}" type="number" inputmode="numeric" min="0" step="100" value="{{ (int) $item['min'] }}" data-cost-amount></span>
                            </label>
                            @if($item['min'] !== $item['max'])<small class="v2-cost-hint">{{ __('Our figure: :range', ['range' => $range($item['min'], $item['max'], 'MYR')]) }}</small>@endif
                        </div>
                    @endforeach
                </div>
                <label class="v2-field" for="cost-years">{{ __('Years of study') }}
                    <select id="cost-years" data-cost-years>@foreach(range(1, 5) as $years)<option value="{{ $years }}">{{ trans_choice(':count year|:count years', $years, ['count' => $years]) }}</option>@endforeach</select>
                </label>
            </form>

            <div class="v2-tool-result" aria-live="polite">
                <div class="v2-card v2-cost-total">
                    <p class="v2-eyebrow">{{ __('Estimated total') }}</p>
                    <p class="v2-cost-bdt"><span data-cost-bdt>{{ $range($totals['bdt_min'], $totals['bdt_min'], 'BDT') }}</span></p>
                    <p class="v2-cost-myr"><span data-cost-myr>{{ $range($totals['myr_min'], $totals['myr_min'], 'MYR') }}</span> · <span data-cost-period>{{ trans_choice(':count year|:count years', 1, ['count' => 1]) }}</span></p>
                    @if($totals['myr_min'] !== $totals['myr_max'])<p class="v2-cost-hint">{{ __('With our full ranges: :range per year.', ['range' => $range($totals['bdt_min'], $totals['bdt_max'], 'BDT')]) }}</p>@endif
                    <p class="v2-cost-rate">{{ __('Rate used: 1 MYR = :rate BDT', ['rate' => rtrim(rtrim(number_format($rate, 4), '0'), '.')]) }}@if($rateDate) · {{ __('checked on :date', ['date' => $rateDate]) }}@endif</p>
                </div>
                <p class="v2-tool-note">{{ __('An estimate, not a quote. Each institution sets its own tuition and EMGS sets the Student Pass fees; exchange rates change daily. Air ticket and our service fee are not included.') }}</p>
                <div class="v2-actions">
                    <a class="v2-btn v2-btn-red" href="{{ route('consultancy.malaysia') }}#apply">{{ __('Get an exact quote') }}</a>
                    @if(\Illuminate\Support\Facades\Route::has('consultancy.malaysia.courses') && app(\App\Services\Content\PublicPageAvailability::class)->isAvailable('consultancy.malaysia.courses'))<a class="v2-btn v2-btn-outline" href="{{ route('consultancy.malaysia.courses') }}">{{ __('Find a course') }}</a>@endif
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
