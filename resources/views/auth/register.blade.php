@extends('layouts.auth')

@section('title', 'Create Account | Eagle Global Hub LTD')
@section('hero-title', 'Explore The World With Eagle Global Hub LTD')
@section('hero-description', 'Create your account and start planning your next journey.')

@section('content')
    <div class="egho-auth-head">
        <span class="egho-eyebrow">{{ __('GET STARTED') }}</span>
        <h2>{{ __('Create Account') }}</h2>
        <p>{{ __('Join us and start your journey.') }}</p>
    </div>

    <form method="POST" action="{{ route('register.store') }}" class="egho-auth-form">
        @csrf

        <label class="egho-field" for="name">
            <span>{{ __('Full Name') }}</span>

            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                autocomplete="name"
                placeholder="{{ __('Your full name') }}"
                required
                autofocus
            >
        </label>

        <label class="egho-field" for="email">
            <span>{{ __('Email Address') }}</span>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="email"
                placeholder="you@example.com"
                required
            >
        </label>

        <label class="egho-field" for="password">
            <span>{{ __('Password') }}</span>

            <input
                id="password"
                type="password"
                name="password"
                autocomplete="new-password"
                placeholder="{{ __('Create a password') }}"
                required
            >
        </label>

        <label class="egho-field" for="password_confirmation">
            <span>{{ __('Confirm Password') }}</span>

            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
                placeholder="{{ __('Confirm your password') }}"
                required
            >
        </label>

        <button type="submit" class="egho-btn egho-btn-primary egho-auth-submit">
            {{ __('Create Account') }}
        </button>

        <p class="egho-auth-alt">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}">{{ __('Login') }}</a>
        </p>
    </form>
@endsection
