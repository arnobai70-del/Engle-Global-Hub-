@extends('layouts.admin')
@section('title', 'Website launch checklist')
@push('head')
<style>
    .launch-summary { display: flex; flex-wrap: wrap; gap: .5rem; margin: 0 0 1rem; }
    .launch-status { display: inline-flex; align-items: center; gap: .35rem; padding: .2rem .65rem; border-radius: 999px; font-size: .8rem; font-weight: 700; white-space: nowrap; }
    .launch-status::before { content: ""; width: .55rem; height: .55rem; border-radius: 50%; background: currentColor; }
    .launch-status.is-green { background: #e7f6ec; color: #13703a; }
    .launch-status.is-amber { background: #fff4dc; color: #8a5a00; }
    .launch-status.is-red { background: #fde8e8; color: #a61b1b; }
    .launch-table td:first-child { width: 8rem; }
    .launch-table td:last-child { width: 5rem; text-align: right; }
</style>
@endpush
@section('content')
<x-admin.page-header title="Website launch checklist" description="What still has to be filled in or fixed before launch. Every row reads the live settings, so it updates as soon as you save." icon="L" eyebrow="Launch" />

<div class="egh-card">
    <div class="launch-summary" data-launch-summary>
        <span class="launch-status is-red">{{ $counts['red'] }} to fix</span>
        <span class="launch-status is-amber">{{ $counts['amber'] }} to fill in</span>
        <span class="launch-status is-green">{{ $counts['green'] }} ready</span>
    </div>
    <p><strong>Red</strong> blocks launch: visitors or enquiries are affected. <strong>Amber</strong> is hidden on the website until you fill it in; nothing broken is shown meanwhile. <strong>Green</strong> is ready.</p>
</div>

@foreach($sections as $section)
<section class="egh-card admin-table-card" aria-labelledby="launch-{{ $section['key'] }}" data-launch-section="{{ $section['key'] }}">
    <h2 id="launch-{{ $section['key'] }}">{{ $section['title'] }}</h2>
    <table class="stu-table launch-table">
        <thead><tr><th>Status</th><th>Check</th><th><span class="sr-only">Open</span></th></tr></thead>
        <tbody>
        @foreach($section['items'] as $item)
            <tr data-launch-status="{{ $item['status'] }}">
                <td><span class="launch-status is-{{ $item['status'] }}">{{ ['green' => 'Ready', 'amber' => 'Fill in', 'red' => 'Fix'][$item['status']] }}</span></td>
                <td><strong>{{ $item['label'] }}</strong><br><span class="stu-muted">{{ $item['detail'] }}</span></td>
                <td>@if($item['url'])<a class="stu-link" href="{{ $item['url'] }}">Open</a>@endif</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
@endforeach
@endsection
