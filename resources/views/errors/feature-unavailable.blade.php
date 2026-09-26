@extends('layouts.site')

@section('title', 'Feature Unavailable')
@section('body_class', 'public-page-body')

@section('content')

    <main class="egho-error">

        <div class="egho-shell">

            <section
                class="egho-error-card"
                aria-labelledby="feature-unavailable-title"
            >
                <span class="egho-error-code">
                    UNAVAILABLE
                </span>

                <h1 id="feature-unavailable-title">
                    Feature unavailable
                </h1>

                <p>{{ $message }}</p>

                <div class="egho-error-actions">
                    <a
                        href="{{ route('home') }}"
                        class="egho-btn egho-btn-primary"
                    >
                        Return home
                    </a>
                </div>
            </section>

        </div>

    </main>

@endsection
