{{--
    Service panels — the panel's second row.

    Each panel carries a checklist and exactly one action. Where the action
    leads to a page this website really has, it is a link; where the product
    does not exist yet, it is a status chip instead of a dead link.
--}}
<section class="egho-section egho-section-alt">
    <div class="egho-shell">
        <div class="egho-panels">

            @foreach ($servicePanels as $panel)
                @php
                    $panelLink = ($panel['work_visa'] ?? false)
                        ? null
                        : ($panel['service'] ? $serviceLink($panel['service']) : null);
                @endphp

                <div class="egho-panel {{ $panel['class'] }}">
                    <div>
                        <h3>{{ $panel['title'] }}</h3>

                        <p>{{ $panel['copy'] }}</p>

                        <ul class="egho-panel-list">
                            @foreach ($panel['items'] as $item)
                                <li>
                                    <span aria-hidden="true">&#10003;</span>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>

                        @if ($panel['work_visa'] ?? false)
                            @feature('visa')
                                <a
                                    href="{{ route('work-visa.apply') }}"
                                    class="egho-panel-cta"
                                >
                                    {{ $panel['cta'] }}
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M5 12h13M13 7l5 5-5 5"/>
                                    </svg>
                                </a>
                            @else
                                <span class="egho-panel-status">
                                    Available once the Visa feature is
                                    enabled
                                </span>
                            @endfeature
                        @elseif ($panelLink)
                            <a href="{{ $panelLink }}" class="egho-panel-cta">
                                {{ $panel['cta'] }}
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 12h13M13 7l5 5-5 5"/>
                                </svg>
                            </a>
                        @elseif ($panel['cta'])
                            <span class="egho-panel-status">
                                Available once the provider is configured
                            </span>
                        @else
                            <span class="egho-panel-status">
                                Not available yet
                            </span>
                        @endif
                    </div>

                    <div
                        class="egho-panel-media"
                        style="--egho-panel-image: url('{{ $panel['image'] }}')"
                        aria-hidden="true"
                    ></div>
                </div>
            @endforeach

        </div>
    </div>
</section>
