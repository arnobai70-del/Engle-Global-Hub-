{{--
    Hero for the Study in Malaysia planning tools (eligibility, course finder, cost calculator, booking).
    @var string $title
    @var string $lead
    @var string $id  heading id
--}}
<section class="sg-page-hero v2-tool-hero" style="--sg-page-hero: url('{{ asset('images/malaysia/kuala-lumpur-day.webp') }}')" aria-labelledby="{{ $id }}">
    <div class="sg-shell">
        <nav class="sg-crumbs" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <a href="{{ route('consultancy.malaysia') }}">{{ __('Study in Malaysia') }}</a> › <span aria-current="page">{{ $title }}</span></nav>
        <h1 id="{{ $id }}">{{ $title }}</h1>
        <p>{{ $lead }}</p>
    </div>
</section>
