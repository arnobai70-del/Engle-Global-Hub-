/*
 * Flight search results — Screen 2.
 *
 * What this file does
 * -------------------
 * 1. Fills the search summary bar from the search that was actually
 *    submitted, so the bar can never describe a search that did not run.
 * 2. Enables the filter rail once offers are on screen, and filters those
 *    offers in the browser.
 * 3. Sorts the returned offers when the visitor asks for a different order.
 * 4. Marks the lowest total fare among the returned options.
 *
 * What this file deliberately does NOT do
 * ---------------------------------------
 * It never fetches, re-searches or replaces a fare. Every control it enables
 * only narrows or re-orders the options the provider already returned for
 * this search, and the wording in the rail says exactly that. The offers
 * themselves, the selection step and the booking steps stay owned by the
 * flight search script in the Vite bundle.
 *
 * Why values are read from `data-` attributes
 * ------------------------------------------
 * Stop counts, durations and timestamps are mirrored onto the rendered cards
 * by the flight search script. Reading those attributes means a filter never
 * has to parse a localised display string, so a window can never disagree
 * with the text beside it.
 */
(function () {
    'use strict';

    var form = document.querySelector('[data-flight-search-form]');
    var results = document.querySelector('[data-flight-results]');

    if (!form || !results) {
        return;
    }

    var EMPTY = '\u2014';

    /* ------------------------------------------------------------------ *
     * Elements
     * ------------------------------------------------------------------ */

    var summary = document.querySelector('[data-flight-summary]');
    var summaryOrigin = document.querySelector('[data-flight-summary-origin]');
    var summaryDestination = document.querySelector('[data-flight-summary-destination]');
    var summaryDates = document.querySelector('[data-flight-summary-dates]');
    var summaryTravellers = document.querySelector('[data-flight-summary-travellers]');
    var summaryModify = document.querySelector('[data-flight-summary-modify]');

    var rail = document.querySelector('[data-flight-filters]');
    var sortSelect = document.querySelector('[data-flight-sort]');
    var countOutput = document.querySelector('[data-flight-count]');
    var airlineHost = rail ? rail.querySelector('[data-flight-airline-filters]') : null;
    var note = rail ? rail.querySelector('[data-flight-filter-note]') : null;
    var resetButton = rail ? rail.querySelector('[data-flight-filter-reset]') : null;
    var stopBoxes = rail
        ? Array.prototype.slice.call(rail.querySelectorAll('[data-flight-stop-filter]'))
        : [];

    var priceMinInput = rail ? rail.querySelector('[data-flight-price-min]') : null;
    var priceMaxInput = rail ? rail.querySelector('[data-flight-price-max]') : null;
    var priceMinLabel = rail ? rail.querySelector('[data-flight-price-min-label]') : null;
    var priceMaxLabel = rail ? rail.querySelector('[data-flight-price-max-label]') : null;

    var departureMinInput = rail ? rail.querySelector('[data-flight-departure-min]') : null;
    var departureMaxInput = rail ? rail.querySelector('[data-flight-departure-max]') : null;
    var departureWindow = rail ? rail.querySelector('[data-flight-departure-window]') : null;

    var arrivalMinInput = rail ? rail.querySelector('[data-flight-arrival-min]') : null;
    var arrivalMaxInput = rail ? rail.querySelector('[data-flight-arrival-max]') : null;
    var arrivalWindow = rail ? rail.querySelector('[data-flight-arrival-window]') : null;

    var idleNote = note ? note.textContent.trim() : '';
    var activeNote =
        'Filtering and sorting run in your browser over the options this search'
        + ' returned. They never request, add or replace a fare, and the'
        + " provider's own order is used until you choose a different sort.";

    /* ------------------------------------------------------------------ *
     * State
     * ------------------------------------------------------------------ */

    var records = [];
    var selectedStops = [];
    var selectedCarriers = [];
    var airlineSignature = null;

    /*
        Slider positions the visitor set themselves.

        A slider nobody has touched is pinned to the edge of the current result
        set. Without this, a revalidation that brings back a cheaper fare would
        recompute the slider bounds while the old position stayed where it was,
        quietly hiding that cheaper fare even though the visitor never chose to
        exclude anything.
    */
    var rangesTouched = {
        priceMin: false,
        priceMax: false,
        departureMin: false,
        departureMax: false,
        arrivalMin: false,
        arrivalMax: false,
    };
    var priceBounds = null;
    var timeBounds = null;
    var sort = 'provider';
    var busy = false;
    var forceNext = false;
    var renderTimer = null;

    /* ------------------------------------------------------------------ *
     * Formatting helpers
     * ------------------------------------------------------------------ */

    var localeSeparators = (function () {
        var separators = { group: '\u00a0', decimal: '.' };

        try {
            new Intl.NumberFormat(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
                .formatToParts(1234567.89)
                .forEach(function (part) {
                    if (part.type === 'group') {
                        separators.group = part.value;
                    }

                    if (part.type === 'decimal') {
                        separators.decimal = part.value;
                    }
                });
        } catch (error) {
            /* Fall back to the defaults above. */
        }

        return separators;
    })();

    var parseMoney = function (text) {
        var value = String(text || '').trim();

        if (!value) {
            return { amount: null, currency: null };
        }

        var code = value.match(/[A-Z]{3}/);
        var digits = value.replace(/[A-Z]{3}/, '');

        if (localeSeparators.group) {
            digits = digits.split(localeSeparators.group).join('');
        }

        if (localeSeparators.decimal !== '.') {
            digits = digits.split(localeSeparators.decimal).join('.');
        }

        digits = digits.replace(/[^0-9.]/g, '');

        var amount = Number(digits);

        return {
            amount: Number.isFinite(amount) ? amount : null,
            currency: code ? code[0] : null,
        };
    };

    var formatMoney = function (amount, currency) {
        if (!Number.isFinite(amount)) {
            return EMPTY;
        }

        if (!currency) {
            return String(Math.round(amount));
        }

        try {
            return new Intl.NumberFormat(undefined, {
                style: 'currency',
                currency: currency,
                currencyDisplay: 'code',
                maximumFractionDigits: 0,
            }).format(amount);
        } catch (error) {
            return currency + ' ' + Math.round(amount);
        }
    };

    var formatHour = function (hour) {
        try {
            return new Intl.DateTimeFormat(undefined, {
                hour: 'numeric',
                minute: '2-digit',
            }).format(new Date(2000, 0, 1, Number(hour) % 24, 0));
        } catch (error) {
            return String(hour) + ':00';
        }
    };

    var formatDate = function (value) {
        if (!value) {
            return '';
        }

        var parsed = new Date(value + 'T00:00:00');

        if (Number.isNaN(parsed.getTime())) {
            return value;
        }

        try {
            return new Intl.DateTimeFormat(undefined, {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            }).format(parsed);
        } catch (error) {
            return value;
        }
    };

    var durationToMinutes = function (value) {
        if (typeof value !== 'string') {
            return null;
        }

        var match = value.match(/^PT(?:(\d+)H)?(?:(\d+)M)?$/);

        if (!match) {
            return null;
        }

        return Number(match[1] || 0) * 60 + Number(match[2] || 0);
    };

    var displayedDurationToMinutes = function (text) {
        var hours = /(\d+)\s*h/.exec(String(text || ''));
        var minutes = /(\d+)\s*m/.exec(String(text || ''));

        if (!hours && !minutes) {
            return null;
        }

        return Number(hours ? hours[1] : 0) * 60 + Number(minutes ? minutes[1] : 0);
    };

    var hourOf = function (value) {
        if (!value) {
            return null;
        }

        var parsed = new Date(value);

        if (Number.isNaN(parsed.getTime())) {
            return null;
        }

        return parsed.getHours();
    };

    /* ------------------------------------------------------------------ *
     * Reading the rendered offers
     * ------------------------------------------------------------------ */

    var scan = function () {
        records = Array.prototype.slice
            .call(results.querySelectorAll('.flight-offer-card'))
            .map(function (node, index) {
                var priceNode = node.querySelector('.flight-offer-price strong');
                var ownerNode = node.querySelector('.flight-owner-name');
                var slices = Array.prototype.slice.call(
                    node.querySelectorAll('.flight-offer-slice')
                );

                var money = parseMoney(priceNode ? priceNode.textContent : '');

                var stops = slices.map(function (slice) {
                    var declared = slice.dataset ? slice.dataset.stops : '';

                    if (declared !== undefined && declared !== '') {
                        var parsed = Number(declared);

                        if (Number.isFinite(parsed)) {
                            return parsed;
                        }
                    }

                    return Math.max(
                        slice.querySelectorAll('.flight-offer-segment').length - 1,
                        0
                    );
                });

                var minutes = slices.reduce(function (total, slice) {
                    var own = slice.dataset
                        ? durationToMinutes(slice.dataset.duration)
                        : null;

                    if (own === null) {
                        var durationNode = slice.querySelector('.flight-slice-duration');

                        own = durationNode
                            ? displayedDurationToMinutes(durationNode.textContent)
                            : null;
                    }

                    return total + (own === null ? 0 : own);
                }, 0);

                var firstSlice = slices.length ? slices[0] : null;

                var departureNode = firstSlice
                    ? firstSlice.querySelector(
                        '.flight-segment-point:not(.flight-segment-point-arrival)'
                    )
                    : null;

                var arrivalNode = firstSlice
                    ? firstSlice.querySelector('.flight-segment-point-arrival')
                    : null;

                return {
                    node: node,
                    index: index,
                    carrier: ownerNode ? ownerNode.textContent.trim() : '',
                    amount: money.amount,
                    currency: money.currency,
                    stops: stops,
                    minutes: minutes,
                    departureHour: hourOf(
                        departureNode && departureNode.dataset
                            ? departureNode.dataset.departingAt
                            : null
                    ),
                    arrivalHour: hourOf(
                        arrivalNode && arrivalNode.dataset
                            ? arrivalNode.dataset.arrivingAt
                            : null
                    ),
                };
            });
    };

    /* ------------------------------------------------------------------ *
     * Bounds and defaults
     * ------------------------------------------------------------------ */

    var setRange = function (input, low, high) {
        if (!input) {
            return;
        }

        input.min = String(low);
        input.max = String(high);

        var current = Number(input.value);

        if (!Number.isFinite(current)) {
            input.value = String(low);

            return;
        }

        /* A position outside the new range is clamped, never dropped. */
        input.value = String(Math.min(Math.max(current, low), high));
    };

    var boundsAreForced = function () {
        if (forceNext) {
            forceNext = false;

            return true;
        }

        return false;
    };

    var initPriceBounds = function (force) {
        var priced = records.filter(function (record) {
            return record.amount !== null;
        });

        /*
            A price filter is only offered when every priced option is quoted
            in one currency. Comparing unlike currencies would be a made-up
            comparison, so the control stays disabled instead.
        */
        var singleCurrency =
            priced.length > 0
            && priced.every(function (record) {
                return (
                    record.currency !== null
                    && record.currency === priced[0].currency
                );
            });

        priceBounds = singleCurrency
            ? {
                currency: priced[0].currency,
                low: Math.floor(
                    priced.reduce(function (lowest, record) {
                        return Math.min(lowest, record.amount);
                    }, priced[0].amount)
                ),
                high: Math.ceil(
                    priced.reduce(function (highest, record) {
                        return Math.max(highest, record.amount);
                    }, priced[0].amount)
                ),
            }
            : null;

        if (priceMinInput) {
            priceMinInput.disabled = priceBounds === null;

            if (priceBounds) {
                setRange(priceMinInput, priceBounds.low, priceBounds.high);

                if (force || !rangesTouched.priceMin) {
                    priceMinInput.value = String(priceBounds.low);
                }
            }
        }

        if (priceMaxInput) {
            priceMaxInput.disabled = priceBounds === null;

            if (priceBounds) {
                setRange(priceMaxInput, priceBounds.low, priceBounds.high);

                if (force || !rangesTouched.priceMax) {
                    priceMaxInput.value = String(priceBounds.high);
                }
            }
        }

        if (priceMinLabel) {
            priceMinLabel.textContent = priceBounds
                ? formatMoney(Number(priceMinInput ? priceMinInput.value : priceBounds.low), priceBounds.currency)
                : 'Not applied';
        }

        if (priceMaxLabel) {
            priceMaxLabel.textContent = priceBounds
                ? formatMoney(Number(priceMaxInput ? priceMaxInput.value : priceBounds.high), priceBounds.currency)
                : 'Not applied';
        }
    };

    var initTimeBounds = function (force) {
        var departureHours = records
            .map(function (record) {
                return record.departureHour;
            })
            .filter(function (hour) {
                return hour !== null;
            });

        var arrivalHours = records
            .map(function (record) {
                return record.arrivalHour;
            })
            .filter(function (hour) {
                return hour !== null;
            });

        timeBounds = departureHours.length || arrivalHours.length
            ? {
                departure: departureHours.length
                    ? {
                        low: Math.min.apply(null, departureHours),
                        high: Math.max.apply(null, departureHours),
                    }
                    : null,
                arrival: arrivalHours.length
                    ? {
                        low: Math.min.apply(null, arrivalHours),
                        high: Math.max.apply(null, arrivalHours),
                    }
                    : null,
            }
            : null;

        /*
            The sliders span the hours actually present in this search, and an
            untouched slider starts at that edge, so its default position
            excludes nothing.
        */
        [
            [departureMinInput, timeBounds && timeBounds.departure, 'low', 'departureMin'],
            [departureMaxInput, timeBounds && timeBounds.departure, 'high', 'departureMax'],
            [arrivalMinInput, timeBounds && timeBounds.arrival, 'low', 'arrivalMin'],
            [arrivalMaxInput, timeBounds && timeBounds.arrival, 'high', 'arrivalMax'],
        ].forEach(function (entry) {
            var input = entry[0];
            var bounds = entry[1];
            var edge = entry[2];
            var key = entry[3];

            if (!input) {
                return;
            }

            input.disabled = !bounds;

            if (!bounds) {
                return;
            }

            setRange(input, bounds.low, bounds.high);

            if (force || !rangesTouched[key]) {
                input.value = String(bounds[edge]);
            }
        });

        if (departureWindow) {
            departureWindow.textContent = windowLabel(
                departureMinInput,
                departureMaxInput,
                timeBounds && timeBounds.departure
            );
        }

        if (arrivalWindow) {
            arrivalWindow.textContent = windowLabel(
                arrivalMinInput,
                arrivalMaxInput,
                timeBounds && timeBounds.arrival
            );
        }
    };

    var windowLabel = function (minInput, maxInput, bounds) {
        if (!bounds || !minInput || !maxInput) {
            return 'Not applied';
        }

        return (
            formatHour(Number(minInput.value))
            + ' \u2013 '
            + formatHour(Number(maxInput.value))
        );
    };

    var renderAirlines = function () {
        if (!airlineHost) {
            return;
        }

        var counts = {};

        records.forEach(function (record) {
            if (!record.carrier) {
                return;
            }

            counts[record.carrier] = (counts[record.carrier] || 0) + 1;
        });

        var names = Object.keys(counts).sort();

        /*
            Rebuilding these checkboxes generates DOM mutations, which would
            wake the observer and start another pass. The list is therefore
            rebuilt only when the carriers in the results actually changed,
            which also keeps the visitor's checked boxes intact across an
            unrelated re-render.
        */
        var signature = names
            .map(function (name) {
                return name + ':' + counts[name];
            })
            .join('|');

        if (signature === airlineSignature) {
            return;
        }

        airlineSignature = signature;

        airlineHost.textContent = '';

        if (!names.length) {
            var message = document.createElement('p');
            message.className = 'egho-filter-note';
            message.textContent =
                'Carrier names appear here once a search has returned options.';
            airlineHost.appendChild(message);

            return;
        }

        selectedCarriers = selectedCarriers.filter(function (carrier) {
            return names.indexOf(carrier) !== -1;
        });

        names.forEach(function (name) {
            var label = document.createElement('label');
            label.className = 'egho-check';

            var input = document.createElement('input');
            input.type = 'checkbox';
            input.value = name;
            input.checked = selectedCarriers.indexOf(name) !== -1;
            input.setAttribute('data-flight-carrier-filter', '');

            input.addEventListener('change', function () {
                selectedCarriers = checkedValues(
                    airlineHost,
                    '[data-flight-carrier-filter]'
                );

                apply();
            });

            var text = document.createElement('span');
            text.textContent = name + ' (' + counts[name] + ')';

            label.appendChild(input);
            label.appendChild(text);
            airlineHost.appendChild(label);
        });
    };

    var checkedValues = function (host, selector) {
        if (!host) {
            return [];
        }

        return Array.prototype.slice
            .call(host.querySelectorAll(selector))
            .filter(function (input) {
                return input.checked;
            })
            .map(function (input) {
                return input.value;
            });
    };

    /* ------------------------------------------------------------------ *
     * Filtering
     * ------------------------------------------------------------------ */

    var readFilters = function () {
        var priceLow = priceMinInput && !priceMinInput.disabled
            ? Number(priceMinInput.value)
            : null;

        var priceHigh = priceMaxInput && !priceMaxInput.disabled
            ? Number(priceMaxInput.value)
            : null;

        return {
            stops: stopBoxes
                .filter(function (box) {
                    return box.checked;
                })
                .map(function (box) {
                    return Number(box.value);
                }),
            carriers: selectedCarriers.slice(),
            priceLow: Number.isFinite(priceLow) ? priceLow : null,
            priceHigh: Number.isFinite(priceHigh) ? priceHigh : null,
            departure: hourRange(departureMinInput, departureMaxInput),
            arrival: hourRange(arrivalMinInput, arrivalMaxInput),
        };
    };

    var hourRange = function (minInput, maxInput) {
        if (!minInput || !maxInput || minInput.disabled || maxInput.disabled) {
            return null;
        }

        return {
            low: Number(minInput.value),
            high: Number(maxInput.value),
        };
    };

    var matches = function (record, filters) {
        if (filters.stops.length) {
            var allowed = record.stops.every(function (stops) {
                return filters.stops.indexOf(stops >= 2 ? 2 : stops) !== -1;
            });

            if (!allowed) {
                return false;
            }
        }

        if (filters.carriers.length && filters.carriers.indexOf(record.carrier) === -1) {
            return false;
        }

        if (filters.priceLow !== null && record.amount !== null) {
            if (record.amount < filters.priceLow - 0.5) {
                return false;
            }
        }

        if (filters.priceHigh !== null && record.amount !== null) {
            if (record.amount > filters.priceHigh + 0.5) {
                return false;
            }
        }

        if (filters.departure && record.departureHour !== null) {
            if (record.departureHour < filters.departure.low || record.departureHour > filters.departure.high) {
                return false;
            }
        }

        if (filters.arrival && record.arrivalHour !== null) {
            if (record.arrivalHour < filters.arrival.low || record.arrivalHour > filters.arrival.high) {
                return false;
            }
        }

        return true;
    };

    var apply = function () {
        var filters = readFilters();
        var visible = 0;

        records.forEach(function (record) {
            var allowed = matches(record, filters);
            var hidden = !allowed;

            if (record.node.hidden !== hidden) {
                record.node.hidden = hidden;
            }

            if (record.node.classList.contains('is-filtered-out') !== hidden) {
                record.node.classList.toggle('is-filtered-out', hidden);
            }

            if (allowed) {
                visible += 1;
            }
        });

        updateCount(visible);
        updateCheapest(visible);
        updateEmptyState(visible);
        updateResetButton(filters);
    };

    var updateCount = function (visible) {
        if (!countOutput) {
            return;
        }

        var total = records.length;

        if (!total) {
            if (!countOutput.hidden) {
                countOutput.hidden = true;
            }

            return;
        }

        var message = visible === total
            ? 'Showing all ' + total + ' option' + (total === 1 ? '' : 's')
            + ' returned by this search'
            : 'Showing ' + visible + ' of ' + total + ' options';

        if (countOutput.hidden) {
            countOutput.hidden = false;
        }

        if (countOutput.textContent !== message) {
            countOutput.textContent = message;
        }
    };

    var setCheapestBadge = function (record, show) {
        var existing = record.querySelector('[data-flight-cheapest]');

        if (!show) {
            if (existing) {
                existing.remove();
            }

            return;
        }

        if (existing) {
            return;
        }

        var identity = record.querySelector('.flight-offer-identity');

        if (!identity) {
            return;
        }

        var badge = document.createElement('span');
        badge.className = 'egho-flight-cheapest';
        badge.setAttribute('data-flight-cheapest', '');
        badge.textContent = 'Cheapest';
        badge.title =
            'Lowest total fare among the options returned by this search.';

        identity.appendChild(badge);
    };

    var updateCheapest = function (visible) {
        var cheapest = null;

        if (priceBounds && visible > 1) {
            var eligible = records.filter(function (record) {
                return !record.node.hidden && record.amount !== null;
            });

            if (eligible.length > 1) {
                cheapest = eligible.reduce(function (best, record) {
                    if (best === null || record.amount < best.amount) {
                        return record;
                    }

                    return best;
                }, null);
            }
        }

        records.forEach(function (record) {
            setCheapestBadge(record.node, record === cheapest);
        });
    };

    var updateEmptyState = function (visible) {
        var existing = results.querySelector('[data-flight-empty]');

        if (visible > 0 || !records.length) {
            if (existing) {
                existing.remove();
            }

            return;
        }

        if (existing) {
            return;
        }

        var empty = document.createElement('p');
        empty.className = 'egho-flight-empty';
        empty.setAttribute('data-flight-empty', '');
        empty.setAttribute(
            'role',
            'status'
        );
        empty.textContent =
            'No option returned by this search matches the filters above.'
            + ' Clear a filter to see the other options again.';

        results.appendChild(empty);
    };

    var isFiltering = function (filters) {
        if (filters.stops.length || filters.carriers.length) {
            return true;
        }

        if (filters.departure || filters.arrival) {
            var departureNarrowed =
                filters.departure
                && timeBounds
                && timeBounds.departure
                && (
                    filters.departure.low > timeBounds.departure.low
                    || filters.departure.high < timeBounds.departure.high
                );

            var arrivalNarrowed =
                filters.arrival
                && timeBounds
                && timeBounds.arrival
                && (
                    filters.arrival.low > timeBounds.arrival.low
                    || filters.arrival.high < timeBounds.arrival.high
                );

            if (departureNarrowed || arrivalNarrowed) {
                return true;
            }
        }

        if (priceBounds && filters.priceLow !== null && filters.priceLow > priceBounds.low) {
            return true;
        }

        if (priceBounds && filters.priceHigh !== null && filters.priceHigh < priceBounds.high) {
            return true;
        }

        return false;
    };

    var updateResetButton = function (filters) {
        if (!resetButton) {
            return;
        }

        var needed = records.length > 0 && isFiltering(filters);

        if (resetButton.hidden !== !needed) {
            resetButton.hidden = !needed;
        }
    };

    /* ------------------------------------------------------------------ *
     * Sorting
     * ------------------------------------------------------------------ */

    var applySort = function () {
        var host = results.querySelector('.flight-offer-cards');

        if (!host || records.length < 2) {
            return;
        }

        var ordered = records.slice();

        if (sort === 'cheapest' && priceBounds) {
            ordered.sort(function (a, b) {
                return (
                    (a.amount === null ? Infinity : a.amount)
                    - (b.amount === null ? Infinity : b.amount)
                );
            });
        } else if (sort === 'shortest') {
            ordered.sort(function (a, b) {
                return (
                    (a.minutes || Infinity) - (b.minutes || Infinity)
                );
            });
        } else if (sort === 'earliest') {
            ordered.sort(function (a, b) {
                return (
                    (a.departureHour === null ? 99 : a.departureHour)
                    - (b.departureHour === null ? 99 : b.departureHour)
                );
            });
        } else {
            ordered.sort(function (a, b) {
                return a.index - b.index;
            });
        }

        var changed = ordered.some(function (record, position) {
            return host.children[position] !== record.node;
        });

        if (!changed) {
            return;
        }

        ordered.forEach(function (record) {
            host.appendChild(record.node);
        });
    };

    /* ------------------------------------------------------------------ *
     * Refresh cycle
     * ------------------------------------------------------------------ */

    var refresh = function () {
        busy = true;

        try {
            var force = boundsAreForced();

            scan();
            initPriceBounds(force);
            initTimeBounds(force);
            renderAirlines();

            stopBoxes.forEach(function (box) {
                box.disabled = records.length === 0;
                box.checked = selectedStops.indexOf(box.value) !== -1;
            });

            if (resetButton && records.length === 0) {
                resetButton.hidden = true;
            }

            if (sortSelect) {
                sortSelect.disabled = records.length < 2;

                var cheapest = sortSelect.querySelector('option[value="cheapest"]');

                if (cheapest) {
                    cheapest.disabled = priceBounds === null;
                }

                if (priceBounds === null && sort === 'cheapest') {
                    sort = 'provider';
                    sortSelect.value = 'provider';
                }
            }

            if (note) {
                note.textContent = records.length ? activeNote : idleNote;
            }

            applySort();
            apply();
        } finally {
            busy = false;
        }
    };

    var scheduleRefresh = function () {
        window.clearTimeout(renderTimer);

        renderTimer = window.setTimeout(function () {
            refresh();
        }, 60);
    };

    /* ------------------------------------------------------------------ *
     * Summary bar
     * ------------------------------------------------------------------ */

    var fieldValue = function (name) {
        var element = form.elements[name];

        return element ? String(element.value || '').trim() : '';
    };

    var updateSummary = function () {
        if (!summary) {
            return;
        }

        var origin = fieldValue('origin').toUpperCase();
        var destination = fieldValue('destination').toUpperCase();
        var tripType = fieldValue('trip_type');
        var cabinField = form.elements['cabin_class'];

        var cabin =
            cabinField
            && cabinField.selectedOptions
            && cabinField.selectedOptions[0]
                ? cabinField.selectedOptions[0].textContent.trim()
                : '';

        var adults = Number(fieldValue('adults') || 0);
        var children = Number(fieldValue('children') || 0);
        var infants = Number(fieldValue('infants') || 0);
        var travellers = adults + children + infants;

        if (summaryOrigin) {
            summaryOrigin.textContent = origin || EMPTY;
        }

        if (summaryDestination) {
            summaryDestination.textContent = destination || EMPTY;
        }

        var dates = formatDate(fieldValue('departure_date'));

        if (tripType === 'round_trip' && fieldValue('return_date')) {
            dates = dates + ' \u2013 ' + formatDate(fieldValue('return_date'));
        } else if (tripType === 'one_way') {
            dates = dates + ' \u00b7 One way';
        }

        if (summaryDates) {
            summaryDates.textContent = dates || EMPTY;
        }

        if (summaryTravellers) {
            summaryTravellers.textContent =
                (
                    travellers > 0
                        ? travellers + ' traveller' + (travellers === 1 ? '' : 's')
                        : 'Travellers not set'
                )
                + (cabin ? ' \u00b7 ' + cabin : '');
        }

        summary.hidden = false;
    };

    /* ------------------------------------------------------------------ *
     * Reset
     * ------------------------------------------------------------------ */

    var resetFilters = function () {
        selectedStops = [];
        selectedCarriers = [];

        Object.keys(rangesTouched).forEach(function (key) {
            rangesTouched[key] = false;
        });

        stopBoxes.forEach(function (box) {
            box.checked = false;
        });

        if (airlineHost) {
            Array.prototype.slice
                .call(airlineHost.querySelectorAll('input[type="checkbox"]'))
                .forEach(function (input) {
                    input.checked = false;
                });
        }

        if (priceBounds) {
            if (priceMinInput) {
                priceMinInput.value = String(priceBounds.low);
            }

            if (priceMaxInput) {
                priceMaxInput.value = String(priceBounds.high);
            }
        }

        if (timeBounds) {
            if (timeBounds.departure) {
                if (departureMinInput) {
                    departureMinInput.value = String(timeBounds.departure.low);
                }

                if (departureMaxInput) {
                    departureMaxInput.value = String(timeBounds.departure.high);
                }
            }

            if (timeBounds.arrival) {
                if (arrivalMinInput) {
                    arrivalMinInput.value = String(timeBounds.arrival.low);
                }

                if (arrivalMaxInput) {
                    arrivalMaxInput.value = String(timeBounds.arrival.high);
                }
            }
        }

        forceNext = true;

        if (note && !records.length) {
            note.textContent = idleNote;
        }
    };

    /* ------------------------------------------------------------------ *
     * Wiring
     * ------------------------------------------------------------------ */

    stopBoxes.forEach(function (box) {
        box.addEventListener('change', function () {
            selectedStops = stopBoxes
                .filter(function (candidate) {
                    return candidate.checked;
                })
                .map(function (candidate) {
                    return candidate.value;
                });

            apply();
        });
    });

    var bindRange = function (input, key) {
        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            /*
                From here on the position is the visitor's own choice, so a new
                result set clamps it instead of moving it back to the edge.
            */
            rangesTouched[key] = true;

            if (priceMinInput && priceMaxInput && !priceMinInput.disabled) {
                var low = Number(priceMinInput.value);
                var high = Number(priceMaxInput.value);

                if (low > high) {
                    if (input === priceMinInput) {
                        priceMaxInput.value = priceMinInput.value;
                    } else {
                        priceMinInput.value = priceMaxInput.value;
                    }
                }
            }

            if (departureMinInput && departureMaxInput && !departureMinInput.disabled) {
                var departureLow = Number(departureMinInput.value);
                var departureHigh = Number(departureMaxInput.value);

                if (departureLow > departureHigh) {
                    if (input === departureMinInput) {
                        departureMaxInput.value = departureMinInput.value;
                    } else {
                        departureMinInput.value = departureMaxInput.value;
                    }
                }
            }

            if (arrivalMinInput && arrivalMaxInput && !arrivalMinInput.disabled) {
                var arrivalLow = Number(arrivalMinInput.value);
                var arrivalHigh = Number(arrivalMaxInput.value);

                if (arrivalLow > arrivalHigh) {
                    if (input === arrivalMinInput) {
                        arrivalMaxInput.value = arrivalMinInput.value;
                    } else {
                        arrivalMinInput.value = arrivalMaxInput.value;
                    }
                }
            }

            if (priceBounds) {
                if (priceMinLabel) {
                    priceMinLabel.textContent = formatMoney(
                        Number(priceMinInput ? priceMinInput.value : priceBounds.low),
                        priceBounds.currency
                    );
                }

                if (priceMaxLabel) {
                    priceMaxLabel.textContent = formatMoney(
                        Number(priceMaxInput ? priceMaxInput.value : priceBounds.high),
                        priceBounds.currency
                    );
                }
            }

            if (timeBounds) {
                if (departureWindow) {
                    departureWindow.textContent = windowLabel(
                        departureMinInput,
                        departureMaxInput,
                        timeBounds.departure
                    );
                }

                if (arrivalWindow) {
                    arrivalWindow.textContent = windowLabel(
                        arrivalMinInput,
                        arrivalMaxInput,
                        timeBounds.arrival
                    );
                }
            }

            apply();
        });
    };

    [
        [priceMinInput, 'priceMin'],
        [priceMaxInput, 'priceMax'],
        [departureMinInput, 'departureMin'],
        [departureMaxInput, 'departureMax'],
        [arrivalMinInput, 'arrivalMin'],
        [arrivalMaxInput, 'arrivalMax'],
    ].forEach(function (entry) {
        bindRange(entry[0], entry[1]);
    });

    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            sort = sortSelect.value;

            applySort();
            apply();
        });
    }

    if (resetButton) {
        resetButton.addEventListener('click', function () {
            resetFilters();
            refresh();
        });
    }

    if (summaryModify) {
        summaryModify.addEventListener('click', function () {
            var card = form.closest('.flight-search-card') || form;

            if (form.elements['origin'] && form.elements['origin'].focus) {
                form.elements['origin'].focus();
            }

            if (card.scrollIntoView) {
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    form.addEventListener('submit', function () {
        updateSummary();
        resetFilters();
        sort = 'provider';

        if (sortSelect) {
            sortSelect.value = 'provider';
        }
    });

    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function () {
            if (busy) {
                return;
            }

            scheduleRefresh();
        });

        observer.observe(results, {
            childList: true,
            subtree: true,
        });
    }

    /*
     * The results box starts hidden and empty, so the first refresh runs only
     * when the flight search script has rendered something. Running it once
     * now keeps the summary and the disabled state of every control correct
     * even before the first search.
     */
    refresh();
})();
