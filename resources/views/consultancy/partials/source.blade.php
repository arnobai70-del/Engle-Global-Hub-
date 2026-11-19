{{-- "Source: …" link for a factual row of a study destination page, like "Source: EMGS" on Study in Malaysia. --}}
@if(filled($item->source_url) && str_starts_with((string) $item->source_url, 'https://'))<a class="v2-source" href="{{ $item->source_url }}" target="_blank" rel="noopener noreferrer">{{ __('Source: :source', ['source' => $item->sourceLabel()]) }}</a>@endif
