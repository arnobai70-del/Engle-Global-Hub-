{{--
    Why choose, and the panel's customer comments.

    The comments are SAMPLE CONTENT: this application stores no review data at
    all, so rather than publishing invented social proof the section renders
    only in the local development environment and says out loud that it is
    sample content. It never renders in production, and no rating is shown.
--}}
<section class="egho-section">
    <div class="egho-shell">

        <div class="egho-section-head">
            <div>
                <span class="egho-eyebrow">The difference</span>
                <h2>Why Choose Eagle Global Hub?</h2>
                <p>
                    Six things this website does, each of them checked
                    by the application's own test suite.
                </p>
            </div>
        </div>

        <ul class="egho-why">
            @foreach ($whyChoose as $reason)
                <li>
                    <span class="egho-why-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">{!! $reason['icon'] !!}</svg>
                    </span>

                    <span>
                        <strong>{{ $reason['title'] }}</strong>
                        <small>{{ $reason['copy'] }}</small>
                    </span>
                </li>
            @endforeach
        </ul>

    </div>
</section>

@if (app()->environment('local'))
    <section class="egho-section egho-section-alt">
        <div class="egho-shell">

            <div class="egho-section-head">
                <div>
                    <span class="egho-eyebrow">Testimonials</span>
                    <h2>
                        What Our Customers Say
                        <span class="egho-sample-badge">Sample data</span>
                    </h2>
                    <p>
                        Layout preview only. These comments are not read
                        from any review record and are not shown outside
                        the local development environment.
                    </p>
                </div>
            </div>

            <div class="egho-rail" data-egho-rail>
                <div class="egho-voices is-rail" data-egho-rail-track>
                    @foreach ([
                        [
                            'quote' => 'Excellent service! Got my work visa processed smoothly through Eagle Global Hub.',
                            'name' => 'Rahim Uddin',
                            'role' => 'Work Visa Customer',
                        ],
                        [
                            'quote' => 'Great flight deals and an easy booking flow from search to traveller details.',
                            'name' => 'Nusrat Jahan',
                            'role' => 'Flight Booking Customer',
                        ],
                        [
                            'quote' => 'Very clear about what is available and what is not. That is what I wanted.',
                            'name' => 'Tanvir Ahmed',
                            'role' => 'Tour Package Customer',
                        ],
                    ] as $voice)
                        <figure class="egho-voice" data-egho-rail-item>
                            <blockquote>
                                <p>&ldquo;{{ $voice['quote'] }}&rdquo;</p>
                            </blockquote>

                            <figcaption class="egho-voice-person">
                                <span class="egho-voice-avatar" aria-hidden="true">
                                    {{ strtoupper(substr($voice['name'], 0, 1)) }}
                                </span>

                                <span>
                                    <strong>{{ $voice['name'] }}</strong>
                                    <small>{{ $voice['role'] }}</small>
                                </span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>

        </div>
    </section>
@endif
