@extends('layouts.site')

@section('title', __('Access denied'))
@section('robots', 'noindex,follow')
@section('body_class', 'public-page-body')

@section('content')
    @include('errors._page', ['code' => 403, 'title' => __('Access denied'), 'message' => __('You do not have permission to open this page. If you think this is a mistake, sign in with the right account or contact us.'), 'back' => 1])
@endsection
