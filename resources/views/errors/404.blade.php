@extends('layouts.site')

@section('title', 'Page Not Found')
@section('body_class', 'public-page-body')

@section('content')

    <main class="egho-error">

        <div class="egho-shell">

            <section class="egho-error-card">
                <span class="egho-error-code">
                    404
                </span>

                <h1>
                    Page not found
                </h1>

                <p>
                    The page you requested is not available. You can return home
                    or continue through the currently available account services.
                </p>

                <div class="egho-error-actions">
                    <a
                        href="{{ route('home') }}"
                        class="egho-btn egho-btn-primary"
                    >
                        Home
                    </a>

                    @auth
                        @feature('dashboard')
                            <a
                                href="{{ route('dashboard') }}"
                                class="egho-btn egho-btn-ghost"
                            >
                                Dashboard
                            </a>
                        @endfeature
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="egho-btn egho-btn-ghost"
                        >
                            Login
                        </a>
                    @endauth
                </div>
            </section>

        </div>

    </main>

@endsection
