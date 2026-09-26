@extends('layouts.site')

@section('title', 'Work Visa Processing')

@section(
    'meta_description',
    'Work visa processing information and application preparation from Eagle Global Hub LTD.'
)

@section('body_class', 'egho-page-body')

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            <section class="egho-contact-hero">
                <div>
                    <span class="egho-eyebrow">WORK VISA PROCESSING</span>
                    <h1>Your global career starts here</h1>
                    <p>
                        Work visa preparation support for job visas, work
                        permits and employer-sponsored routes. Eligibility and
                        the final decision always belong to the destination
                        immigration authority, never to this website.
                    </p>

                    <div class="egho-actions" style="margin-top:18px">
                        <a
                            href="{{ route('work-visa.apply') }}"
                            class="egho-btn egho-btn-primary"
                        >
                            Apply now
                        </a>
                        <a
                            href="{{ route('visa.index') }}"
                            class="egho-btn egho-btn-ghost"
                        >
                            Visa information
                        </a>
                    </div>
                </div>

                <div class="egho-contact-hero-media">
                    <img
                        src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1600&q=85"
                        srcset="
                            https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1200&q=85 1x,
                            https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=2000&q=85 2x
                        "
                        alt=""
                        loading="lazy"
                        width="1600"
                        height="1067"
                    >
                </div>
            </section>

            <section class="egho-notice" role="status">
                <span class="egho-notice-status">Not Configured</span>
                <h2>Work visa processing is not configured</h2>
                <p>
                    Work visa applications are handled with a vetted immigration
                    partner, document checklists and a payment method. Until that
                    screening and submission path is enabled, this page cannot
                    accept an application, upload a document or charge a fee.
                </p>
                <p>
                    Nothing on this page creates an application, and no
                    processing time or outcome is promised anywhere on this
                    website.
                </p>
                <div class="egho-actions">
                    <a
                        href="{{ route('home') }}"
                        class="egho-btn egho-btn-ghost"
                    >
                        Back to travel services
                    </a>
                </div>
            </section>

            {{--
                Service outline. These are the support categories the agency
                intends to offer; they are descriptions, not promises of an
                outcome, and they are listed in every environment.
            --}}
            <ul class="egho-benefits" style="margin-top:20px">
                @foreach ([
                    [
                        'title' => 'Job Visa & Work Permits',
                        'note' => 'Employer documents and permit paperwork prepared with you.',
                    ],
                    [
                        'title' => 'Skilled Worker Visa',
                        'note' => 'Points-based routes, qualification and language paperwork.',
                    ],
                    [
                        'title' => 'Employer Sponsored Visa',
                        'note' => 'Sponsorship letters, contracts and job-offer checks.',
                    ],
                    [
                        'title' => 'Document Assistance',
                        'note' => 'Translation, attestation and checklist reviews.',
                    ],
                ] as $benefit)
                    <li class="egho-benefit">
                        <span class="egho-benefit-icon" aria-hidden="true">
                            &#10003;
                        </span>
                        <span>
                            <strong>{{ $benefit['title'] }}</strong>
                            <small>{{ $benefit['note'] }}</small>
                        </span>
                    </li>
                @endforeach
            </ul>

            {{--
                Design preview only, local development only.

                The destinations below preview the destination-tile layout. They
                are not a list of routes this website can process, and no
                processing time or success rate is shown for them.

                Wiring point: a configured immigration partner supplies the
                destinations it actually supports, with its own document
                requirements and fee schedule.
            --}}
            @if (app()->environment('local'))
                <div class="egho-sample-note">
                    <span aria-hidden="true">&#9432;</span>
                    <span>
                        <strong>Layout preview.</strong>
                        Destination tiles are placeholders that preview the
                        layout. They are not a list of supported routes, and
                        processing times are decided by the destination
                        authority.
                    </span>
                </div>

                <section aria-label="Popular work visa destinations preview">
                    <h2 style="margin:0 0 14px;font-size:20px;color:#0b2545">
                        Popular Work Visa Destinations
                    </h2>

                    <div class="egho-country-tiles">
                        @foreach ([
                            [
                                'name' => 'Canada',
                                'note' => 'Sample destination',
                                'image' => 'https://images.unsplash.com/photo-1517935706615-2717063c2225?auto=format&fit=crop&w=1000&q=85',
                            ],
                            [
                                'name' => 'Australia',
                                'note' => 'Sample destination',
                                'image' => 'https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&w=1000&q=85',
                            ],
                            [
                                'name' => 'United Kingdom',
                                'note' => 'Sample destination',
                                'image' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=1000&q=85',
                            ],
                            [
                                'name' => 'Germany',
                                'note' => 'Sample destination',
                                'image' => 'https://images.unsplash.com/photo-1560969184-10fe8719e047?auto=format&fit=crop&w=1000&q=85',
                            ],
                            [
                                'name' => 'United States',
                                'note' => 'Sample destination',
                                'image' => 'https://images.unsplash.com/photo-1485871981521-5b1fd3805eee?auto=format&fit=crop&w=1000&q=85',
                            ],
                            [
                                'name' => 'United Arab Emirates',
                                'note' => 'Sample destination',
                                'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1000&q=85',
                            ],
                        ] as $destination)
                            <article class="egho-country-tile">
                                <span class="egho-sample-badge">Sample</span>
                                <img
                                    src="{{ $destination['image'] }}"
                                    alt=""
                                    loading="lazy"
                                    width="1000"
                                    height="625"
                                >
                                <div class="egho-country-tile-body">
                                    <strong>{{ $destination['name'] }}</strong>
                                    <small>{{ $destination['note'] }}</small>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <p class="egho-filter-note">
                This website prepares and checks paperwork. It does not decide
                eligibility, processing time or the outcome of any work visa
                application.
            </p>

        </div>
    </main>

@endsection
