@extends('layouts.site')

@section('title', __('Your session expired'))
@section('robots', 'noindex,follow')
@section('body_class', 'public-page-body')

@section('content')
    @include('errors._page', ['code' => 419, 'title' => __('Your session expired'), 'message' => __('The form was open for too long, so it was not sent for your security. Please go back, refresh the page and submit it again.'), 'back' => 1])
@endsection
