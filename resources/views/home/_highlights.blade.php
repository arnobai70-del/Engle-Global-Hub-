{{--
    Assurance strip and promotion cards.

    Every assurance line describes how this website actually behaves. No
    rating, review count or availability claim is made, and none of the five
    items is a link: there is nothing further to open.
--}}
<section class="egho-section egho-section-tight">
    <div class="egho-shell">
        <ul class="egho-assurances">

            <li>
                <span class="egho-assurance-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 8h15l-3-3"/>
                        <path d="M20 16H5l3 3"/>
                    </svg>
                </span>

                <span class="egho-assurance-copy">
                    <strong>Compare fares</strong>
                    <small>
                        Every offer the provider returns, side by side.
                    </small>
                </span>
            </li>

            <li>
                <span class="egho-assurance-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3a9 9 0 1 0 9 9"/>
                        <path d="M12 7v5l3.5 2"/>
                    </svg>
                </span>

                <span class="egho-assurance-copy">
                    <strong>Live revalidation</strong>
                    <small>
                        The selected fare is rechecked before you
                        continue.
                    </small>
                </span>
            </li>

            <li>
                <span class="egho-assurance-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 4.5 6.6v4.9c0 4.6 3.1 7.6 7.5 8.5 4.4-.9 7.5-3.9 7.5-8.5V6.6z"/>
                        <path d="m9 12 2.2 2.2L15.5 10"/>
                    </svg>
                </span>

                <span class="egho-assurance-copy">
                    <strong>Secure account steps</strong>
                    <small>
                        Search, travellers, review and status in one
                        account.
                    </small>
                </span>
            </li>

            <li>
                <span class="egho-assurance-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 6h10M9 12h10M9 18h10"/>
                        <path d="m4 6 1.4 1.4L8 5"/>
                        <path d="m4 12 1.4 1.4L8 11"/>
                        <path d="m4 18 1.4 1.4L8 17"/>
                    </svg>
                </span>

                <span class="egho-assurance-copy">
                    <strong>Status you can check</strong>
                    <small>
                        Every booking shows its own order status.
                    </small>
                </span>
            </li>

            <li>
                <span class="egho-assurance-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="m8.5 12.2 2.3 2.3 4.7-5"/>
                    </svg>
                </span>

                <span class="egho-assurance-copy">
                    <strong>Honest availability</strong>
                    <small>
                        A service is listed only when its provider is
                        configured.
                    </small>
                </span>
            </li>

        </ul>
    </div>
</section>

<section class="egho-section">
    <div class="egho-shell">
        <div class="egho-promos">

            @foreach ($promoBanners as $promo)
                @feature($promo['feature'])
                    @php $promoLink = $serviceLink($promo['feature']); @endphp

                    <div
                        class="egho-promo"
                        style="--egho-promo-image: url('{{ $promo['image'] }}')"
                    >
                        <span class="egho-promo-badge">{{ $promo['badge'] }}</span>

                        {{--
                            Promotion cards sit directly under the hero h1 with
                            no section heading of their own, so they are h2.
                            Using h3 here skipped a heading level before the
                            first section h2.
                        --}}
                        <h2>{{ $promo['title'] }}</h2>

                        <p>{{ $promo['copy'] }}</p>

                        @if ($promoLink)
                            <a href="{{ $promoLink }}" class="egho-promo-cta">
                                {{ $promo['cta'] }}
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 12h13M13 7l5 5-5 5"/>
                                </svg>
                            </a>
                        @else
                            <span class="egho-promo-cta">
                                {{ $promo['cta'] }}
                            </span>
                        @endif
                    </div>
                @endfeature
            @endforeach

            {{-- Work Visa call to action, with the panel's checklist. --}}
            <div
                id="work-visa"
                class="egho-promo is-workvisa"
                style="--egho-promo-image: url('https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1400&q=85')"
            >
                <span class="egho-promo-badge">Work Visa</span>

                <h2>Work Visa Processing</h2>

                <p>Start your global career.</p>

                <ul class="egho-promo-list">
                    <li>
                        <span aria-hidden="true">&#10003;</span>
                        Job Visa
                    </li>
                    <li>
                        <span aria-hidden="true">&#10003;</span>
                        Skilled Worker Visa
                    </li>
                    <li>
                        <span aria-hidden="true">&#10003;</span>
                        Employer Sponsored Visa
                    </li>
                    <li>
                        <span aria-hidden="true">&#10003;</span>
                        Document Assistance
                    </li>
                </ul>

                @feature('visa')
                    <a
                        href="{{ route('work-visa.apply') }}"
                        class="egho-promo-cta"
                    >
                        Apply Now
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 12h13M13 7l5 5-5 5"/>
                        </svg>
                    </a>
                @else
                    {{--
                        The work visa route is hidden with the Visa feature, so
                        there is no reachable destination and the call to action
                        is shown as a status chip instead of a dead link.
                    --}}
                    <span class="egho-promo-cta">
                        Coming soon
                    </span>
                @endfeature
            </div>

        </div>
    </div>
</section>
