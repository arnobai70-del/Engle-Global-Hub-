@extends('layouts.site')

@section('title', 'Work Visa Application')

@section(
    'meta_description',
    'Work visa application preparation step for Eagle Global Hub LTD clients.'
)

@section('body_class', 'egho-page-body')

@section('content')

    <main class="egho-page">
        <div class="egho-shell">

            <header class="egho-page-head">
                <span class="egho-eyebrow">WORK VISA APPLICATION</span>
                <h1>Prepare a work visa application</h1>
                <p>
                    The visa category, document checklist and review steps an
                    application needs, in the order they are worked through.
                    Nothing is submitted from this page: no application, no
                    uploaded document and no fee is created until a vetted
                    immigration partner is connected. The final decision
                    always belongs to the destination immigration authority.
                </p>
            </header>

            <ol class="egho-steps" aria-label="Work visa application steps">
                @foreach ([
                    'Select Visa',
                    'Visa Category',
                    'Documents',
                    'Review',
                    'Payment',
                ] as $step)
                    <li @class(['is-active' => $loop->first])>{{ $step }}</li>
                @endforeach
            </ol>

            <section class="egho-notice" role="status">
                <span class="egho-notice-status">Not Configured</span>
                <h2>Work visa applications are not configured</h2>
                <p>
                    This step collects the details an application needs and then
                    hands them to a vetted immigration partner with a document
                    checklist and a payment method. Until that path is enabled,
                    no application is created, no personal information is
                    stored, and no fee can be charged.
                </p>
                <p>
                    Nothing typed on this page is submitted anywhere, and no
                    processing time or outcome is promised.
                </p>
                <div class="egho-actions">
                    <a
                        href="{{ route('work-visa.index') }}"
                        class="egho-btn egho-btn-ghost"
                    >
                        Back to work visa processing
                    </a>
                </div>
            </section>

            {{--
                Design preview only, local development only.

                The personal information form and the application summary below
                are static placeholders used to review the approved layout. Every
                field is disabled, nothing is submitted, and every figure is a
                sample.

                Wiring point: the chosen destination and category supply the
                summary, the document step produces a checklist, and the payment
                step charges the configured service fee before submission.
            --}}
            @if (app()->environment('local'))
                <div class="egho-sample-note">
                    <span aria-hidden="true">&#9432;</span>
                    <span>
                        <strong>Layout preview.</strong>
                        The form is disabled and the summary figures are samples.
                        Nothing on this page can be submitted, stored or charged.
                    </span>
                </div>

                <div class="egho-booking-layout">

                    <section class="egho-contact-card">
                        <h2>Personal information</h2>
                        <p class="egho-result-summary">
                            Placeholder applicant details. Real applications
                            collect these fields with document evidence and are
                            checked before anything is submitted.
                        </p>

                        <div class="egho-form-grid">
                            <label class="egho-field">
                                <span>Title</span>
                                <select disabled>
                                    <option>Select title</option>
                                </select>
                            </label>

                            <label class="egho-field">
                                <span>First name</span>
                                <input type="text" disabled>
                            </label>

                            <label class="egho-field">
                                <span>Last name</span>
                                <input type="text" disabled>
                            </label>

                            <label class="egho-field">
                                <span>Date of birth</span>
                                <input type="date" disabled>
                            </label>

                            <div class="egho-field">
                                <span>Gender</span>
                                <div class="egho-radio-row">
                                    @foreach (['Male', 'Female', 'Other'] as $gender)
                                        <label class="egho-radio">
                                            <input
                                                type="radio"
                                                name="sample_gender"
                                                disabled
                                            >
                                            <span>{{ $gender }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <label class="egho-field">
                                <span>Nationality</span>
                                <select disabled>
                                    <option>Select nationality</option>
                                </select>
                            </label>

                            <label class="egho-field egho-field-wide">
                                <span>Current address</span>
                                <input type="text" disabled>
                            </label>

                            <label class="egho-field">
                                <span>Email address</span>
                                <input type="email" disabled>
                            </label>

                            <label class="egho-field">
                                <span>Phone number</span>
                                <input type="tel" disabled>
                            </label>
                        </div>

                        <p class="egho-filter-note">
                            Sample fields only. No applicant record is created and
                            no data leaves this page.
                        </p>
                    </section>

                    <aside class="egho-summary-card">
                        <h2>Application summary</h2>

                        <div class="egho-summary-hotel">
                            <div class="egho-summary-thumb">
                                <img
                                    src="https://images.unsplash.com/photo-1517935706615-2717063c2225?auto=format&fit=crop&w=400&q=85"
                                    alt=""
                                    loading="lazy"
                                    width="400"
                                    height="400"
                                >
                            </div>
                            <div>
                                <strong>Sample destination</strong>
                                <small>Work visa &middot; sample category</small>
                            </div>
                        </div>

                        <div class="egho-summary-row">
                            <span>Visa category</span>
                            <span>Sample category</span>
                        </div>

                        <div class="egho-summary-row">
                            <span>Applicants</span>
                            <span>1 applicant</span>
                        </div>

                        <div class="egho-summary-row">
                            <span>Estimated processing</span>
                            <span>Sample range</span>
                        </div>

                        <div class="egho-summary-row">
                            <span>Service fee</span>
                            <span>BDT 75,000</span>
                        </div>

                        <div class="egho-summary-total">
                            <span>Total</span>
                            <strong>BDT 75,000</strong>
                        </div>

                        <div class="egho-summary-actions">
                            <button
                                type="button"
                                class="egho-btn egho-btn-primary"
                                disabled
                            >
                                Save and continue
                            </button>
                            <small>
                                Sample figures. Processing time is decided by the
                                destination authority, and no application is
                                created or charged from this page.
                            </small>
                        </div>
                    </aside>

                </div>
            @endif

        </div>
    </main>

@endsection
