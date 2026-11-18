{{-- Social links from Admin › Homepage (each hidden when empty). @var array<string, string> $links  network => url --}}
@php
    $icons = [
        'facebook' => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v6h4v-6h3l1-4h-4V8z"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/>',
        'linkedin' => '<path d="M4 9h4v11H4zM6 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM10 9h4v1.6c.6-1 1.9-1.9 3.6-1.9 3 0 4.4 1.9 4.4 5.3V20h-4v-5.4c0-1.6-.6-2.5-1.9-2.5-1.4 0-2.1 1-2.1 2.5V20h-4z"/>',
        'youtube' => '<path d="M22 12s0-3.4-.4-4.9a2.6 2.6 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.3A2.6 2.6 0 0 0 2.4 7.1C2 8.6 2 12 2 12s0 3.4.4 4.9a2.6 2.6 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.3a2.6 2.6 0 0 0 1.8-1.8c.4-1.5.4-4.9.4-4.9zM10 15V9l5 3z"/>',
        'tiktok' => '<path d="M16 3c.4 2.4 1.9 3.8 4 4v3.3a7.5 7.5 0 0 1-4-1.2V15a6 6 0 1 1-6-6h.6v3.4A2.7 2.7 0 1 0 12.7 15V3z"/>',
    ];
    $names = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'];
@endphp
@if($links !== [])
<ul class="sg-footer-social" aria-label="{{ __('Follow us') }}">
    @foreach($links as $network => $url)
        <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $names[$network] ?? ucfirst($network) }}" title="{{ $names[$network] ?? ucfirst($network) }}"><svg viewBox="0 0 24 24" aria-hidden="true" width="20" height="20">{!! $icons[$network] ?? '<circle cx="12" cy="12" r="8"/>' !!}</svg></a></li>
    @endforeach
</ul>
@endif
