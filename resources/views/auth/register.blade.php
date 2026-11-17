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

    @php
        $accountTypes = [
            'student' => ['icon' => 'cap', 'title' => __('Student'), 'text' => __('Study in Malaysia or Singapore: track your application, documents and messages.')],
            'traveller' => ['icon' => 'plane', 'title' => __('Traveller'), 'text' => __('Visa, air tickets, hotels and tours: follow your enquiries and bookings.')],
            'agent' => ['icon' => 'group', 'title' => __('Agent / B2B partner'), 'text' => __('Refer students and clients. Your partner application is reviewed by our team.')],
        ];
        $selectedType = old('account_type', request('type'));
    @endphp

    <form method="POST" action="{{ route('register.store') }}" class="egho-auth-form">
        @csrf

        <fieldset class="sg-account-types">
            <legend>{{ __('I am creating an account as a') }}</legend>
            @foreach($accountTypes as $value => $type)
                <label class="sg-account-type">
                    <input type="radio" name="account_type" value="{{ $value }}" @checked($selectedType === $value) required>
                    <span class="sg-account-type-card">
                        <x-site.art :name="$type['icon']" />
                        <span><strong>{{ $type['title'] }}</strong><small>{{ $type['text'] }}</small></span>
                    </span>
                </label>
            @endforeach
        </fieldset>

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
