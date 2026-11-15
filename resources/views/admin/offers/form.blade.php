@extends('layouts.admin')
@section('title', $offer->exists ? 'Edit Offer' : 'Add Offer')
@section('content')
<x-admin.page-header :title="$offer->exists ? 'Edit Offer' : 'Add Offer'" description="State real prices and conditions only. Do not promise visa approval." icon="O" eyebrow="Website CMS"><a class="egh-button secondary" href="{{ route('admin.offers.index') }}">Back</a></x-admin.page-header>
<div class="egh-card">
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $offer->exists ? route('admin.offers.update', $offer) : route('admin.offers.store') }}">@csrf @if($offer->exists) @method('PATCH') @endif
<div class="stu-form-grid">
<label>Title<input name="title" value="{{ old('title', $offer->title) }}" maxlength="150" required></label>
<label>URL slug<input name="slug" value="{{ old('slug', $offer->slug) }}" maxlength="160" placeholder="generated from the title"></label>
<label>Related service<select name="service"><option value="">Any service</option>@foreach($services as $service)<option value="{{ $service->value }}" @selected(old('service', $offer->service?->value) === $service->value)>{{ $service->label() }}</option>@endforeach</select></label>
<label>Display order<input type="number" name="sort_order" min="0" value="{{ old('sort_order', $offer->sort_order) }}" required></label>
<label>Valid from<input type="date" name="valid_from" value="{{ old('valid_from', $offer->valid_from?->format('Y-m-d')) }}"></label>
<label>Valid until<input type="date" name="valid_until" value="{{ old('valid_until', $offer->valid_until?->format('Y-m-d')) }}"></label>
<label class="stu-full">Summary<textarea name="summary" maxlength="300" required>{{ old('summary', $offer->summary) }}</textarea></label>
<label class="stu-full">Terms and conditions<textarea name="terms" maxlength="5000">{{ old('terms', $offer->terms) }}</textarea></label>
<label class="stu-full"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $offer->is_published))> Published</label>
</div>
<div class="admin-form-actions"><button class="egh-button">Save</button></div>
</form>
@if($offer->exists)<form method="POST" action="{{ route('admin.offers.destroy', $offer) }}" class="admin-danger-zone" onsubmit="return confirm('Delete this offer?')">@csrf @method('DELETE')<button class="egh-button danger">Delete Offer</button></form>@endif
</div>
@endsection
