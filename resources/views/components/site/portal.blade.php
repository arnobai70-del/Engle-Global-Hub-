@props(['active' => null, 'kind' => 'student'])
@php
    $user = auth()->user();
    $icon = static fn (string $path): string => '<svg viewBox="0 0 24 24" aria-hidden="true">'.$path.'</svg>';
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="8" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="15" width="7" height="6" rx="1.5"/>',
        'applications' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
        'enquiries' => '<path d="M4 5h16v11H8l-4 4z"/><path d="M8 9h8M8 12.5h5"/>',
        'bookings' => '<path d="M10.5 13.5 3 11l1.5-1.5 7.5.8 4.5-4.8a2 2 0 0 1 2.8 2.8L14.5 12.8l.8 7.5L13.8 22l-2.5-7.5"/>',
        'notifications' => '<path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'profile' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.3-4 4.3-6 8-6s6.7 2 8 6"/>',
        'referrals' => '<circle cx="9" cy="8" r="3.5"/><path d="M3 20c.8-3.5 3.2-5.5 6-5.5s5.2 2 6 5.5"/><path d="M17 8v6M14 11h6"/>',
        'study' => '<path d="M2 9.5 12 5l10 4.5-10 4.5z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/>',
    ];
    $links = $kind === 'agent'
        ? [
            ['key' => 'agent', 'label' => __('Dashboard'), 'href' => route('agent.index'), 'icon' => 'dashboard'],
            ['key' => 'agent-referral', 'label' => __('Refer a student'), 'href' => route('agent.referrals.create'), 'icon' => 'referrals'],
            ['key' => 'agent-service-referral', 'label' => __('Refer an Enquiry'), 'href' => route('agent.service-referrals.create'), 'icon' => 'enquiries'],
            ['key' => 'notifications', 'label' => __('Notifications'), 'href' => route('notifications.index'), 'icon' => 'notifications'],
            ['key' => 'account', 'label' => __('My Account'), 'href' => route('account.overview'), 'icon' => 'profile'],
        ]
        : array_values(array_filter([
            ['key' => 'dashboard', 'label' => __('Dashboard'), 'href' => route('dashboard'), 'icon' => 'dashboard'],
            ['key' => 'applications', 'label' => __('My Applications'), 'href' => route('student.applications.index'), 'icon' => 'applications'],
            ['key' => 'enquiries', 'label' => __('My Enquiries'), 'href' => route('service-enquiries.mine'), 'icon' => 'enquiries'],
            $user?->can('flights.book') ? ['key' => 'bookings', 'label' => __('Manage Booking'), 'href' => route('bookings.index'), 'icon' => 'bookings'] : null,
            ['key' => 'notifications', 'label' => __('Notifications'), 'href' => route('notifications.index'), 'icon' => 'notifications'],
            ['key' => 'account', 'label' => __('My Account'), 'href' => route('account.overview'), 'icon' => 'profile'],
            ['key' => 'study', 'label' => __('Study in Malaysia'), 'href' => route('consultancy.malaysia'), 'icon' => 'study'],
        ]));
    $title = match ($kind) {
        'agent' => __('Agent Portal'),
        'tourist' => __('Tourist Portal'),
        default => __('Student Portal'),
    };
@endphp
<div class="sg-shell sg-portal">
    <aside class="sg-portal-side" aria-label="{{ $title }}">
        <div class="sg-portal-side-head">
            <img src="{{ asset('images/eagle-global-hub-logo.png') }}" alt="" width="240" height="241">
            <div><strong>{{ $title }}</strong><small>{{ $user?->name }}</small></div>
        </div>
        <nav class="sg-portal-nav">
            @foreach($links as $link)
                <a href="{{ $link['href'] }}" @if($active === $link['key']) aria-current="page" @endif>{!! $icon($icons[$link['icon']]) !!}<span>{{ $link['label'] }}</span></a>
            @endforeach
        </nav>
    </aside>
    <div class="sg-portal-main">
        {{ $slot }}
    </div>
</div>
