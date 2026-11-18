@extends('layouts.admin')
@section('title', 'Edit listing: '.$listing->name)
@section('content')
<x-admin.page-header :title="$listing->name" :description="'Listing at '.$destination->name.'. Enter only verified details.'" icon="D" eyebrow="Website CMS"><a class="egh-button secondary" href="{{ route('admin.destinations.edit', $destination) }}#listings">Back to {{ $destination->name }}</a></x-admin.page-header>
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('admin.destinations.listings.update', [$destination, $listing]) }}" class="egh-card">@csrf @method('PATCH')
    @include('admin.destinations._listing-fields', ['listing' => $listing])
    <div class="admin-form-actions"><button class="egh-button">Save listing</button></div>
</form>
<form method="POST" action="{{ route('admin.destinations.listings.destroy', [$destination, $listing]) }}" class="admin-danger-zone" onsubmit="return confirm('Delete this listing?')">@csrf @method('DELETE')<button class="egh-button danger">Delete listing</button></form>
@endsection
