@extends('layouts.auth')

@section('title', 'Create Account | Eagle Global Hub LTD')
@section('hero-title', 'Explore The World With Eagle Global Hub LTD')
@section('hero-description', 'Create your account and start planning your next journey.')

@section('content')
    <div class="egho-auth-head">
        <span class="egho-eyebrow">GET STARTED</span>
        <h2>Create Account</h2>
        <p>Join us and start your journey.</p>
    </div>

    <form method="POST" action="{{ route('register.store') }}" class="egho-auth-form">
        @csrf

        <label class="egho-field" for="name">
            <span>Full Name</span>

            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                autocomplete="name"
                placeholder="Your full name"
                required
                autofocus
            >
        </label>

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
            >
        </label>

        <label class="egho-field" for="password">
            <span>Password</span>

            <input
                id="password"
                type="password"
                name="password"
                autocomplete="new-password"
                placeholder="Create a password"
                required
            >
        </label>

        <label class="egho-field" for="password_confirmation">
            <span>Confirm Password</span>

            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
                placeholder="Confirm your password"
                required
            >
        </label>

        <button type="submit" class="egho-btn egho-btn-primary egho-auth-submit">
            Create Account
        </button>

        <p class="egho-auth-alt">
            Already have an account?
            <a href="{{ route('login') }}">Login</a>
        </p>
    </form>
@endsection
