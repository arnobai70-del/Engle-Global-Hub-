{{-- Admin-managed header menu (Admin › Menus). Same markup as the built-in menu, so desktop and mobile stay in sync. --}}
@php($currentUrl = url()->current())
@php($isCurrent = static fn (?string $url): bool => $url !== null && ! str_contains($url, '#') && rtrim(strtok($url, '?'), '/') === rtrim($currentUrl, '/'))
@php($malaysiaUrl = rtrim(route('consultancy.malaysia'), '/'))
{{-- The study item (key study-malaysia or student-visa, or the dropdown that links to Study in Malaysia) becomes the Student Visa mega-menu. --}}
@php($isMalaysia = static fn (array $item): bool => in_array($item['key'], ['study-malaysia', 'student-visa'], true) || collect($item['children'])->contains(fn ($child): bool => rtrim(strtok((string) $child['url'], '?#'), '/') === $malaysiaUrl))
@foreach($items as $item)
    @if($isMalaysia($item))
        @include('partials.menus.student-visa-mega', [
            'label' => $item['label'],
            'links' => collect($item['children'])->map(fn ($child): array => ['label' => $child['label'], 'url' => $child['url'], 'current' => $isCurrent($child['url']), 'new_tab' => $child['new_tab']])->all(),
            'active' => request()->routeIs('consultancy.*') || request()->is('services/student-visa') || collect($item['children'])->contains(fn ($child) => $isCurrent($child['url'])),
        ])
    @elseif($item['children'] === [])
        <a href="{{ $item['url'] }}" class="sg-nav-link" @if($isCurrent($item['url'])) aria-current="page" @endif @if($item['new_tab']) target="_blank" rel="noopener noreferrer" @endif>{{ $item['label'] }}</a>
    @else
        <details class="sg-dd" @if(collect($item['children'])->contains(fn ($child) => $isCurrent($child['url']))) data-active @endif>
            <summary>{{ $item['label'] }}</summary>
            <div class="sg-dd-menu">
                @foreach($item['children'] as $child)
                    <a href="{{ $child['url'] }}" @if($isCurrent($child['url'])) aria-current="page" @endif @if($child['new_tab']) target="_blank" rel="noopener noreferrer" @endif>{{ $child['label'] }}</a>
                @endforeach
            </div>
        </details>
    @endif
@endforeach
