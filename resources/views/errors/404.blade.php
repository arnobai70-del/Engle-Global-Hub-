@extends('layouts.site')

@section('title', __('Page not found'))
@section('robots', 'noindex,follow')
@section('body_class', 'public-page-body')

@section('content')
    @include('errors._page', ['code' => 404, 'title' => __('Page not found'), 'message' => __('The page you requested is not available. It may have moved, or it may not be published yet.'), 'back' => 1])
@endsection
