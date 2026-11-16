@props(['name'])
@php
    $paths = [
        'cap' => '<path d="M2 9.5 12 5l10 4.5-10 4.5z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/><path d="M22 9.5V15"/>',
        'passport' => '<rect x="5" y="3" width="14" height="18" rx="2"/><circle cx="12" cy="10" r="3.2"/><path d="M9 17h6"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5h6v2M3 12h18"/>',
        'plane' => '<path d="M10.5 13.5 3 11l1.5-1.5 7.5.8 4.5-4.8a2 2 0 0 1 2.8 2.8L14.5 12.8l.8 7.5L13.8 22l-2.5-7.5"/>',
        'seat' => '<path d="M7 4v9a2 2 0 0 0 2 2h7l2 5"/><path d="M7 13h9a2 2 0 0 1 2 2"/><path d="M5 20h6"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'group' => '<circle cx="8" cy="8" r="3"/><circle cx="16.5" cy="9" r="2.5"/><path d="M2.5 19c.7-3.2 2.9-5 5.5-5s4.8 1.8 5.5 5M14 14.5c2.6-.6 5.6.6 6.5 4.5"/>',
        'mountain' => '<path d="m3 19 6-9 4 5 2.5-3L21 19z"/><circle cx="16.5" cy="6.5" r="1.8"/>',
        'train' => '<rect x="5" y="3" width="14" height="14" rx="3"/><path d="M5 10h14M9 21l1.5-4M15 21l-1.5-4"/><circle cx="9" cy="13.5" r=".8"/><circle cx="15" cy="13.5" r=".8"/>',
        'bus' => '<rect x="4" y="3" width="16" height="15" rx="2"/><path d="M4 10h16M7 18v2M17 18v2"/><circle cx="8" cy="14" r=".8"/><circle cx="16" cy="14" r=".8"/>',
        'car' => '<path d="M5 16V11l2-5h10l2 5v5"/><path d="M3 16h18v2H3zM5 11h14"/><circle cx="7.5" cy="18" r="1.5"/><circle cx="16.5" cy="18" r="1.5"/>',
        'bike' => '<circle cx="5.5" cy="17" r="3.5"/><circle cx="18.5" cy="17" r="3.5"/><path d="M5.5 17 9 9h5l4.5 8M9 9 7.5 6H5M14 9l1-3h2"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.4 2.5 3.6 5.5 3.6 9s-1.2 6.5-3.6 9c-2.4-2.5-3.6-5.5-3.6-9S9.6 5.5 12 3z"/>',
        'box' => '<path d="m3 7 9-4 9 4v10l-9 4-9-4z"/><path d="m3 7 9 4 9-4M12 11v10"/>',
        'build' => '<path d="M3 21h18M5 21V9l7-5 7 5v12"/><path d="M9 21v-6h6v6M9 11h6"/>',
        'bed' => '<path d="M3 18V7M3 14h18v4M21 14v-2.5A2.5 2.5 0 0 0 18.5 9H11v5"/><circle cx="7" cy="11" r="2"/>',
        'map' => '<path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3z"/><path d="M9 3v15M15 6v15"/>',
        'phone' => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
        'visa' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14.5 3v4.5H19"/><path d="M9 13h6M9 16.5h4"/>',
        'id' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="11" r="2"/><path d="M5.5 16c.6-1.6 1.7-2.4 3-2.4s2.4.8 3 2.4M14 10h4M14 13.5h4"/>',
        'leaf' => '<path d="M12 21V11"/><path d="M12 11c-4 0-7-2.5-7-7 4.5 0 7 3 7 7zM12 14c3.5 0 6-2 6-6-4 0-6 2.5-6 6z"/><path d="M5 21h14"/>',
        'tag' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
        'support' => '<path d="M4 13a8 8 0 0 1 16 0"/><rect x="3" y="13" width="4" height="6" rx="1.5"/><rect x="17" y="13" width="4" height="6" rx="1.5"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ];
@endphp
<svg viewBox="0 0 24 24" aria-hidden="true" {{ $attributes }}>{!! $paths[$name] ?? $paths['globe'] !!}</svg>
