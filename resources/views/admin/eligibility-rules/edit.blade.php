@extends('layouts.admin')
@section('title', 'Edit eligibility rule')
@section('content')
<x-admin.page-header title="Edit eligibility rule" description="Changes apply to the public eligibility checker right away." icon="E" eyebrow="Malaysia Consultancy"><a class="egh-button secondary" href="{{ route('admin.eligibility-rules.index') }}">Back</a></x-admin.page-header>
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('admin.eligibility-rules.update', $rule) }}" class="egh-card">@csrf @method('PATCH')
@include('admin.eligibility-rules._fields', ['rule' => $rule])
<div class="admin-form-actions"><button class="egh-button">Save</button><a class="egh-button secondary" href="{{ route('admin.eligibility-rules.index') }}">Cancel</a></div></form>
@endsection
