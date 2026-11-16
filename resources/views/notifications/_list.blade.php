<main class="{{ $isStaff ? '' : 'egho-page' }}">
<div class="{{ $isStaff ? 'egh-card' : 'egho-shell' }}">
<section class="{{ $isStaff ? '' : 'egho-contact-card' }}">
@unless($isStaff)<h1>{{ __('Notifications') }}</h1>@endunless
@if(session('status'))<p role="status">{{ session('status') }}</p>@endif
@if($notifications->isEmpty())
<p>{{ __('You have no notifications.') }}</p>
@else
<form method="POST" action="{{ route('notifications.read') }}">@csrf<button type="submit" class="{{ $isStaff ? 'egh-button secondary' : 'egho-btn' }}">{{ __('Mark all as read') }}</button></form>
<ul>
@foreach($notifications as $notification)
<li style="margin:12px 0">
<strong>@if($notification->read_at === null)<span aria-label="Unread">● </span>@endif{{ $notification->data['title'] ?? 'Notification' }}</strong><br>
{{ $notification->data['body'] ?? '' }}<br>
<small>{{ $notification->created_at->format('j M Y, H:i') }}</small>
@if(! empty($notification->data['url']) && str_starts_with($notification->data['url'], '/')) · <a href="{{ $notification->data['url'] }}">{{ __('Open') }}</a>@endif
</li>
@endforeach
</ul>
{{ $notifications->links() }}
@endif
</section>
</div>
</main>
