@extends('layouts.auth')

@section('title', 'Confirm Password | Eagle Global Hub LTD')
@section('hero-title', 'Confirm Your Password')
@section('hero-description', 'Re-enter your password before continuing to a protected account action.')

@section('content')
    <div class="egho-auth-head">
        <span class="egho-eyebrow">SECURITY CHECK</span>
        <h2>Confirm Password</h2>
        <p>
            For your security, please confirm your password before continuing.
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('password.confirm.store') }}"
        class="egho-auth-form"
    >
        @csrf

        <label class="egho-field" for="password">
            <span>Password</span>

            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                placeholder="Enter your password"
                required
                autofocus
            >
        </label>

        <button type="submit" class="egho-btn egho-btn-primary egho-auth-submit">
            Confirm Password
        </button>

        <p class="egho-auth-alt">
            <a href="{{ route('dashboard') }}">
                Return to dashboard
            </a>
        </p>
    </form>
@endsection
