@extends('layouts.admin')
@section('title', 'News ticker')
@section('content')
@php
    $now = now();
    $window = function ($item) {
        $tz = \App\Services\NewsTicker::TIMEZONE;
        $from = $item->starts_at?->setTimezone($tz)->format('j M Y H:i');
        $until = $item->ends_at?->setTimezone($tz)->format('j M Y H:i');

        return match (true) {
            $from && $until => $from.' – '.$until,
            (bool) $from => 'From '.$from,
            (bool) $until => 'Until '.$until,
            default => 'Always',
        };
    };
    $state = fn ($item) => match (true) {
        ! $item->is_active => 'Draft',
        $item->starts_at && $item->starts_at->gt($now) => 'Scheduled',
        $item->ends_at && $item->ends_at->lte($now) => 'Ended',
        default => 'Showing',
    };
@endphp
<x-admin.page-header title="News ticker" description="Short headlines that scroll across the top of the homepage hero." icon="N" eyebrow="Website CMS">
    @can('content.manage')<a class="egh-button" href="{{ route('admin.news-ticker.create') }}">Add headline</a>@endcan
</x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="egh-card" data-ticker-summary>
    <p>
        @if(! $enabled)
            The ticker is <strong>off</strong>. Nothing is shown on the homepage.
        @elseif($activeCount === 0)
            The ticker is <strong>on</strong>, but no headline is showing right now, so the homepage shows nothing.
        @else
            The ticker is <strong>on</strong> and shows {{ $activeCount }} headline(s) on the homepage now.
        @endif
        Times are Bangladesh time (Asia/Dhaka). Changes appear on the homepage straight away.
    </p>
</div>

@can('content.manage')
<form class="egh-card" method="POST" action="{{ route('admin.news-ticker.settings') }}" data-ticker-settings>
    @csrf @method('PATCH')
    <h2>Settings</h2>
    <div class="stu-form-grid">
        <label class="stu-check"><input type="checkbox" name="news_enabled" value="1" @checked(old('news_enabled', $enabled))> Show the ticker on the homepage</label>
        <label>Speed<select name="news_speed">@foreach(['slow' => 'Slow', 'normal' => 'Normal (recommended)', 'fast' => 'Fast'] as $value => $label)<option value="{{ $value }}" @selected(old('news_speed', $speed) === $value)>{{ $label }}</option>@endforeach</select></label>
        <label>Label at the left<input name="news_label" maxlength="40" value="{{ old('news_label', $settings['news_label'] ?? '') }}" placeholder="Latest"></label>
        <label>Label in Bengali<input name="news_label_bn" lang="bn" maxlength="40" value="{{ old('news_label_bn', $settings['news_label_bn'] ?? '') }}" placeholder="সর্বশেষ"></label>
    </div>
    <button class="egh-button">Save settings</button>
</form>
@endcan

<div class="egh-card admin-table-card">
<table class="stu-table">
    <thead><tr><th>Order</th><th>Headline</th><th>Link</th><th>Shown</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    @forelse($items as $item)
        <tr data-ticker-item="{{ $item->id }}">
            <td>
                @can('content.manage')
                <form method="POST" action="{{ route('admin.news-ticker.move', $item) }}" class="stu-inline-form">@csrf @method('PATCH')<button class="egh-button secondary" name="direction" value="up" aria-label="Move up">↑</button><button class="egh-button secondary" name="direction" value="down" aria-label="Move down">↓</button></form>
                @else
                {{ $loop->iteration }}
                @endcan
            </td>
            <td>@if($item->cta_label)<span class="stu-badge">{{ $item->cta_label }}</span> @endif<strong>{{ $item->title }}</strong>@if($item->subtitle)<br><span class="stu-muted" lang="bn">{{ $item->subtitle }}</span>@endif</td>
            <td>{{ $item->url ?: '—' }}</td>
            <td>{{ $window($item) }}</td>
            <td>{{ $state($item) }}</td>
            <td>
                @can('content.manage')
                <a class="stu-link" href="{{ route('admin.news-ticker.edit', $item) }}">Edit</a>
                <form method="POST" action="{{ route('admin.news-ticker.destroy', $item) }}" class="stu-inline-form" onsubmit="return confirm('Delete this headline?')">@csrf @method('DELETE')<button class="egh-button secondary">Delete</button></form>
                @endcan
            </td>
        </tr>
    @empty
        <tr><td colspan="6">No headlines yet.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@endsection
