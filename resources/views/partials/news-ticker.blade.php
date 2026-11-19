{{--
    Homepage news ticker (Admin › News ticker), at the top of the hero band.
    Without JavaScript, or with reduced motion, the headlines are a static line that can be scrolled.
    With JavaScript, egh-sg-nav.js measures one group and sets the loop duration from the speed in
    px/s, so every headline moves at the same speed. The second group exists only for the seamless
    loop: it is hidden from screen readers and its links are not focusable.
    @var array{label: string, speed: int, items: list<array{text: string, url: ?string, external: bool, badge: ?string}>} $ticker
--}}
<section class="egho-news-ticker" aria-label="{{ __('Latest updates') }}" data-news-ticker data-speed="{{ $ticker['speed'] }}"
    data-label-pause="{{ __('Pause headlines') }}" data-label-play="{{ __('Play headlines') }}">
    <div class="v2-shell egho-news-ticker-inner">
        <span class="egho-news-ticker-label">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9v6h3l5 4V5L7 9z"/><path d="M16 9a4 4 0 0 1 0 6"/></svg>
            <span class="egho-news-ticker-label-text">{{ $ticker['label'] }}</span>
        </span>
        <div class="egho-news-ticker-viewport">
            <div class="egho-news-ticker-track">
                @foreach ([false, true] as $duplicate)
                    <ul class="egho-news-ticker-group" @if($duplicate) aria-hidden="true" data-news-duplicate @endif>
                        @foreach ($ticker['items'] as $item)
                            <li class="egho-news-ticker-item">
                                @if($item['badge'])<span class="egho-news-ticker-badge">{{ $item['badge'] }}</span>@endif
                                @if($item['url'])
                                    <a href="{{ $item['url'] }}" @if($item['external']) target="_blank" rel="noopener noreferrer" @endif @if($duplicate) tabindex="-1" @endif>{{ $item['text'] }}</a>
                                @else
                                    <span>{{ $item['text'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>
        <button type="button" class="egho-news-ticker-toggle" aria-pressed="false" aria-label="{{ __('Pause headlines') }}" data-news-toggle hidden>
            <svg class="egho-news-icon-pause" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14M16 5v14"/></svg>
            <svg class="egho-news-icon-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5l11 7-11 7z"/></svg>
        </button>
    </div>
</section>
