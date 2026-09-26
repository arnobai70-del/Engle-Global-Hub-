@extends('layouts.auth')

@section('title', 'Verify Email | Eagle Global Hub LTD')

@section('hero-title', 'Verify Your Email')

@section(
    'hero-description',
    'Confirm your email address to secure your account and continue your Eagle Global Hub LTD journey.'
)

@section('content')
    <div class="egho-auth-head">
        <span class="egho-eyebrow">EMAIL VERIFICATION</span>

        <h2>Check Your Inbox</h2>

        <p>
            We sent a verification link to your registered email address.
            Please verify your email before continuing.
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="auth-alert auth-alert-success" role="status">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="egho-auth-form">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <button type="submit" class="egho-btn egho-btn-primary egho-auth-submit">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="egho-btn egho-btn-ghost egho-auth-submit">
                Logout
            </button>
        </form>
    </div>
@endsection
