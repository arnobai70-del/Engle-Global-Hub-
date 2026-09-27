/*
    Homepage rails.

    Destination and testimonial rails keep their real previous/next controls.
    Rails marked with `data-egho-auto` also move automatically, one card at a
    time. Autoplay pauses while the user hovers, focuses or interacts with the
    rail, and it is disabled for users who prefer reduced motion.
*/
(function () {
    'use strict';

    var reduceMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var initRail = function (rail) {
        var scope = rail.closest('section') || rail.parentElement || document;
        var track = rail.querySelector('[data-egho-rail-track]');
        var previous = scope.querySelector('[data-egho-rail-prev]');
        var next = scope.querySelector('[data-egho-rail-next]');

        if (!track || !previous || !next) {
            return;
        }

        var autoTimer = null;
        var autoDirection = 1;
        var autoDelay = parseInt(rail.getAttribute('data-egho-auto-delay') || '3200', 10);

        if (!Number.isFinite(autoDelay) || autoDelay < 1800) {
            autoDelay = 3200;
        }

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
                return;
            }

            previous.disabled = track.scrollLeft <= 2;
            next.disabled = track.scrollLeft >= limit - 2;
        };

        var move = function (direction) {
            track.scrollBy({
                left: direction * step(),
                behavior: reduceMotion ? 'auto' : 'smooth'
            });
        };

        var stopAuto = function () {
            if (autoTimer !== null) {
                window.clearInterval(autoTimer);
                autoTimer = null;
            }
        };

        var startAuto = function () {
            if (reduceMotion || !rail.hasAttribute('data-egho-auto') || maxScroll() <= 4) {
                return;
            }

            stopAuto();
            autoTimer = window.setInterval(function () {
                if (document.hidden) {
                    return;
                }

                var limit = maxScroll();

                if (track.scrollLeft >= limit - 2) {
                    autoDirection = -1;
                } else if (track.scrollLeft <= 2) {
                    autoDirection = 1;
                }

                move(autoDirection);
            }, autoDelay);
        };

        previous.addEventListener('click', function () {
            autoDirection = -1;
            move(-1);
            startAuto();
        });

        next.addEventListener('click', function () {
            autoDirection = 1;
            move(1);
            startAuto();
        });

        rail.addEventListener('mouseenter', stopAuto);
        rail.addEventListener('mouseleave', startAuto);
        rail.addEventListener('focusin', stopAuto);
        rail.addEventListener('focusout', function (event) {
            if (!rail.contains(event.relatedTarget)) {
                startAuto();
            }
        });
        rail.addEventListener('pointerdown', stopAuto);
        rail.addEventListener('pointerup', startAuto);

        track.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', function () {
            sync();
            startAuto();
        });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopAuto();
            } else {
                startAuto();
            }
        });

        sync();
        startAuto();
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
