@php
    $isAdminDashboard = auth()->user()->hasAnyRole(['admin', 'super-admin']);
@endphp

@extends($isAdminDashboard ? 'layouts.admin' : 'layouts.site')

@section('title', 'Dashboard')
@section('body_class', 'dashboard-body')
@section('page-class', 'admin-page')

@section('content')
    @if ($isAdminDashboard)
        @include('dashboard.admin')
    @else
        @include('dashboard.customer')
    @endif
@endsection
