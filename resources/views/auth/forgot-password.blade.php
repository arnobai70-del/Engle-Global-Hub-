@extends('layouts.auth')

@section('title', 'Forgot Password | Eagle Global Hub LTD')

@section('hero-title', 'Forgot Your Password?')

@section(
    'hero-description',
    'No problem. Enter your email address and we will send you a password reset link.'
)

@section('content')
    <div class="egho-auth-head">
        <span class="egho-eyebrow">ACCOUNT RECOVERY</span>

        <h2>Forgot Password</h2>

        <p>
            Enter the email address associated with your Eagle Global Hub LTD account.
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('password.email') }}"
        class="egho-auth-form"
    >
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

        <button type="submit" class="egho-btn egho-btn-primary egho-auth-submit">
            Send Password Reset Link
        </button>

        <p class="egho-auth-alt">
            Remember your password?

            <a href="{{ route('login') }}">
                Back to Login
            </a>
        </p>
    </form>
@endsection
