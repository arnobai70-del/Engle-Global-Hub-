@extends('layouts.auth')

@section('title', 'Confirm Password | Eagle Global Hub LTD')
@section('hero-title', 'Confirm Your Password')
@section('hero-description', 'Re-enter your password before continuing to a protected account action.')

@section('content')
    <div class="egho-auth-head">
        <span class="egho-eyebrow">{{ __('SECURITY CHECK') }}</span>
        <h2>{{ __('Confirm Password') }}</h2>
        <p>
            {{ __('For your security, please confirm your password before continuing.') }}
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('password.confirm.store') }}"
        class="egho-auth-form"
    >
        @csrf

        <label class="egho-field" for="password">
            <span>{{ __('Password') }}</span>

            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                placeholder="{{ __('Enter your password') }}"
                required
                autofocus
            >
        </label>

        <button type="submit" class="egho-btn egho-btn-primary egho-auth-submit">
            {{ __('Confirm Password') }}
        </button>

        <p class="egho-auth-alt">
            <a href="{{ route('dashboard') }}">
                {{ __('Return to dashboard') }}
            </a>
        </p>
    </form>
@endsection
