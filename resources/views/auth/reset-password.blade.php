@extends('layouts.auth')

@section('title', 'Reset Password | Eagle Global Hub LTD')

@section('hero-title', 'Create A New Password')

@section(
    'hero-description',
    'Choose a secure new password to regain access to your Eagle Global Hub LTD account.'
)

@section('content')
    <div class="egho-auth-head">
        <span class="egho-eyebrow">{{ __('SECURE YOUR ACCOUNT') }}</span>

        <h2>{{ __('Reset Password') }}</h2>

        <p>
            {{ __('Enter your email address and choose a new password.') }}
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('password.update') }}"
        class="egho-auth-form"
    >
        @csrf

        <input
            type="hidden"
            name="token"
            value="{{ $request->route('token') }}"
        >

        <label class="egho-field" for="email">
            <span>{{ __('Email Address') }}</span>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                autocomplete="email"
                placeholder="you@example.com"
                required
                autofocus
            >
        </label>

        <label class="egho-field" for="password">
            <span>{{ __('New Password') }}</span>

            <input
                id="password"
                type="password"
                name="password"
                autocomplete="new-password"
                placeholder="{{ __('Enter new password') }}"
                required
            >
        </label>

        <label class="egho-field" for="password_confirmation">
            <span>{{ __('Confirm New Password') }}</span>

            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
                placeholder="{{ __('Confirm new password') }}"
                required
            >
        </label>

        <button type="submit" class="egho-btn egho-btn-primary egho-auth-submit">
            {{ __('Reset Password') }}
        </button>

        <p class="egho-auth-alt">
            {{ __('Remember your password?') }}

            <a href="{{ route('login') }}">
                {{ __('Back to Login') }}
            </a>
        </p>
    </form>
@endsection
