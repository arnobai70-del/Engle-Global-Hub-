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
        var paused = false;

        var step = function () {
            var card = track.querySelector('[data-egho-rail-item]');

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

        var sync = function () {
            var limit = maxScroll();
            var canScroll = limit > 4;

            rail.setAttribute('data-egho-rail-ready', 'true');
            previous.hidden = !canScroll;
            next.hidden = !canScroll;

            if (!canScroll) {
                previous.disabled = true;
                next.disabled = true;
                return;
            }

            previous.disabled = track.scrollLeft <= 2;
            next.disabled = track.scrollLeft >= limit - 2;
        };

        var scrollToPosition = function (left) {
            track.scrollTo({
                left: left,
                behavior: reducedMotion && reducedMotion.matches ? 'auto' : 'smooth'
            });
        };

        var goPrevious = function () {
            scrollToPosition(Math.max(0, track.scrollLeft - step()));
        };

        var goNext = function () {
            var limit = maxScroll();
            var target = track.scrollLeft + step();

            if (target >= limit - 2) {
                target = limit;
            }

            scrollToPosition(target);
        };

        var autoplayTick = function () {
            if (paused || document.hidden || maxScroll() <= 4) {
                return;
            }

            var limit = maxScroll();
            if (track.scrollLeft >= limit - 2) {
                scrollToPosition(0);
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

        previous.addEventListener('click', function () {
            goPrevious();
            startAutoplay();
        });

        next.addEventListener('click', function () {
            goNext();
            startAutoplay();
        });

        track.addEventListener('scroll', sync, { passive: true });

        rail.addEventListener('mouseenter', function () {
            paused = true;
        });
        rail.addEventListener('mouseleave', function () {
            paused = false;
        });
        rail.addEventListener('focusin', function () {
            paused = true;
        });
        rail.addEventListener('focusout', function () {
            paused = false;
        });
        rail.addEventListener('pointerdown', function () {
            paused = true;
        }, { passive: true });
        rail.addEventListener('pointerup', function () {
            paused = false;
        }, { passive: true });

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
