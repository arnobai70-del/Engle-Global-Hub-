@extends('layouts.auth')

@section('title', 'Login | Eagle Global Hub LTD')
@section('hero-title', 'Welcome Back!')
@section('hero-description', 'Login to continue your travel experience.')

@section('content')
    <div class="egho-auth-head">
        <span class="egho-eyebrow">{{ __('WELCOME BACK') }}</span>
        <h2>{{ __('Login') }}</h2>
        <p>{{ __('Sign in to continue to your Eagle Global Hub LTD account.') }}</p>
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="egho-auth-form">
        @csrf

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
                autofocus
            >
        </label>

        <label class="egho-field" for="password">
            <span>{{ __('Password') }}</span>

            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                placeholder="{{ __('Enter your password') }}"
                required
            >
        </label>

        <div class="egho-auth-row">
            <label class="egho-check-row">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                >

                <span>{{ __('Remember me') }}</span>
            </label>

            <a href="{{ route('password.request') }}">
                {{ __('Forgot password?') }}
            </a>
        </div>

        <button type="submit" class="egho-btn egho-btn-primary egho-auth-submit">
            {{ __('Login') }}
        </button>

        <p class="egho-auth-alt">
            {{ __('Don\'t have an account?') }}
            <a href="{{ route('register') }}">{{ __('Create account') }}</a>
        </p>
    </form>

    <section class="sg-login-roles" aria-labelledby="login-roles">
        <h2 id="login-roles">{{ __('One secure sign-in for everyone') }}</h2>
        <ul>
            <li><strong>{{ __('Student Login') }}</strong> {{ __('Opens your Student Portal: applications, documents, appointments and messages.') }}</li>
            <li><strong>{{ __('Agent Login') }}</strong> {{ __('Approved agents and Egabe B2B partners reach the Agent Portal.') }} <a href="{{ route('b2b.register') }}">{{ __('Become a partner') }}</a></li>
            <li><strong>{{ __('Tourist Login') }}</strong> {{ __('Travellers see their enquiries, bookings and notifications.') }}</li>
        </ul>
    </section>
@endsection
