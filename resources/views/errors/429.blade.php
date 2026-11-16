@extends('layouts.site')

@section('title', __('Too many attempts'))
@section('robots', 'noindex,follow')
@section('body_class', 'public-page-body')

@section('content')
    @include('errors._page', ['code' => 429, 'title' => __('Too many attempts'), 'message' => __('You have tried this too many times in a short period. Please wait a minute and try again.'), 'back' => 1])
@endsection
