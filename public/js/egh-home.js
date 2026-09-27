/*
    Homepage rails.

    Destination and comment rails stay progressively enhanced: every card is
    present in the document, touch/trackpad scrolling works without JavaScript,
    and controls appear only when there is actually something to scroll.
*/
(function () {
    'use strict';

    var reducedMotion = window.matchMedia
        ? window.matchMedia('(prefers-reduced-motion: reduce)')
        : null;

    var initRail = function (rail) {
        var scope = rail.closest('section') || rail.parentElement || document;
        var track = rail.querySelector('[data-egho-rail-track]');
        var previous = scope.querySelector('[data-egho-rail-prev]');
        var next = scope.querySelector('[data-egho-rail-next]');

        if (!track || !previous || !next) {
            return;
        }

        var autoplayDelay = parseInt(rail.getAttribute('data-egho-autoplay') || '0', 10);
        var autoplayTimer = null;
        var interactionTimer = null;
        var interacting = false;

        var items = Array.prototype.slice.call(
            track.querySelectorAll('[data-egho-rail-item]')
        );

        var step = function () {
            var card = items[0];

            if (!card) {
                return track.clientWidth;
            }

            var styles = window.getComputedStyle(track);
            var gap = parseFloat(styles.columnGap || styles.gap || '0');

            return card.getBoundingClientRect().width
                + (Number.isFinite(gap) ? gap : 0);
        };

        var maxScroll = function () {
            return Math.max(0, track.scrollWidth - track.clientWidth);
        };

        var markActiveCard = function () {
            if (!items.length) return;

            var trackRect = track.getBoundingClientRect();
            var trackCenter = trackRect.left + (trackRect.width / 2);
            var closest = null;
            var closestDistance = Infinity;

            items.forEach(function (item) {
                var rect = item.getBoundingClientRect();
                var center = rect.left + (rect.width / 2);
                var distance = Math.abs(center - trackCenter);

                item.classList.remove('is-rail-active');

                if (distance < closestDistance) {
                    closestDistance = distance;
                    closest = item;
                }
            });

            if (closest) {
                closest.classList.add('is-rail-active');
            }
        };

        var pulseButton = function (button) {
            if (!button || (reducedMotion && reducedMotion.matches)) return;
            button.classList.remove('is-rail-pulse');
            void button.offsetWidth;
            button.classList.add('is-rail-pulse');
            window.setTimeout(function () {
                button.classList.remove('is-rail-pulse');
            }, 420);
        };

        var sync = function () {
            var limit = maxScroll();
            var canScroll = limit > 4;

            rail.setAttribute('data-egho-rail-ready', 'true');
            previous.hidden = !canScroll;
            next.hidden = !canScroll;

            if (!canScroll) {
                previous.disabled = true;
                next.disabled = true;
                markActiveCard();
                return;
            }

            previous.disabled = track.scrollLeft <= 2;
            next.disabled = track.scrollLeft >= limit - 2;
            markActiveCard();
        };

        var scrollToPosition = function (left) {
            track.scrollTo({
                left: left,
                behavior: reducedMotion && reducedMotion.matches ? 'auto' : 'smooth'
            });
        };

        var goPrevious = function () {
            scrollToPosition(Math.max(0, track.scrollLeft - step()));
            pulseButton(previous);
        };

        var goNext = function () {
            var limit = maxScroll();
            var target = track.scrollLeft + step();

            if (target >= limit - 2) {
                target = limit;
            }

            scrollToPosition(target);
            pulseButton(next);
        };

        var autoplayTick = function () {
            if (interacting || document.hidden || maxScroll() <= 4) {
                return;
            }

            var limit = maxScroll();
            if (track.scrollLeft >= limit - 2) {
                scrollToPosition(0);
                pulseButton(previous);
            } else {
                goNext();
            }
        };

        var stopAutoplay = function () {
            if (autoplayTimer !== null) {
                window.clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        };

        var startAutoplay = function () {
            stopAutoplay();

            if (
                !Number.isFinite(autoplayDelay)
                || autoplayDelay < 2500
                || (reducedMotion && reducedMotion.matches)
                || maxScroll() <= 4
            ) {
                return;
            }

            autoplayTimer = window.setInterval(autoplayTick, autoplayDelay);
        };

        var beginInteraction = function () {
            interacting = true;
            if (interactionTimer !== null) {
                window.clearTimeout(interactionTimer);
                interactionTimer = null;
            }
        };

        var endInteraction = function () {
            if (interactionTimer !== null) {
                window.clearTimeout(interactionTimer);
            }

            interactionTimer = window.setTimeout(function () {
                interacting = false;
            }, 900);
        };

        previous.addEventListener('click', function () {
            beginInteraction();
            goPrevious();
            endInteraction();
            startAutoplay();
        });

        next.addEventListener('click', function () {
            beginInteraction();
            goNext();
            endInteraction();
            startAutoplay();
        });

        track.addEventListener('scroll', sync, { passive: true });
        track.addEventListener('pointerdown', beginInteraction, { passive: true });
        track.addEventListener('pointerup', endInteraction, { passive: true });
        track.addEventListener('pointercancel', endInteraction, { passive: true });
        track.addEventListener('touchend', endInteraction, { passive: true });
        rail.addEventListener('focusin', beginInteraction);
        rail.addEventListener('focusout', endInteraction);

        window.addEventListener('resize', function () {
            sync();
            startAutoplay();
        });

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                startAutoplay();
            }
        });

        if (reducedMotion && typeof reducedMotion.addEventListener === 'function') {
            reducedMotion.addEventListener('change', startAutoplay);
        }

        sync();
        startAutoplay();
    };

    var init = function () {
        document.querySelectorAll('[data-egho-rail]').forEach(initRail);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
