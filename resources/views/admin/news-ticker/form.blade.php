@extends('layouts.admin')
@section('title', $item->exists ? 'Edit headline' : 'Add headline')
@section('content')
@php
    $tz = \App\Services\NewsTicker::TIMEZONE;
    $time = fn (string $field) => old($field, $item->{$field}?->setTimezone($tz)->format('Y-m-d\TH:i'));
@endphp
<x-admin.page-header :title="$item->exists ? 'Edit headline' : 'Add headline'" description="One short headline for the homepage news ticker." icon="N" eyebrow="News ticker">
    <a class="egh-button secondary" href="{{ route('admin.news-ticker.index') }}">All headlines</a>
</x-admin.page-header>
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="egh-card" method="POST" action="{{ $item->exists ? route('admin.news-ticker.update', $item) : route('admin.news-ticker.store') }}">
    @csrf @if($item->exists) @method('PATCH') @endif
    <p>Keep it short: it is read while it moves. Plain text only.</p>
    <div class="stu-form-grid">
        <label class="stu-full">Headline (English, required, up to 140 characters)<input name="text" maxlength="140" required value="{{ old('text', $item->title) }}"></label>
        <label class="stu-full">Headline in Bengali (optional; English is shown when empty)<input name="text_bn" lang="bn" maxlength="140" value="{{ old('text_bn', $item->subtitle) }}"></label>
        <label>Link (optional)<input name="url" maxlength="500" value="{{ old('url', $item->url) }}" placeholder="/study-in-malaysia or https://…"></label>
        <label>Badge (optional, e.g. New, Intake, Offer)<input name="badge" maxlength="20" value="{{ old('badge', $item->cta_label) }}"></label>
        <label>Show from (Bangladesh time, optional)<input type="datetime-local" name="starts_at" value="{{ $time('starts_at') }}"></label>
        <label>Show until (Bangladesh time, optional)<input type="datetime-local" name="ends_at" value="{{ $time('ends_at') }}"></label>
        <label class="stu-check"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $item->is_active))> Published</label>
    </div>
    <p class="stu-muted">Links to other websites open in a new tab.</p>
    <button class="egh-button">{{ $item->exists ? 'Save headline' : 'Add headline' }}</button>
</form>
@endsection
