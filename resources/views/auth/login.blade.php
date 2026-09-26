@extends('layouts.auth')

@section('title', 'Login | Eagle Global Hub LTD')
@section('hero-title', 'Welcome Back!')
@section('hero-description', 'Login to continue your travel experience.')

@section('content')
    <div class="egho-auth-head">
        <span class="egho-eyebrow">WELCOME BACK</span>
        <h2>Login</h2>
        <p>Sign in to continue to your Eagle Global Hub LTD account.</p>
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="egho-auth-form">
        @csrf

        <label class="egho-field" for="email">
            <span>Email Address</span>

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
            <span>Password</span>

            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                placeholder="Enter your password"
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

                <span>Remember me</span>
            </label>

            <a href="{{ route('password.request') }}">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="egho-btn egho-btn-primary egho-auth-submit">
            Login
        </button>

        <p class="egho-auth-alt">
            Don't have an account?
            <a href="{{ route('register') }}">Create account</a>
        </p>
    </form>
@endsection
