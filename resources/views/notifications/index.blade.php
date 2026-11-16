@php($isStaff = auth()->user()->isBackOffice())
@extends($isStaff ? 'layouts.admin' : 'layouts.site')
@section('title', 'Notifications')
@section('robots', 'noindex,nofollow')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
@if($isStaff)<x-admin.page-header title="Notifications" description="Updates about applications you are responsible for." eyebrow="Account" />@endif
@if($isStaff)
@include('notifications._list')
@else
<x-site.portal active="notifications">
@include('notifications._list')
</x-site.portal>
@endif
@endsection
