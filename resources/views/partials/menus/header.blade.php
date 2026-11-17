{{-- Admin-managed header menu (Admin › Menus). Same markup as the built-in menu, so desktop and mobile stay in sync. --}}
@php($currentUrl = url()->current())
@php($isCurrent = static fn (?string $url): bool => $url !== null && ! str_contains($url, '#') && rtrim(strtok($url, '?'), '/') === rtrim($currentUrl, '/'))
@foreach($items as $item)
    @if($item['children'] === [])
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
