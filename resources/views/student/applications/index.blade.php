@extends('layouts.site')
@section('title', 'My Study Applications')
@section('robots', 'noindex,nofollow')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<x-site.portal active="applications" kind="student">
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-card">
            <h1>{{ __('My study applications') }}</h1>
            @if($student === null)
                <p>{{ __('No student profile is linked to your account yet. If you have contacted us about studying abroad, a consultant will link your application once your file is opened.') }}</p>
                @feature('student-consultancy')<p><a class="egho-btn egho-btn-primary" href="{{ route('consultancy.singapore') }}">{{ __('Request a free consultation') }}</a></p>@endfeature
            @elseif($applications->isEmpty())
                <p>{{ __('Your student profile has no applications yet. Your consultant will open one after your consultation.') }}</p>
            @else
                <ul>
                    @foreach($applications as $application)
                        <li>
                            <a href="{{ route('student.applications.show', $application) }}"><strong>{{ $application->reference }}</strong></a>
                            — {{ $application->destinationCountry->name }}@if($application->institution), {{ $application->institution->name }}@endif
                            — {{ __($application->status->stage()) }}: {{ __($application->status->label()) }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</main>
</x-site.portal>
@endsection
