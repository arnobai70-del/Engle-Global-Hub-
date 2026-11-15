@extends('layouts.admin')
@section('title', 'Open Student Application')
@section('content')
<x-admin.page-header title="Open Student Application" :description="'For '.$student->first_name.' '.$student->last_name.' ('.$student->reference_code.')'" icon="S" eyebrow="Singapore Consultancy"><a class="egh-button secondary" href="{{ route('admin.students.show', $student) }}">Back</a></x-admin.page-header>
<div class="egh-card">
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('admin.student-applications.store', $student) }}">@csrf
@include('admin.student-applications._fields', ['application' => null])
<div class="admin-form-actions"><button class="egh-button">Open application</button><a class="egh-button secondary" href="{{ route('admin.students.show', $student) }}">Cancel</a></div>
</form>
</div>
@endsection
